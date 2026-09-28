const { chromium } = require('C:/Users/an.chauh/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs');
(async()=>{
const browser=await chromium.launch({channel:'msedge',headless:true});
const context=await browser.newContext({viewport:{width:1440,height:1000}});
await context.addCookies([{name:'travelplus_locale',value:'vi',url:'http://localhost'}]);
const page=await context.newPage();let errs=[],failed=[];page.on('pageerror',e=>errs.push(e.message));page.on('response',r=>{if(r.status()>=400)failed.push({url:r.url(),status:r.status()})});
const routes=['/','/tim-kiem-tour','/tour-mua-thu','/tour-nuoc-ngoai','/tour-trong-nuoc','/ve-chung-toi','/dich-vu-visa','/dich-vu-mice','/dich-vu-ve-may-bay','/dich-vu-van-chuyen','/dich-vu-dich-thuat','/dich-vu-khach-san','/cam-hung-du-lich','/tour-theo-yeu-cau','/contact','/travelplus-reward','/account/login','/account/register','/booking/lookup','/dieu-khoan-su-dung','/chinh-sach-bao-mat','/en','/en/tour-search','/en/autumn-tours','/admin/tours','/booking/checkout','/audit-page-not-found'];
let results=[],tourLinks=[];
for(const route of routes){errs=[];failed=[];try{
 await page.setViewportSize({width:1440,height:1000});const response=await page.goto('http://localhost'+route,{waitUntil:'load',timeout:25000});
 const desktop=await page.evaluate(()=>({title:document.title,h1:[...document.querySelectorAll('h1')].map(e=>e.innerText),width:document.documentElement.scrollWidth,description:document.querySelector('meta[name="description"]')?.content,canonical:document.querySelector('link[rel="canonical"]')?.href,imgs:[...document.images].filter(i=>i.complete&&i.currentSrc&&!i.naturalWidth).map(i=>i.currentSrc)}));
 if(route==='/tim-kiem-tour')tourLinks=await page.locator('a[href*="/tour/"]').evaluateAll(a=>[...new Set(a.map(x=>x.href))].slice(0,5));
 await page.setViewportSize({width:375,height:812});const mobile=await page.evaluate(()=>({width:document.documentElement.scrollWidth,overflow:[...document.querySelectorAll('body *')].filter(e=>{let r=e.getBoundingClientRect(),s=getComputedStyle(e);return r.width&&r.right>innerWidth+3&&s.position!=='fixed'&&s.visibility!=='hidden'&&s.display!=='none'}).slice(0,6).map(e=>({tag:e.tagName,cls:e.className}))}));
 if(['/','/tim-kiem-tour','/tour-mua-thu','/contact','/account/login'].includes(route))await page.screenshot({path:'writable/audit-'+(route.replaceAll('/','_')||'home')+'-mobile.png',fullPage:true});
 const result={route,status:response.status(),finalUrl:page.url(),desktop,mobile,errors:[...errs],failed:[...failed]};results.push(result);console.log(JSON.stringify({route,status:result.status,overflow:mobile.width,errors:errs,failed:failed.length}));
 }catch(e){results.push({route,error:e.message});console.log(route,e.message)} }
fs.writeFileSync('writable/site-audit-results.json',JSON.stringify({results,tourLinks},null,2));await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
