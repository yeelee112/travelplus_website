(() => {
    'use strict';
    const plain = value => {
        const doc = new DOMParser().parseFromString(value, 'text/html');
        return (doc.body.textContent || '').normalize('NFC').toLocaleLowerCase().replace(/\s+/g, ' ').trim();
    };
    document.querySelectorAll('[data-seo-editor]').forEach(editor => {
        const locale = editor.dataset.seoEditor;
        const tour = editor.dataset.seoKind === 'tour';
        const field = name => document.querySelector(`[name="${name}_${locale}"]`);
        const value = name => {
            const input = field(name);
            const rich = name === 'content' ? input?.closest('[data-tab-panel]')?.querySelector('.js-editor') : null;
            return rich ? rich.textContent : (input?.value || '');
        };
        const update = () => {
            const keyword = plain(value('focus_keyword'));
            const list = editor.querySelector('[data-seo-checks]');
            list.replaceChildren();
            const add = text => { const li = document.createElement('li'); li.textContent = text; list.append(li); };
            if (!keyword) { add('Nhập từ khóa chính để xem gợi ý. Có thể bỏ trống nếu chưa nghiên cứu từ khóa.'); return; }
            [
                [tour ? 'Tên tour' : 'Tiêu đề bài viết', value(tour ? 'name' : 'title')],
                ['Tiêu đề SEO (dùng tên nếu để trống)', value('meta_title').trim() || value(tour ? 'name' : 'title')],
                ['Mô tả SEO', value('meta_description').trim() || value(tour ? 'short_description' : 'excerpt')],
                [tour ? 'Tổng quan / mô tả tour' : 'Nội dung bài viết', tour ? value('overview') + ' ' + value('description') : value('content')]
            ].forEach(([label, text]) => add(`${label}: ${plain(text).includes(keyword) ? 'đã có cụm từ chính.' : 'chưa thấy cụm từ chính; chỉ bổ sung nếu phù hợp.'}`));
        };
        document.querySelector('form')?.addEventListener('input', update);
        document.querySelector('form')?.addEventListener('change', update);
        document.getElementById('copySeoViToEn')?.addEventListener('click', () => setTimeout(update, 0));
        editor.querySelector('[data-copy-hashtags]').addEventListener('click', async () => {
            const input = field('social_hashtags');
            const seen = new Set();
            const tags = input.value.split(/[\s,;#]+/u).map(s => s.replace(/[^\p{L}\p{N}_]/gu, '')).filter(s => {
                const key = s.toLocaleLowerCase();
                if (!key || seen.has(key)) return false;
                seen.add(key); return true;
            }).map(s => '#' + s).join(' ');
            const status = editor.querySelector('[data-hashtag-status]');
            if (!tags) { status.textContent = 'Chưa có hashtag để sao chép.'; return; }
            input.value = tags;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            try { await navigator.clipboard.writeText(tags); status.textContent = 'Đã sao chép.'; }
            catch { input.focus(); input.select(); status.textContent = 'Nhấn Ctrl+C hoặc sao chép phần đã chọn.'; }
        });
        update();
    });
})();
