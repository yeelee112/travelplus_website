const { chromium } = require(process.argv[2] || 'playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
  const browser = await chromium.launch({headless: true, channel: 'msedge'});
  try {
    const page = await browser.newPage();
    const source = fs.readFileSync('app/Views/admin/tours/import.php', 'utf8');
    const script = source.match(/<script>([\s\S]*?)<\/script>/)[1];
    for (const scenario of ['success', 'login', 'oversize', 'network']) {
      await page.setContent('<form id="tourDocumentImport" action="https://example.test/admin/tours/create-v2"><input name="csrf_test_name" value="stale"><input id="document" type="file"><textarea id="document-text">Keep my itinerary</textarea><div id="importError" hidden></div><button type="submit">Read</button></form>');
      await page.evaluate(scenario => {
        window.sent = null;
        HTMLFormElement.prototype.submit = function () { window.sent = this.elements.csrf_test_name.value; };
        window.fetch = async () => {
          if (scenario === 'network') throw new TypeError('offline');
          return { redirected: scenario === 'login', ok: true, headers: new Headers({'content-type': 'application/json'}),
            json: async () => ({name: 'csrf_test_name', hash: 'fresh-token', uploadLimit: 20000000, postLimit: scenario === 'oversize' ? 1 : 50000000}) };
        };
      }, scenario);
      await page.addScriptTag({content: script});
      await page.click('button');
      await page.waitForFunction(() => window.sent !== null || !document.getElementById('importError').hidden);
      const state = await page.evaluate(() => ({sent: window.sent, text: document.getElementById('document-text').value, disabled: document.querySelector('button').disabled}));
      assert.equal(state.sent, scenario === 'success' ? 'fresh-token' : null);
      assert.equal(state.text, 'Keep my itinerary');
      if (scenario !== 'success') assert.equal(state.disabled, false);
    }
    console.log('PASS: fresh CSRF before submit; expired login, oversized request and network failure preserve input and allow retry');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
