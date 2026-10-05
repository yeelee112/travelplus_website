<div class="border rounded p-3 mb-3" data-seo-kind="<?= esc($kind ?? 'blog', 'attr') ?>" data-seo-editor="<?= esc($locale, 'attr') ?>">
    <?php if (($kind ?? 'blog') === 'blog'): ?>
    <div class="mb-3" data-seo-suggestions data-endpoint="<?= esc(site_url('admin/blogs/seo-suggestions'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>">
        <button type="button" class="btn btn-outline-primary" data-suggest-seo>Gợi ý từ nội dung <?= strtoupper(esc($locale)) ?></button>
        <p class="form-text mt-2">AI đọc tiêu đề, tóm tắt và nội dung bản <?= strtoupper(esc($locale)) ?> để đề xuất. Bạn xem và chỉnh trước khi áp dụng; không tự lưu hay đăng bài. Gợi ý không có dữ liệu lượng tìm kiếm.</p>
        <p class="small mb-2" role="status" aria-live="polite" data-suggest-status></p>
        <div class="border rounded p-3" data-suggest-preview hidden>
            <strong>Chọn mục muốn áp dụng</strong>
            <p class="form-text">Mặc định chỉ chọn các ô đang trống. Chọn ô đã có nội dung sẽ thay thế giá trị hiện tại.</p>
            <div data-suggest-fields></div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-apply-seo>Áp dụng mục đã chọn</button>
                <button type="button" class="btn btn-outline-secondary" data-dismiss-seo>Bỏ gợi ý</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="focus_keyword_<?= esc($locale) ?>">Từ khóa chính <?= strtoupper(esc($locale)) ?></label>
            <input id="focus_keyword_<?= esc($locale) ?>" name="focus_keyword_<?= esc($locale) ?>" class="form-control" maxlength="150" value="<?= esc($fv('focus_keyword_' . $locale), 'attr') ?>" placeholder="<?= $locale === 'vi' ? 'VD: du lịch Nhật Bản mùa thu' : 'e.g. Japan autumn travel' ?>">
            <div class="form-text">Một cụm từ thể hiện đúng nhu cầu tìm thông tin. Dùng tự nhiên trong nội dung, không lặp để đạt mật độ.</div>
        </div>
        <div class="col-md-6">
            <label for="secondary_keywords_<?= esc($locale) ?>">Từ khóa phụ <?= strtoupper(esc($locale)) ?></label>
            <textarea id="secondary_keywords_<?= esc($locale) ?>" name="secondary_keywords_<?= esc($locale) ?>" class="form-control" maxlength="1000" placeholder="Ngăn cách bằng dấu phẩy hoặc xuống dòng"><?= esc($fv('secondary_keywords_' . $locale)) ?></textarea>
            <div class="form-text">Các chủ đề liên quan để triển khai nội dung; nên chọn 3–5 cụm phù hợp với nội dung bài viết.</div>
        </div>
        <div class="col-12">
            <p class="form-text mb-1">Gợi ý biên tập, không phải điểm SEO hay cam kết thứ hạng. Các từ khóa này chỉ lưu trong quản trị, không xuất thành meta keywords.</p>
            <ul class="small mb-0" data-seo-checks></ul>
        </div>
        <div class="col-12">
            <label for="social_hashtags_<?= esc($locale) ?>">Hashtag dùng khi đăng mạng xã hội</label>
            <input id="social_hashtags_<?= esc($locale) ?>" name="social_hashtags_<?= esc($locale) ?>" class="form-control" maxlength="1000" value="<?= esc($fv('social_hashtags_' . $locale), 'attr') ?>" placeholder="#TravelPlus #DuLichNhatBan #MuaThu">
            <div class="form-text mb-2">Mỗi hashtag viết liền, cách nhau bằng khoảng trắng. Chỉ lưu để sao chép khi đăng bài; không tự hiển thị trên trang bài viết.</div>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-copy-hashtags>Sao chép hashtag</button>
            <span class="small ms-2" role="status" data-hashtag-status></span>
        </div>
    </div>
</div>
