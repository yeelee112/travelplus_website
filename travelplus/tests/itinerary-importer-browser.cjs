// Run: node tests/itinerary-importer-browser.cjs <path-to-playwright>
const { chromium } = require(process.argv[2] || 'playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');

(async () => {
  const browser = await chromium.launch({ headless: true, channel: 'msedge' });
  try {
    const page = await browser.newPage();
    const source = fs.readFileSync('app/Views/admin/tours/form.php', 'utf8');
    const functions = source.slice(source.indexOf('function escapeHtml('), source.indexOf('\ninitItineraryImporter();'));
    await page.setContent(`<select id="itineraryImportLocale"><option value="vi">VI</option><option value="en">EN</option></select>
      <div id="itineraryImportContent" contenteditable="true"></div><div id="itineraryImportPreview"></div>
      <div id="itineraryCopyStatus"></div>
      <div id="itineraryRows"></div>
      ${['collectItineraryImport','copyItineraryRich','previewItineraryImport','replaceItineraryImport','appendItineraryImport','clearItineraryImport'].map(id => `<button id="${id}">${id}</button>`).join('')}`);
    await page.addScriptTag({ content: functions + '\ninitItineraryImporter();' });
    const result = await page.evaluate(() => {
      const pasted = '<p>Day 1: Toronto</p><p><span style="font-weight:700;font-style:italic" onclick="alert(1)">CN Tower</span> &amp; lake</p><script>alert(1)</' + 'script><p>Day 2: Ottawa</p><p><i>Breakfast</i></p>';
      const safe = sanitizeImportedHtml(pasted);
      const rows = parseItineraryImportHtml(safe, 'en');
      document.getElementById('itineraryImportContent').innerHTML = safe;
      document.getElementById('itineraryImportLocale').value = 'en';
      return { safe, rows, locale: document.getElementById('itineraryImportLocale').value, editor: document.getElementById('itineraryImportContent').innerHTML };
    });
    assert.equal(result.rows.length, 2);
    assert.match(result.rows[0].description_en, /<(?:em|strong)>/);
    assert.match(result.rows[0].description_en, /<strong>/);
    assert.match(result.rows[0].description_en, /<em>/);
    assert.doesNotMatch(result.safe, /onclick|script|style=/);
    assert.equal(result.locale, 'en');
    assert.match(result.editor, /<em>/);
    // Exercise rich and HTML clipboard paths without changing the system clipboard.
    await page.evaluate(() => {
      window.copied = null;
      Object.defineProperty(navigator, 'clipboard', { value: {
        write: async items => { window.copied = await items[0].getType('text/html').then(blob => blob.text()); },
        writeText: async value => { window.copied = value; },
      }});
      if (!window.ClipboardItem) window.ClipboardItem = class { constructor(items) { this.items = items; } async getType(type) { return this.items[type]; } };
    });
    await page.click('#copyItineraryRich');
    await page.waitForFunction(() => typeof window.copied === 'string');
    assert.match(await page.evaluate(() => window.copied), /<strong>/);

    assert.match(await page.evaluate(() => window.copied), /Day 2/);
    const imported = await page.evaluate(() => {
      window.confirm = () => true;
      window.refreshSummaryMetrics = () => {};
      window.scheduleDraftSave = () => {};
      document.getElementById('itineraryRows').innerHTML = [1, 2].map(day => `<div class="itinerary-row">
        <input name="itinerary_days[${day}][day_number]" value="${day}">
        <input name="itinerary_days[${day}][title_vi]" value="Ngày gốc ${day}">
        <input name="itinerary_days[${day}][title_en]">
        <div class="col-md-6"><textarea name="itinerary_days[${day}][description_vi]">&lt;p&gt;&lt;strong&gt;Nội dung gốc&lt;/strong&gt;&lt;/p&gt;</textarea><div class="js-rich-editor"><p><strong>Nội dung gốc</strong></p></div></div>
        <div class="col-md-6"><textarea name="itinerary_days[${day}][description_en]"></textarea><div class="js-rich-editor"></div></div>
      </div>`).join('');
      importItineraryRows('replace');
      const en = document.querySelector('[name$="[description_en]"]').value;
      const vi = document.querySelector('[name$="[description_vi]"]').value;
      document.getElementById('collectItineraryImport').click();
      return { en, vi, combined: document.getElementById('itineraryImportContent').innerHTML };
    });
    assert.match(imported.en, /<em>/);
    assert.equal(imported.vi, '<p><strong>Nội dung gốc</strong></p>');
    assert.match(imported.combined, /Day 2/);
    assert.match(imported.combined, /<em>/);
    console.log('PASS: formatted paste, day splitting, sanitization, rich copy, English import preserves Vietnamese, gather current itinerary');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
