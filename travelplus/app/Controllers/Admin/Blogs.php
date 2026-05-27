<?php

namespace App\Controllers\Admin;

use App\Data\LocalizedPathCatalog;
use DOMDocument;
use DOMElement;

class Blogs extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $this->hasBlogTables($db)) {
            return redirect()->to(LocalizedPathCatalog::url('admin.dashboard'))
                ->with('error', 'Chưa có bảng blog. Hãy chạy file SQL tạo bảng blog trước.');
        }

        $status = trim((string) $this->request->getGet('status'));
        $keyword = trim((string) $this->request->getGet('q'));
        $viewCountSelect = $db->fieldExists('view_count', 'blogs') ? 'b.view_count' : '0 AS view_count';

        $builder = $db->table('blogs b')
            ->select('
                b.id,
                b.category,
                b.author_name,
                b.thumbnail,
                b.cover_image,
                b.featured_image,
                b.status,
                b.is_featured,
                ' . $viewCountSelect . ',
                b.published_at,
                vi.title AS title_vi,
                vi.slug AS slug_vi,
                en.title AS title_en,
                en.slug AS slug_en
            ', false)
            ->join('blog_translations vi', 'vi.blog_id = b.id AND vi.locale = "vi"', 'left')
            ->join('blog_translations en', 'en.blog_id = b.id AND en.locale = "en"', 'left');

        if ($status !== '' && in_array($status, ['draft', 'published'], true)) {
            $builder->where('b.status', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('vi.title', $keyword)
                ->orLike('en.title', $keyword)
                ->orLike('vi.slug', $keyword)
                ->orLike('en.slug', $keyword)
                ->orLike('b.category', $keyword)
                ->orLike('b.author_name', $keyword)
                ->groupEnd();
        }

        $blogs = $builder
            ->orderBy('b.is_featured', 'DESC')
            ->orderBy('b.published_at', 'DESC')
            ->orderBy('b.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/blogs/index', [
            'blogs' => $blogs,
            'status' => $status,
            'keyword' => $keyword,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return $this->renderForm([
            'formAction' => site_url('admin/blogs'),
            'pageTitle' => 'Create blog',
            'pageDesc' => 'Soạn bài viết, chèn ảnh trong nội dung và lưu song ngữ VI/EN.',
            'submitLabel' => 'Lưu blog',
            'formData' => session()->getFlashdata('oldFormData') ?? [],
            'errors' => session()->getFlashdata('errors') ?? [],
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return $this->saveBlog();
    }

    public function edit(int $blogId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();
        $blog = $this->getBlogForAdmin($db, $blogId);

        if ($blog === null) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Không tìm thấy bài viết blog.');
        }

        return $this->renderForm([
            'formAction' => site_url('admin/blogs/' . $blogId),
            'pageTitle' => 'Edit blog #' . $blogId,
            'pageDesc' => 'Cập nhật nội dung, hình ảnh và SEO cho bài viết blog.',
            'submitLabel' => 'Cập nhật blog',
            'blogId' => $blogId,
            'formData' => session()->getFlashdata('oldFormData') ?? $blog,
            'errors' => session()->getFlashdata('errors') ?? [],
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function update(int $blogId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return $this->saveBlog($blogId);
    }

    public function updateStatus(int $blogId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $this->hasBlogTables($db)) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Chưa có bảng blog.');
        }

        $blog = $db->table('blogs')->where('id', $blogId)->get()->getRowArray();
        if (! is_array($blog)) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Không tìm thấy bài viết blog.');
        }

        $status = trim((string) $this->request->getPost('status'));
        if (! in_array($status, ['draft', 'published'], true)) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Trạng thái blog không hợp lệ.');
        }

        $payload = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'published' && empty($blog['published_at'])) {
            $payload['published_at'] = date('Y-m-d H:i:s');
        }

        $db->table('blogs')->where('id', $blogId)->update($payload);

        return redirect()->to(site_url('admin/blogs'))->with('success', 'Đã cập nhật trạng thái blog #' . $blogId . '.');
    }

    public function delete(int $blogId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $this->hasBlogTables($db)) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Chưa có bảng blog.');
        }

        $blog = $db->table('blogs')->where('id', $blogId)->get()->getRowArray();
        if (! is_array($blog)) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Không tìm thấy bài viết blog.');
        }

        $db->transStart();
        $db->table('blog_translations')->where('blog_id', $blogId)->delete();
        $db->table('blogs')->where('id', $blogId)->delete();
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Không thể xóa bài viết blog lúc này.');
        }

        $this->deleteDirectory($this->blogUploadDirectory($blogId));

        return redirect()->to(site_url('admin/blogs'))->with('success', 'Đã xóa bài viết blog #' . $blogId . '.');
    }

    public function uploadEditorImage()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $path = $this->storeTempEditorImage('editor_image');

        if ($path === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Upload ảnh thất bại. Chỉ hỗ trợ JPG, PNG, WEBP tối đa 8MB.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'path' => $path,
            'url' => base_url($path),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderForm(array $data)
    {
        $data['categoryOptions'] = $this->getCategoryOptions();
        return view('admin/blogs/form', $data);
    }

    /**
     * @return list<string>
     */
    private function getCategoryOptions(): array
    {
        $db = db_connect();

        if (! $this->hasBlogTables($db)) {
            return [
                'Cảm hứng du lịch',
                'Kinh nghiệm du lịch',
                'Điểm đến nổi bật',
                'Ẩm thực và văn hóa',
            ];
        }

        $rows = $db->table('blogs')
            ->select('category')
            ->where('category !=', '')
            ->groupBy('category')
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        $categories = array_values(array_filter(array_map(
            static fn(array $row): string => trim((string) ($row['category'] ?? '')),
            $rows
        )));

        if ($categories === []) {
            return [
                'Cảm hứng du lịch',
                'Kinh nghiệm du lịch',
                'Điểm đến nổi bật',
                'Ẩm thực và văn hóa',
            ];
        }

        return $categories;
    }

    private function saveBlog(?int $blogId = null)
    {
        $db = db_connect();

        if (! $this->hasBlogTables($db)) {
            return redirect()->back()->withInput()->with('errors', ['Chưa có bảng blog. Hãy chạy file SQL tạo bảng blog trước.']);
        }

        $existing = $blogId !== null ? $this->getBlogForAdmin($db, $blogId) : null;
        $oldImagePaths = $existing === null ? [] : array_filter([
            trim((string) ($existing['thumbnail'] ?? '')),
            trim((string) ($existing['cover_image'] ?? '')),
            trim((string) ($existing['featured_image'] ?? '')),
        ]);
        if ($blogId !== null && $existing === null) {
            return redirect()->to(site_url('admin/blogs'))->with('error', 'Không tìm thấy bài viết blog.');
        }

        $rules = [
            'category' => 'required|max_length[120]',
            'author_name' => 'required|max_length[120]',
            'status' => 'required|in_list[draft,published]',
            'published_at' => 'permit_empty|valid_date[Y-m-d\\TH:i]',
            'title_vi' => 'required|min_length[3]|max_length[255]',
            'slug_vi' => 'required|min_length[3]|max_length[255]',
            'excerpt_vi' => 'permit_empty|max_length[5000]',
            'content_vi' => 'permit_empty|max_length[200000]',
            'title_en' => 'permit_empty|min_length[3]|max_length[255]',
            'slug_en' => 'permit_empty|min_length[3]|max_length[255]',
            'excerpt_en' => 'permit_empty|max_length[5000]',
            'content_en' => 'permit_empty|max_length[200000]',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectBackWithFormErrors($blogId, $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $slugErrors = $this->validateUniqueSlugs($db, $post, $blogId);
        if ($slugErrors !== []) {
            return $this->redirectBackWithFormErrors($blogId, $slugErrors);
        }

        $now = date('Y-m-d H:i:s');
        $publishedAt = trim((string) ($post['published_at'] ?? ''));
        $publishedAt = $publishedAt !== '' ? str_replace('T', ' ', $publishedAt) . ':00' : null;

        $db->transStart();

        $blogPayload = [
            'category' => trim((string) $post['category']),
            'author_name' => trim((string) $post['author_name']),
            'status' => (string) $post['status'],
            'is_featured' => isset($post['is_featured']) ? 1 : 0,
            'published_at' => $publishedAt,
            'updated_at' => $now,
        ];

        if ($blogId === null) {
            $blogPayload['thumbnail'] = '';
            $blogPayload['cover_image'] = '';
            $blogPayload['featured_image'] = '';
            $blogPayload['created_at'] = $now;

            $db->table('blogs')->insert($blogPayload);
            $blogId = (int) $db->insertID();
        } else {
            $db->table('blogs')->where('id', $blogId)->update($blogPayload);
        }

        $thumbnail = $this->storeBlogImage($blogId, 'thumbnail', 'thumbnail_file');
        $coverImage = $this->storeBlogImage($blogId, 'cover', 'cover_file');
        $featuredImage = $this->storeBlogImage($blogId, 'featured', 'featured_file');

        $currentThumbnail = trim((string) ($post['current_thumbnail'] ?? ''));
        $currentCover = trim((string) ($post['current_cover_image'] ?? ''));
        $currentFeatured = trim((string) ($post['current_featured_image'] ?? ''));

        $finalThumbnail = $thumbnail ?: $currentThumbnail ?: $coverImage ?: $featuredImage ?: $currentCover ?: $currentFeatured;
        $finalCover = $coverImage ?: $currentCover ?: $thumbnail ?: $featuredImage ?: $currentThumbnail ?: $currentFeatured;
        $finalFeatured = $featuredImage ?: $currentFeatured ?: $coverImage ?: $thumbnail ?: $currentCover ?: $currentThumbnail;

        $db->table('blogs')->where('id', $blogId)->update([
            'thumbnail' => $finalThumbnail,
            'cover_image' => $finalCover,
            'featured_image' => $finalFeatured,
            'updated_at' => $now,
        ]);

        $translations = [
            'vi' => [
                'title' => trim((string) $post['title_vi']),
                'slug' => trim((string) $post['slug_vi']),
                'excerpt' => trim((string) ($post['excerpt_vi'] ?? '')),
                'meta_title' => trim((string) ($post['meta_title_vi'] ?? '')),
                'meta_description' => trim((string) ($post['meta_description_vi'] ?? '')),
                'content' => $this->sanitizeEditorHtml((string) ($post['content_vi'] ?? '')),
            ],
            'en' => [
                'title' => trim((string) (($post['title_en'] ?? '') ?: $post['title_vi'])),
                'slug' => trim((string) (($post['slug_en'] ?? '') ?: $post['slug_vi'])),
                'excerpt' => trim((string) (($post['excerpt_en'] ?? '') ?: ($post['excerpt_vi'] ?? ''))),
                'meta_title' => trim((string) ($post['meta_title_en'] ?? '')),
                'meta_description' => trim((string) ($post['meta_description_en'] ?? '')),
                'content' => $this->sanitizeEditorHtml((string) (($post['content_en'] ?? '') ?: ($post['content_vi'] ?? ''))),
            ],
        ];

        foreach ($translations as $locale => $translation) {
            $this->upsertTranslation($db, $blogId, $locale, $translation, $now);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->redirectBackWithFormErrors($blogId, ['Không thể lưu bài viết blog. Vui lòng kiểm tra lại dữ liệu.']);
        }

        $message = $existing === null
            ? 'Đã tạo bài viết blog #' . $blogId
            : 'Đã cập nhật bài viết blog #' . $blogId;

        $activeImagePaths = array_filter([$finalThumbnail, $finalCover, $finalFeatured]);
        foreach (array_diff($oldImagePaths, $activeImagePaths) as $unusedPath) {
            $this->deleteRelativeFile((string) $unusedPath, 'uploads/blogs/');
        }

        return redirect()->to(site_url('admin/blogs/' . $blogId . '/edit'))->with('success', $message);
    }

    /**
     * @param array<string, mixed> $translation
     */
    private function upsertTranslation($db, int $blogId, string $locale, array $translation, string $now): void
    {
        $payload = [
            'title' => $translation['title'],
            'slug' => $translation['slug'],
            'excerpt' => $translation['excerpt'],
            'content' => $translation['content'],
            'meta_title' => $translation['meta_title'] ?: ($translation['title'] . ' | Travel Plus'),
            'meta_description' => $translation['meta_description'] ?: $translation['excerpt'],
            'updated_at' => $now,
        ];

        $exists = $db->table('blog_translations')
            ->where('blog_id', $blogId)
            ->where('locale', $locale)
            ->countAllResults() > 0;

        if ($exists) {
            $db->table('blog_translations')
                ->where('blog_id', $blogId)
                ->where('locale', $locale)
                ->update($payload);

            return;
        }

        $payload['blog_id'] = $blogId;
        $payload['locale'] = $locale;
        $payload['created_at'] = $now;

        $db->table('blog_translations')->insert($payload);
    }

    /**
     * @param array<string, mixed> $post
     * @return list<string>
     */
    private function validateUniqueSlugs($db, array $post, ?int $blogId): array
    {
        $errors = [];

        $slugMap = [
            'vi' => trim((string) ($post['slug_vi'] ?? '')),
            'en' => trim((string) (($post['slug_en'] ?? '') ?: ($post['slug_vi'] ?? ''))),
        ];

        foreach ($slugMap as $locale => $slug) {
            if ($slug === '') {
                continue;
            }

            $builder = $db->table('blog_translations')
                ->select('blog_id')
                ->where('locale', $locale)
                ->where('slug', $slug);

            if ($blogId !== null) {
                $builder->where('blog_id !=', $blogId);
            }

            $existing = $builder->get()->getRowArray();

            if ($existing !== null) {
                $errors[] = sprintf('Slug %s đã tồn tại cho ngôn ngữ %s.', strtoupper($locale), strtoupper($locale));
            }
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getBlogForAdmin($db, int $blogId): ?array
    {
        $row = $db->table('blogs b')
            ->select('
                b.*,
                vi.title AS title_vi,
                vi.slug AS slug_vi,
                vi.excerpt AS excerpt_vi,
                vi.content AS content_vi,
                vi.meta_title AS meta_title_vi,
                vi.meta_description AS meta_description_vi,
                en.title AS title_en,
                en.slug AS slug_en,
                en.excerpt AS excerpt_en,
                en.content AS content_en,
                en.meta_title AS meta_title_en,
                en.meta_description AS meta_description_en
            ')
            ->join('blog_translations vi', 'vi.blog_id = b.id AND vi.locale = "vi"', 'left')
            ->join('blog_translations en', 'en.blog_id = b.id AND en.locale = "en"', 'left')
            ->where('b.id', $blogId)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        return [
            'category' => (string) ($row['category'] ?? ''),
            'author_name' => (string) ($row['author_name'] ?? ''),
            'thumbnail' => (string) ($row['thumbnail'] ?? ''),
            'cover_image' => (string) ($row['cover_image'] ?? ''),
            'featured_image' => (string) ($row['featured_image'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'is_featured' => (int) ($row['is_featured'] ?? 0),
            'published_at' => $this->toDateTimeLocal((string) ($row['published_at'] ?? '')),
            'title_vi' => (string) ($row['title_vi'] ?? ''),
            'slug_vi' => (string) ($row['slug_vi'] ?? ''),
            'excerpt_vi' => (string) ($row['excerpt_vi'] ?? ''),
            'content_vi' => (string) ($row['content_vi'] ?? ''),
            'meta_title_vi' => (string) ($row['meta_title_vi'] ?? ''),
            'meta_description_vi' => (string) ($row['meta_description_vi'] ?? ''),
            'title_en' => (string) ($row['title_en'] ?? ''),
            'slug_en' => (string) ($row['slug_en'] ?? ''),
            'excerpt_en' => (string) ($row['excerpt_en'] ?? ''),
            'content_en' => (string) ($row['content_en'] ?? ''),
            'meta_title_en' => (string) ($row['meta_title_en'] ?? ''),
            'meta_description_en' => (string) ($row['meta_description_en'] ?? ''),
        ];
    }

    private function hasBlogTables($db): bool
    {
        return $db->tableExists('blogs') && $db->tableExists('blog_translations');
    }

    /**
     * @param list<string>|array<string, string> $errors
     */
    private function redirectBackWithFormErrors(?int $blogId, array $errors)
    {
        return redirect()->back()->withInput()
            ->with('errors', $errors)
            ->with('oldFormData', $this->request->getPost());
    }

    private function toDateTimeLocal(string $datetime): string
    {
        if ($datetime === '') {
            return '';
        }

        $timestamp = strtotime($datetime);

        return $timestamp ? date('Y-m-d\TH:i', $timestamp) : '';
    }

    private function sanitizeEditorHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<!doctype html><html><body><div id="root">' . $html . '</div></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        /** @var DOMElement|null $root */
        $root = $dom->getElementById('root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'a', 'img', 'figure', 'figcaption'];
        $allowedImagePrefixes = [
            base_url('uploads/blogs/'),
            base_url('uploads/blogs-editor/'),
            'uploads/blogs/',
            'uploads/blogs-editor/',
            'http://',
            'https://',
        ];

        $walker = function (\DOMNode $node) use (&$walker, $allowedTags, $allowedImagePrefixes): void {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($node->nodeName);

                if (! in_array($tagName, $allowedTags, true)) {
                    while ($node->firstChild) {
                        $node->parentNode?->insertBefore($node->firstChild, $node);
                    }
                    $node->parentNode?->removeChild($node);
                    return;
                }

                if ($node instanceof DOMElement) {
                    $href = $node->getAttribute('href');
                    $src = $node->getAttribute('src');
                    $alt = $node->getAttribute('alt');

                    foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                        $node->removeAttribute($attribute->nodeName);
                    }

                    if ($tagName === 'a') {
                        $href = trim((string) $href);
                        if ($href !== '' && (str_starts_with($href, 'http://') || str_starts_with($href, 'https://') || str_starts_with($href, '/'))) {
                            $node->setAttribute('href', $href);
                            $node->setAttribute('target', '_blank');
                            $node->setAttribute('rel', 'noopener noreferrer');
                        }
                    }

                    if ($tagName === 'img') {
                        $src = trim((string) $src);
                        $alt = trim((string) $alt);
                        $isAllowed = false;
                        foreach ($allowedImagePrefixes as $prefix) {
                            if ($src !== '' && str_starts_with($src, $prefix)) {
                                $isAllowed = true;
                                break;
                            }
                        }

                        if (! $isAllowed) {
                            $node->parentNode?->removeChild($node);
                            return;
                        }

                        $node->setAttribute('src', $src);
                        if ($alt !== '') {
                            $node->setAttribute('alt', $alt);
                        }
                        $node->setAttribute('loading', 'lazy');
                    }
                }
            }

            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $walker($childNode);
            }
        };

        foreach (iterator_to_array($root->childNodes) as $childNode) {
            $walker($childNode);
        }

        $sanitized = '';
        foreach ($root->childNodes as $childNode) {
            $sanitized .= $dom->saveHTML($childNode);
        }

        libxml_clear_errors();

        return trim($sanitized);
    }

    private function blogUploadDirectory(int $blogId): string
    {
        return rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'blogs'
            . DIRECTORY_SEPARATOR . $blogId;
    }

    private function deleteDirectory(string $directory): void
    {
        if ($directory === '' || ! is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            if (is_file($path)) {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }

    private function deleteRelativeFile(string $relativePath, string $allowedPrefix): void
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath));
        $allowedPrefix = trim(str_replace('\\', '/', $allowedPrefix));
        $absolutePath = $this->resolveManagedFilePath($relativePath, $allowedPrefix);

        if ($absolutePath !== null && is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function resolveManagedFilePath(string $relativePath, string $allowedPrefix): ?string
    {
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            return null;
        }

        $allowedPrefix = rtrim($allowedPrefix, '/') . '/';
        if (! str_starts_with($relativePath, $allowedPrefix)) {
            return null;
        }

        $absolutePath = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $candidate = realpath($absolutePath);
        $root = realpath(rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, trim($allowedPrefix, '/')));

        if ($candidate === false || $root === false) {
            return null;
        }

        $candidate = str_replace('\\', '/', $candidate);
        $root = rtrim(str_replace('\\', '/', $root), '/') . '/';

        return str_starts_with($candidate, $root) ? $candidate : null;
    }

    private function storeBlogImage(int $blogId, string $folder, string $fieldName): string
    {
        return $this->storeUploadedImage('uploads/blogs/' . $blogId . '/' . $folder, $fieldName, 'blog-' . $blogId . '-' . $folder);
    }

    private function storeTempEditorImage(string $fieldName): string
    {
        return $this->storeUploadedImage('uploads/blogs-editor/' . date('Ymd'), $fieldName, 'blog-editor');
    }

    private function storeUploadedImage(string $relativeDir, string $fieldName, string $filePrefix): string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return '';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower((string) $file->getClientExtension());
        $mimeType = (string) $file->getMimeType();

        if (! in_array($extension, $allowedExtensions, true) || ! in_array($mimeType, $allowedMimeTypes, true)) {
            return '';
        }

        if ($file->getSizeByUnit('mb') > 8) {
            return '';
        }

        $absoluteDir = FCPATH . $relativeDir;

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $fileName = $filePrefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($absoluteDir, $fileName);

        return $relativeDir . '/' . $fileName;
    }
}
