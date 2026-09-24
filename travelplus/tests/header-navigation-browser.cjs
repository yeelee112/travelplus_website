const { chromium } = require(process.argv[2] || 'playwright');
const assert = require('node:assert/strict');
const base = process.argv[3] || 'http://127.0.0.1:8097';
(async () => {
  const browser = await chromium.launch({ headless: true, channel: 'msedge' });
  try {
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    await context.route('**/recaptcha/**', route => route.abort());
    await context.route('**/assets/css/style-common.css*', route => route.fulfill({ path: 'public/assets/css/style-common.min.css', contentType: 'text/css' }));
    await context.addCookies([{ name: 'travelplus_locale', value: 'vi', url: base }]);
    const page = await context.newPage();
    await page.goto(base + '/tour-theo-yeu-cau', { waitUntil: 'networkidle' });
    const reject = page.getByRole('button', { name: 'Từ chối tùy chọn', exact: true });
    if (await reject.isVisible()) await reject.click();
    for (const localePath of ['/tour-theo-yeu-cau', '/en/custom-tours']) {
      await page.goto(base + localePath, { waitUntil: 'networkidle' });
      for (const width of [1200, 1280, 1440, 1920]) {
        await page.setViewportSize({ width, height: 1000 });
        const layout = await page.locator('.site-header-shell').evaluate(shell => {
          const rect = shell.getBoundingClientRect();
          const links = [...shell.querySelectorAll('.main-menu > ul > li > a')].filter(a => a.getClientRects().length);
          return { shellWidth: rect.width, overflow: document.documentElement.scrollWidth > innerWidth,
            outside: links.some(a => { const r = a.getBoundingClientRect(); return r.left < rect.left || r.right > rect.right; }),
            lineWrap: links.some(a => a.getBoundingClientRect().height > 65), height: rect.height };
        });
        assert.equal(layout.overflow, false, `${localePath} ${width} page overflow`);
        assert.equal(layout.outside, false, `${localePath} ${width} menu overflow`);
        assert.equal(layout.lineWrap, false, `${localePath} ${width} wrapped label`);
        assert.ok(layout.shellWidth > width * .7, 'Navigation should use available width');
        if (localePath === '/tour-theo-yeu-cau' && [1200, 1440].includes(width)) {
          await page.screenshot({ path: `writable/header-refresh-${width}.png` });
        }
      }
    }
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto(base + '/tour-theo-yeu-cau', { waitUntil: 'networkidle' });
    const trigger = page.locator('.site-header-modern .menu-list > li > a.drop-down').first();
    await trigger.click();
    assert.equal(await page.locator('.site-header-modern li.menu-open > .mega-menu').isVisible(), true);
    await page.screenshot({ path: 'writable/header-refresh-dropdown.png' });
    await trigger.click();
    assert.equal(await page.locator('.site-header-modern li.menu-open').count(), 0);
    for (const route of ['/tour-theo-yeu-cau', '/contact', '/ve-chung-toi', '/dich-vu-visa', '/tour-nuoc-ngoai', '/dieu-khoan-su-dung', '/travelplus-reward', '/tour-mua-thu']) {
      const response = await page.goto(base + route, { waitUntil: 'domcontentloaded' });
      assert.equal(response.status(), 200, route);
      assert.equal(await page.locator('.site-breadcrumb').count(), 1, route);
      assert.equal(await page.locator('.site-breadcrumb [aria-current="page"]').count(), 1, route);
      assert.equal(await page.locator('.site-breadcrumb a').first().innerText(), 'Trang chủ', route);
      assert.equal(await page.locator('.breadcrumb-wrapper, .ct-breadcrumb').count(), 0, route);
    }
    await page.goto(base + '/tour-theo-yeu-cau', { waitUntil: 'networkidle' });
    for (const width of [390, 768, 1024]) {
      await page.setViewportSize({ width, height: 844 });
      await page.locator('.mobile-menu-btn').click();
      assert.equal(await page.locator('.main-menu').evaluate(el => el.classList.contains('show-menu')), true);
      await page.waitForFunction(() => Math.abs(document.querySelector('.main-menu').getBoundingClientRect().left) < 1);
      if (width === 390) await page.screenshot({ path: 'writable/header-refresh-mobile-menu.png' });
      await page.locator('.menu-close-btn').click();
      assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
    }
    await page.goto(base + '/tour-nuoc-ngoai', { waitUntil: 'domcontentloaded' });
    const tourLink = await page.locator('a[href]').evaluateAll(links => links.map(link => link.href).find(href => /\/tour-nuoc-ngoai\/[^/]+\/[^/?]+$/.test(href)));
    if (tourLink) {
      await page.goto(tourLink, { waitUntil: 'domcontentloaded' });
      assert.equal(await page.locator('.site-breadcrumb').count(), 1);
      assert.equal(await page.locator('.tour-detail-hero__breadcrumbs').count(), 0);
      await page.setViewportSize({ width: 390, height: 844 });
      assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
    }
    console.log('PASS: wide navigation in VI/EN, responsive menu labels, dropdown opening/closing, shared breadcrumbs on 8 pages, mobile/tablet navigation.');
  } finally { await browser.close(); }
})();
