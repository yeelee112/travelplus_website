const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/assets/js/site-performance.js', 'utf8');
function setup(cookie) {
    const observed = {}, events = {}, reports = [];
    class PerformanceObserver {
        static supportedEntryTypes = ['largest-contentful-paint', 'layout-shift'];
        constructor(callback) { this.callback = callback; }
        observe({type}) { observed[type] = entries => this.callback({getEntries: () => entries}); }
    }
    const document = { cookie, visibilityState:'hidden', querySelector: () => null, body:{classList:{contains: () => true}}, addEventListener:(name,fn)=>events[name]=fn };
    const window = {PerformanceObserver, travelplusTrackEvent:(...args)=>reports.push(args), addEventListener:(name,fn)=>events[name]=fn};
    vm.runInNewContext(source, {window, document, PerformanceObserver, performance:{getEntriesByType:()=>[{responseStart:200,requestStart:20,domContentLoadedEventEnd:550}]}, matchMedia:()=>({matches:true})});
    return {observed,events,reports};
}
test('does not report before analytics consent', () => {
    const s=setup('tp_cookie_consent=necessary'); s.events.pagehide(); assert.equal(s.reports.length,0);
});
test('reports once with timings and the largest CLS session window', () => {
    const s=setup('tp_cookie_consent=necessary%2Canalytics');
    s.observed['largest-contentful-paint']([{startTime:810}]);
    s.observed['layout-shift']([{startTime:100,value:.1,hadRecentInput:false},{startTime:400,value:.05,hadRecentInput:false},{startTime:600,value:.7,hadRecentInput:true},{startTime:2300,value:.12,hadRecentInput:false}]);
    s.events.visibilitychange(); s.events.pagehide();
    assert.equal(s.reports.length,1); const [event,data]=s.reports[0];
    assert.equal(event,'site_performance'); assert.equal(data.cls,.15); assert.equal(data.lcp_ms,810); assert.equal(data.ttfb_ms,180);
    assert.equal(data.page_type,'home'); assert.equal(data.device_layout,'mobile'); assert.equal('page_url' in data,false);
});
