(() => {
  const result = document.querySelector('.ct-success, .ct-card .alert-danger');
  if (result) {
    result.setAttribute('tabindex', '-1');
    result.focus({ preventScroll: true });
    result.scrollIntoView({ block: 'center' });
  }
  const form = document.getElementById('customTourForm');
  if (!form) return;
  const en = form.dataset.locale === 'en';
  const text = (vi, english) => en ? english : vi;
  const steps = [...form.querySelectorAll('[data-step]')];
  const labels = [...form.querySelectorAll('[data-step-label]')];
  const back = form.querySelector('.ct-back');
  const next = form.querySelector('.ct-next');
  const submit = form.querySelector('.ct-submit');
  const error = form.querySelector('.ct-error');
  const field = name => form.elements.namedItem(name);
  let current = 0;
  let busy = false;
  form.noValidate = true;
  const childAges = field('child_ages').closest('label');
  const updateChildren = () => { childAges.hidden = Number(field('children').value) <= 0; };
  field('children').addEventListener('input', updateChildren);
  updateChildren();
  const summary = () => {
    const unknown = text('Cần tư vấn', 'Help me choose');
    const choice = form.querySelector('[name="interests"]:checked');
    document.getElementById('tripSummary').textContent = [
      `${text('Điểm đến', 'Destination')}: ${field('destination').value.trim() || unknown}`,
      `${text('Khởi hành từ', 'Departing from')}: ${field('departure').value.trim() || unknown}`,
      `${text('Thời gian', 'Travel period')}: ${field('estimated_time').value.trim() || unknown} · ${field('trip_length').value.trim() || unknown}`,
      `${text('Số khách', 'Travelers')}: ${field('adults').value} ${text('người lớn', 'adults')}, ${field('children').value} ${text('trẻ em', 'children')}`,
      ...(Number(field('children').value) > 0 ? [`${text('Độ tuổi trẻ em', 'Children’s ages')}: ${field('child_ages').value.trim() || unknown}`] : []),
      `${text('Ngân sách / người (VND)', 'Budget / person (VND)')}: ${field('budget').selectedOptions[0].textContent}`,
      `${text('Trải nghiệm', 'Experience')}: ${choice?.nextElementSibling.textContent || unknown}`,
    ].join('\n');
  };
  const show = (index, focus = true) => {
    current = index;
    steps.forEach((step, i) => { step.hidden = i !== index; });
    labels.forEach((label, i) => {
      label.classList.toggle('is-active', i <= index);
      if (i === index) label.setAttribute('aria-current', 'step');
      else label.removeAttribute('aria-current');
    });
    back.hidden = index === 0;
    next.hidden = index === 2;
    submit.hidden = index !== 2;
    if (index === 2) summary();
    if (focus) steps[index].querySelector('legend').focus();
  };
  const validate = index => {
    const invalid = [...steps[index].querySelectorAll('input, select, textarea')].find(input => !input.checkValidity());
    if (invalid) { show(index); invalid.reportValidity(); return false; }
    return true;
  };
  back.addEventListener('click', () => show(Math.max(0, current - 1)));
  next.addEventListener('click', () => { if (validate(current)) show(current + 1); });
  document.querySelectorAll('[data-interest]').forEach(link => {
    link.addEventListener('click', () => {
      const choice = form.querySelector(`[name="interests"][value="${link.dataset.interest}"]`);
      if (choice) choice.checked = true;
      show(0, false);
    });
  });
  field('phone').addEventListener('input', () => field('phone').setCustomValidity(''));
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (busy) return;
    if (current < 2) { if (validate(current)) show(current + 1); return; }
    const normalized = field('phone').value.trim().replace(/[^\d+]/g, '').replace(/^(\+84|84)/, '0');
    field('phone').setCustomValidity(/^(?:0(?:2\d{8,9}|[35789]\d{8}))$/.test(normalized) ? '' : text('Vui lòng nhập số điện thoại Việt Nam hợp lệ.', 'Please enter a valid Vietnamese phone number.'));
    for (let i = 0; i < steps.length; i++) if (!validate(i)) return;
    error.hidden = true;
    busy = true;
    submit.disabled = true;
    back.disabled = true;
    const original = submit.textContent;
    submit.textContent = text('Đang gửi…', 'Sending…');
    try {
      if (!form.dataset.key || !window.grecaptcha) throw new Error('captcha unavailable');
      const token = await Promise.race([
        new Promise((resolve, reject) => window.grecaptcha.ready(() => window.grecaptcha.execute(form.dataset.key, { action: 'contact' }).then(resolve, reject))),
        new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 15000))
      ]);
      if (!token) throw new Error('empty token');
      field('recaptcha_token').value = token;
      HTMLFormElement.prototype.submit.call(form);
    } catch (_) {
      busy = false;
      submit.disabled = false;
      back.disabled = false;
      submit.textContent = original;
      error.textContent = text('Chưa thể xác minh yêu cầu. Vui lòng thử lại hoặc liên hệ hotline / Zalo ở cuối trang.', 'Verification is unavailable. Please try again or contact us by phone / Zalo in the footer.');
      error.hidden = false;
    }
  });
  show(0, false);
})();
