<?php

namespace App\Controllers\Admin;

class MediaAudit extends BaseAdminController
{
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

        return [
            'stats' => [
                'blog_referenced' => count($referencedBlogFiles),
                'tour_referenced' => count($referencedTourFiles),
                'blog_on_disk' => count($blogFilesOnDisk),
                'tour_on_disk' => count($tourFilesOnDisk),
                'orphan_total' => count($allOrphans),
            ],
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
}
