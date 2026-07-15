<?php

namespace App\Controllers\Admin;

use App\Services\ImageOptimizationService;

class MediaAudit extends BaseAdminController
{
    private const OPTIMIZATION_THRESHOLD_BYTES = 300 * 1024;
    private const MAX_OPTIMIZATIONS_PER_REQUEST = 30;

    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $report = $this->buildReport();

        return view('admin/media-audit/index', [
            'report' => $report,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function deleteOrphans()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $files = $this->request->getPost('files');
        $files = is_array($files) ? array_values(array_filter(array_map('strval', $files))) : [];

        $deleted = 0;
        foreach ($files as $relativePath) {
            if ($this->deleteRelativeFile($relativePath)) {
                $deleted++;
            }
        }

        return redirect()->to(site_url('admin/media-audit'))->with(
            $deleted > 0 ? 'success' : 'error',
            $deleted > 0
                ? 'Đã xóa ' . $deleted . ' file mồ côi.'
                : 'Không có file hợp lệ để xóa.'
        );
    }

    public function optimizeSelected()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $requestedFiles = $this->request->getPost('files');
        $requestedFiles = is_array($requestedFiles)
            ? array_values(array_unique(array_filter(array_map(fn($path): string => $this->normalizeRelativePath((string) $path), $requestedFiles))))
            : [];
        $files = array_slice($requestedFiles, 0, self::MAX_OPTIMIZATIONS_PER_REQUEST);

        if ($files === []) {
            return redirect()->to(site_url('admin/media-audit'))->with('error', 'Chưa chọn ảnh cần tối ưu.');
        }

        $db = db_connect();
        $optimizer = new ImageOptimizationService();
        $optimized = 0;
        $unchanged = 0;
        $failed = 0;
        $savedBytes = 0;

        foreach ($files as $relativePath) {
            $absolutePath = $this->resolveManagedFilePath($relativePath, ['uploads/blogs/', 'uploads/tours/']);
            $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

            if ($absolutePath === null || ! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $failed++;
                continue;
            }

            $destinationPath = preg_replace('/\.(?:jpe?g|png)$/i', '.webp', $absolutePath) ?: '';
            if ($destinationPath === '' || ($extension !== 'webp' && is_file($destinationPath))) {
                $failed++;
                continue;
            }

            if ($this->countMediaReferences($db, $relativePath) < 1) {
                $failed++;
                continue;
            }

            $maxDimension = $this->optimizationMaxDimension($relativePath);
            $result = $optimizer->optimizeToWebp($absolutePath, $maxDimension, $maxDimension, 82, false);
            if (! $result['success']) {
                $failed++;
                continue;
            }
            if (! $result['optimized']) {
                $unchanged++;
                continue;
            }

            if ($extension === 'webp') {
                $optimized++;
                $savedBytes += max(0, (int) $result['original_bytes'] - (int) $result['output_bytes']);
                continue;
            }

            $newRelativePath = $this->relativePathFromAbsolute((string) $result['output_path']);
            if ($newRelativePath === '') {
                @unlink((string) $result['output_path']);
                $failed++;
                continue;
            }

            $db->transStart();
            $this->replaceMediaReferences($db, $relativePath, $newRelativePath);
            $db->transComplete();

            if (! $db->transStatus()) {
                @unlink((string) $result['output_path']);
                $failed++;
                continue;
            }

            if ((string) $result['output_path'] !== $absolutePath) {
                @unlink($absolutePath);
            }
            $optimized++;
            $savedBytes += max(0, (int) $result['original_bytes'] - (int) $result['output_bytes']);
        }

        $skippedByLimit = max(0, count($requestedFiles) - count($files));
        if ($optimized > 0) {
            try {
                cache()->clean();
            } catch (\Throwable) {
            }
        }

        $message = 'Đã tối ưu ' . $optimized . ' ảnh, giảm khoảng ' . $this->formatBytes($savedBytes) . '.';
        if ($failed > 0) {
            $message .= ' Có ' . $failed . ' ảnh không thể xử lý hoặc đã có bản WebP.';
        }
        if ($unchanged > 0) {
            $message .= ' Có ' . $unchanged . ' ảnh đã đủ nhẹ nên được giữ nguyên.';
        }
        if ($skippedByLimit > 0) {
            $message .= ' Còn ' . $skippedByLimit . ' ảnh chưa xử lý để tránh timeout; hãy chạy lại lần nữa.';
        }

        return redirect()->to(site_url('admin/media-audit'))->with($optimized > 0 || $unchanged > 0 ? 'success' : 'error', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReport(): array
    {
        $db = db_connect();

        $referencedBlogFiles = [];
        if ($db->tableExists('blogs')) {
            $rows = $db->table('blogs')
                ->select('thumbnail, cover_image, featured_image')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                foreach (['thumbnail', 'cover_image', 'featured_image'] as $field) {
                    $path = trim((string) ($row[$field] ?? ''));
                    if ($path !== '') {
                        $referencedBlogFiles[] = $this->normalizeRelativePath($path);
                    }
                }
            }
        }

        $referencedTourFiles = [];
        if ($db->tableExists('tours')) {
            $rows = $db->table('tours')->select('thumbnail')->get()->getResultArray();
            foreach ($rows as $row) {
                $path = trim((string) ($row['thumbnail'] ?? ''));
                if ($path !== '') {
                    $referencedTourFiles[] = $this->normalizeRelativePath($path);
                }
            }
        }

        if ($db->tableExists('tour_media')) {
            $rows = $db->table('tour_media')->select('file_path')->get()->getResultArray();
            foreach ($rows as $row) {
                $path = trim((string) ($row['file_path'] ?? ''));
                if ($path !== '') {
                    $referencedTourFiles[] = $this->normalizeRelativePath($path);
                }
            }
        }

        $referencedBlogFiles = array_values(array_unique(array_filter($referencedBlogFiles)));
        $referencedTourFiles = array_values(array_unique(array_filter($referencedTourFiles)));

        $blogFilesOnDisk = $this->scanManagedFiles('uploads/blogs');
        $tourFilesOnDisk = $this->scanManagedFiles('uploads/tours');

        $orphanBlogFiles = array_values(array_diff($blogFilesOnDisk, $referencedBlogFiles));
        $orphanTourFiles = array_values(array_diff($tourFilesOnDisk, $referencedTourFiles));
        $allOrphans = array_merge($orphanBlogFiles, $orphanTourFiles);
        $referencedFiles = array_values(array_unique(array_merge($referencedBlogFiles, $referencedTourFiles)));
        $optimizable = [];

        foreach ($referencedFiles as $path) {
            $absolutePath = $this->resolveManagedFilePath($path, ['uploads/blogs/', 'uploads/tours/']);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $size = $absolutePath !== null ? (int) (filesize($absolutePath) ?: 0) : 0;

            if ($absolutePath === null || $size < self::OPTIMIZATION_THRESHOLD_BYTES || ! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $optimizable[] = [
                'path' => $path,
                'size' => $size,
                'type' => strtoupper($extension),
            ];
        }

        usort($optimizable, static fn(array $left, array $right): int => ((int) $right['size']) <=> ((int) $left['size']));

        return [
            'stats' => [
                'blog_referenced' => count($referencedBlogFiles),
                'tour_referenced' => count($referencedTourFiles),
                'blog_on_disk' => count($blogFilesOnDisk),
                'tour_on_disk' => count($tourFilesOnDisk),
                'orphan_total' => count($allOrphans),
                'optimizable_total' => count($optimizable),
                'optimizable_bytes' => array_sum(array_column($optimizable, 'size')),
            ],
            'optimizable' => $optimizable,
            'orphans' => array_map(function (string $path): array {
                $absolutePath = $this->absolutePath($path);

                return [
                    'path' => $path,
                    'size' => is_file($absolutePath) ? filesize($absolutePath) ?: 0 : 0,
                    'modified_at' => is_file($absolutePath) ? date('Y-m-d H:i:s', filemtime($absolutePath) ?: time()) : '',
                ];
            }, $allOrphans),
        ];
    }

    /**
     * @return list<string>
     */
    private function scanManagedFiles(string $relativeDirectory): array
    {
        $absoluteDirectory = $this->absolutePath($relativeDirectory);
        if (! is_dir($absoluteDirectory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absoluteDirectory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (! $item->isFile()) {
                continue;
            }

            $absolutePath = str_replace('\\', '/', $item->getPathname());
            $root = str_replace('\\', '/', rtrim(FCPATH, '\\/'));
            $relativePath = ltrim(substr($absolutePath, strlen($root)), '/');
            $files[] = $this->normalizeRelativePath($relativePath);
        }

        sort($files);

        return $files;
    }

    private function deleteRelativeFile(string $relativePath): bool
    {
        $relativePath = $this->normalizeRelativePath($relativePath);
        $absolutePath = $this->resolveManagedFilePath($relativePath, ['uploads/blogs/', 'uploads/tours/']);
        if ($absolutePath === null || ! is_file($absolutePath)) {
            return false;
        }

        return @unlink($absolutePath);
    }

    /**
     * @param list<string> $allowedPrefixes
     */
    private function resolveManagedFilePath(string $relativePath, array $allowedPrefixes): ?string
    {
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            return null;
        }

        $candidate = realpath($this->absolutePath($relativePath));
        if ($candidate === false || ! is_file($candidate)) {
            return null;
        }

        $candidate = str_replace('\\', '/', $candidate);

        foreach ($allowedPrefixes as $allowedPrefix) {
            $allowedPrefix = $this->normalizeRelativePath($allowedPrefix);
            if (! str_starts_with($relativePath, rtrim($allowedPrefix, '/') . '/')) {
                continue;
            }

            $root = realpath($this->absolutePath($allowedPrefix));
            if ($root === false) {
                continue;
            }

            $root = rtrim(str_replace('\\', '/', $root), '/') . '/';
            if (str_starts_with($candidate, $root)) {
                return $candidate;
            }
        }

        return null;
    }

    private function absolutePath(string $relativePath): string
    {
        return rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
    }

    private function normalizeRelativePath(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }

    private function relativePathFromAbsolute(string $absolutePath): string
    {
        $absolutePath = str_replace('\\', '/', $absolutePath);
        $root = rtrim(str_replace('\\', '/', FCPATH), '/') . '/';

        return str_starts_with($absolutePath, $root)
            ? $this->normalizeRelativePath(substr($absolutePath, strlen($root)))
            : '';
    }

    private function countMediaReferences($db, string $path): int
    {
        $count = 0;

        foreach ($this->mediaReferenceFields($db) as [$table, $field]) {
            $count += (int) $db->table($table)->where($field, $path)->countAllResults();
        }

        return $count;
    }

    private function replaceMediaReferences($db, string $oldPath, string $newPath): void
    {
        foreach ($this->mediaReferenceFields($db) as [$table, $field]) {
            $db->table($table)->where($field, $oldPath)->update([$field => $newPath]);
        }
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function mediaReferenceFields($db): array
    {
        $fields = [];

        if ($db->tableExists('blogs')) {
            foreach (['thumbnail', 'cover_image', 'featured_image'] as $field) {
                $fields[] = ['blogs', $field];
            }
        }
        if ($db->tableExists('tours')) {
            $fields[] = ['tours', 'thumbnail'];
        }
        if ($db->tableExists('tour_media')) {
            $fields[] = ['tour_media', 'file_path'];
        }

        return $fields;
    }

    private function optimizationMaxDimension(string $relativePath): int
    {
        if (str_starts_with($relativePath, 'uploads/blogs/')) {
            return str_contains($relativePath, '/thumbnail/') ? 1400 : 2000;
        }

        if (str_contains($relativePath, '/gallery/')) {
            return 1800;
        }

        return 2000;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 1, ',', '.') . ' MB';
        }

        return number_format($bytes / 1024, 0, ',', '.') . ' KB';
    }
}
