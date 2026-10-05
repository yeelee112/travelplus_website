(() => {
    'use strict';
    const labels = { focus_keyword: 'Từ khóa chính', secondary_keywords: 'Từ khóa phụ', social_hashtags: 'Hashtag', meta_title: 'Meta title', meta_description: 'Meta description' };
    const limits = { focus_keyword: 150, secondary_keywords: 1000, social_hashtags: 1000, meta_title: 255, meta_description: 500 };
    document.querySelectorAll('[data-seo-suggestions]').forEach(box => {
        const locale = box.closest('[data-seo-editor]').dataset.seoEditor;
        const form = box.closest('form');
        const field = key => form.querySelector(`[name="${key}_${locale}"]`);
        const button = box.querySelector('[data-suggest-seo]');
        const status = box.querySelector('[data-suggest-status]');
        const preview = box.querySelector('[data-suggest-preview]');
        const fields = box.querySelector('[data-suggest-fields]');
        const source = () => ({
            title: field('title')?.value || '', excerpt: field('excerpt')?.value || '',
            content: field('content')?.closest('[data-tab-panel]')?.querySelector('.js-editor')?.innerHTML || field('content')?.value || ''
        });
        let snapshot = '', originals = {}, rows = [];
        button.addEventListener('click', async () => {
            const article = source();
            const plain = new DOMParser().parseFromString(article.content, 'text/html').body.textContent || '';
            if (article.title.trim().length < 3 || plain.trim().length < 100) {
                status.textContent = 'Hãy nhập tiêu đề và ít nhất 100 ký tự nội dung cho ngôn ngữ này trước.'; return;
            }
            if (article.content.length > 60000) { status.textContent = 'Bài viết vượt giới hạn 60.000 ký tự cho một lần gợi ý. Bạn vẫn có thể nhập SEO thủ công.'; return; }
            snapshot = JSON.stringify(article);
            originals = Object.fromEntries(Object.keys(labels).map(key => [key, field(key).value]));
            preview.hidden = true; button.disabled = true; box.setAttribute('aria-busy', 'true');
            status.textContent = 'Đang đọc nội dung và tạo gợi ý…';
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 55000);
            try {
                const body = new FormData();
                Object.entries(article).forEach(([key, value]) => body.append(key, value));
                body.append('locale', locale);
                const token = form.querySelector(`[name="${box.dataset.csrfName}"]`);
                if (token) body.append(token.name, token.value);
                const response = await fetch(box.dataset.endpoint, { method: 'POST', body, signal: controller.signal, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (response.redirected) throw new Error('Phiên đăng nhập đã hết hạn. Hãy đăng nhập lại ở tab khác rồi thử lại.');
                if (!(response.headers.get('content-type') || '').includes('application/json')) throw new Error('Chưa nhận được phản hồi hợp lệ. Hãy kiểm tra phiên đăng nhập rồi thử lại.');
                const data = await response.json();
                if (data.csrf && token) token.value = data.csrf;
                if (!response.ok) throw new Error(data.error || 'Chưa tạo được gợi ý. Vui lòng thử lại.');
                if (snapshot !== JSON.stringify(source())) throw new Error('Nội dung bài đã thay đổi trong lúc chờ. Hãy tạo lại gợi ý cho bản mới.');
                if (!data.suggestions || Object.keys(labels).some(key => typeof data.suggestions[key] !== 'string')) throw new Error('Gợi ý chưa đầy đủ. Vui lòng thử lại.');
                fields.replaceChildren(); rows = [];
                Object.entries(labels).forEach(([key, label]) => {
                    const wrap = document.createElement('div'); wrap.className = 'mb-3';
                    const choice = document.createElement('label'); choice.className = 'd-flex align-items-center gap-2';
                    const check = document.createElement('input'); check.type = 'checkbox'; check.className = 'form-check-input mt-0'; check.checked = !field(key).value.trim();
                    choice.append(check, document.createTextNode(label + (field(key).value.trim() ? ' — đã có nội dung' : ' — đang trống')));
                    const input = document.createElement('textarea'); input.className = 'form-control'; input.rows = 2; input.maxLength = limits[key]; input.value = data.suggestions[key]; input.setAttribute('aria-label', 'Gợi ý ' + label);
                    wrap.append(choice, input); fields.append(wrap); rows.push({ key, check, input });
                });
                preview.hidden = false;
                status.textContent = 'Đã có gợi ý. Kiểm tra địa danh và thông tin thực tế trước khi áp dụng.';
            } catch (error) {
                status.textContent = error.name === 'AbortError' ? 'AI phản hồi quá lâu. Bạn có thể thử lại; các ô SEO vẫn giữ nguyên.' : error.message;
            } finally { clearTimeout(timeout); button.disabled = false; box.removeAttribute('aria-busy'); }
        });
        box.querySelector('[data-apply-seo]').addEventListener('click', () => {
            if (snapshot !== JSON.stringify(source())) { status.textContent = 'Bài viết đã thay đổi. Hãy tạo lại gợi ý trước khi áp dụng.'; return; }
            const selected = rows.filter(row => row.check.checked);
            if (!selected.length) { status.textContent = 'Chọn ít nhất một mục để áp dụng.'; return; }
            if (selected.some(row => field(row.key).value !== originals[row.key])) { status.textContent = 'Một ô SEO đã được chỉnh sau khi yêu cầu gợi ý. Hãy tạo lại gợi ý để tránh mất nội dung mới.'; return; }
            if (selected.some(row => row.input.value.length > limits[row.key])) { status.textContent = 'Gợi ý vượt giới hạn ký tự. Hãy rút gọn trước khi áp dụng.'; return; }
            selected.forEach(({key, input}) => {
                field(key).value = input.value.trim();
                field(key).dispatchEvent(new Event('input', { bubbles: true }));
                field(key).dispatchEvent(new Event('change', { bubbles: true }));
            });
            preview.hidden = true;
            status.textContent = `Đã điền ${selected.length} mục. Bấm Lưu bài viết để lưu vào website.`;
        });
        box.querySelector('[data-dismiss-seo]').addEventListener('click', () => { preview.hidden = true; status.textContent = 'Đã bỏ gợi ý. Các ô SEO giữ nguyên.'; });
    });
})();
