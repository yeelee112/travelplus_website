const { chromium } = require('C:/Users/an.chauh/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert = require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try {
 const context=await browser.newContext({viewport:{width:1440,height:1000}});
 await context.addCookies([{name:'travelplus_locale',value:'vi',url:'http://localhost'}]);
 const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://localhost/tour-mua-thu',{waitUntil:'networkidle'});
 const reject=page.getByRole('button',{name:'Từ chối tùy chọn',exact:true});if(await reject.isVisible())await reject.click();
 assert.equal(await page.locator('.autumn-landing h1').count(),1);
 await page.locator('.autumn-landing img').evaluateAll(imgs=>imgs.forEach(i=>i.loading='eager')); await page.waitForFunction(()=>Array.from(document.querySelectorAll('.autumn-landing img')).every(i=>i.complete&&i.naturalWidth>0)); await page.screenshot({path:'writable/autumn-redesign-desktop.png',fullPage:true});
 console.log('Results:',await page.locator('.at-results').innerText());
 assert.equal(await page.locator('.autumn-landing img').evaluateAll(imgs=>imgs.every(i=>i.complete&&i.naturalWidth>0)),true);
 await page.selectOption('[name="destination"]','nhat-ban');await page.selectOption('[name="month"]','10');
 await Promise.all([page.waitForURL('**/tour-mua-thu?destination=nhat-ban&month=10#autumn-tours'),page.locator('.at-search button').click()]);
 assert.match(await page.locator('.at-results').innerText(),/Tháng 10/);
 assert.equal(await page.locator('.at-filters .is-active').innerText(),'Nhật Bản');
 await page.locator('.at-faq summary').first().click();assert.equal(await page.locator('.at-faq details').first().getAttribute('open'),'');
 await page.setViewportSize({width:390,height:844});await page.goto('http://localhost/tour-mua-thu',{waitUntil:'networkidle'});
 assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Mobile overflow');
 assert.equal(await page.locator('.at-mobile-contact').isVisible(),true);
 await page.screenshot({path:'writable/autumn-redesign-mobile.png',fullPage:true});
 await page.screenshot({path:'writable/autumn-redesign-mobile-hero.png'});
 await page.setViewportSize({width:320,height:800});assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'320px overflow');
 await page.goto('http://localhost/en/autumn-tours',{waitUntil:'networkidle'});assert.match(await page.locator('.autumn-landing h1').innerText(),/Autumn/);
 await page.goto('http://localhost/tour-mua-thu?destination=invalid&month=99',{waitUntil:'networkidle'});assert.equal(await page.locator('[name="month"]').inputValue(),'');
 console.log('PASS: desktop, 390px/320px, images, filter submission, FAQ, English, invalid filters. JS errors:',JSON.stringify(errors));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});


