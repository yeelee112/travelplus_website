/* Shared by initial destination rows and rows added in the tour editor. */
(() => {
  const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
  const slug = value => normalize(value).replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  const aliases = { US: 'Mỹ My USA', AU: 'Úc Uc', GB: 'Anh Scotland UK', KR: 'Hàn Quốc', TW: 'Đài Loan', CZ: 'Séc Czech', AE: 'Dubai UAE' };
  let sequence = 0;

  window.bindTourLocationPicker = row => {
    ['province', 'country'].forEach(type => {
      const group = row.querySelector(`.new-${type}-fields`);
      if (!group || group.dataset.locationPicker) return;
      group.dataset.locationPicker = '1';
      const field = key => group.querySelector(`[name$="[new_${type}_${key}]"]`);
      const code = field('code');
      code.parentElement.className = 'col-md-3';
      code.placeholder = type === 'country' ? 'Mã quốc gia: US' : 'Mã tỉnh/thành: VN-48';
      code.setAttribute('aria-label', type === 'country' ? 'Mã quốc gia (2 chữ cái)' : 'Mã tỉnh/thành trong hệ thống');
      if (type === 'country') {
        code.maxLength = 2;
        code.pattern = '[A-Za-z]{2}';
        code.title = 'Mã quốc gia gồm 2 chữ cái, ví dụ US, CA, AU.';
      }
      code.addEventListener('input', () => { code.value = code.value.toUpperCase(); });
      const panel = document.createElement('div');
      panel.className = 'col-12';
      const id = `location-lookup-${++sequence}`;
      panel.innerHTML = `<div class="p-3 rounded border bg-white">
        <label for="${id}">Tra nhanh ${type === 'country' ? 'quốc gia / vùng lãnh thổ' : 'tỉnh / thành phố'} và tự điền mã</label>
        <input id="${id}" class="form-control mb-2" type="search" autocomplete="off" placeholder="${type === 'country' ? 'Gõ tên hoặc mã: Mỹ, Canada, US…' : 'Gõ tên hoặc mã: Đà Nẵng, Hà Nội, VN-48…'}">
        <select class="form-select" size="4" aria-label="Chọn địa điểm để điền thông tin"></select>
        <small class="text-muted d-block mt-2" aria-live="polite">Chọn một địa điểm để điền tên Việt/Anh, slug và mã. Bạn vẫn có thể sửa các ô bên dưới.</small>
      </div>`;
      group.prepend(panel);
      const search = panel.querySelector('input');
      const select = panel.querySelector('select');
      const status = panel.querySelector('small');
      const entries = window.tourLocationCatalog[type];
      const render = () => {
        const query = normalize(search.value);
        const matches = entries.filter(item => normalize(`${item.vi} ${item.en} ${item.code} ${type === 'country' ? aliases[item.code] || '' : ''}`).includes(query));
        select.replaceChildren(...matches.map(item => new Option(`${item.vi} — ${item.code}`, item.code)));
        select.selectedIndex = -1;
        if (!matches.length) {
          const option = new Option('Không tìm thấy. Bạn có thể nhập thủ công bên dưới.', '');
          option.disabled = true;
          select.append(option);
        }
      };
      search.addEventListener('input', render);
      select.addEventListener('change', () => {
        const item = entries.find(item => item.code === select.value);
        if (!item) return;
        if (type === 'province') {
          const region = row.querySelector('.js-region-select');
          region.value = item.region;
          region.dispatchEvent(new Event('change', { bubbles: true }));
        }
        // A preset creates a new location; do not leave an existing location selected.
        const existing = row.querySelector(type === 'province' ? '.js-province-select' : '.js-country-select');
        if (existing) { existing.value = ''; existing.dataset.selected = ''; }
        const values = { name_vi: item.vi, name_en: item.en, slug_vi: slug(item.vi), slug_en: slug(item.en), code: item.code };
        Object.entries(values).forEach(([key, value]) => {
          field(key).value = value;
          field(key).dispatchEvent(new Event('input', { bubbles: true }));
        });
        status.textContent = `Đã điền ${item.vi} (${item.code}). ${type === 'country' ? 'Chọn đúng châu lục ở phía trên trước khi lưu.' : 'Vùng miền đã được chọn tự động.'}`;
      });
      render();
    });
  };
})();
