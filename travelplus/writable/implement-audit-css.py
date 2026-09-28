from pathlib import Path
p=Path('app/Views/partials/header.php');s=p.read_text(encoding='utf-8').replace("$currentLocale === 'en'", "$locale === 'en'");p.write_text(s,encoding='utf-8')
common='''
/* Keyboard navigation and the shared content entry point. */
.site-skip-link { position:fixed; top:12px; left:12px; z-index:100000; padding:12px 18px; background:#fff; color:#075a78; border:2px solid #0086ad; border-radius:8px; transform:translateY(-160%); font-size:14px; font-weight:600; }
.site-skip-link:focus { transform:translateY(0); }
#site-main-content { scroll-margin-top:100px; }
#site-main-content:focus { outline:none; }
.site-header-modern .mobile-menu-btn { border:0; padding:0; }
.site-header-modern .menu-close-btn { padding:0; background:#fff; }
.site-header-modern :is(.mobile-menu-btn,.menu-close-btn,.dropdown-icon):focus-visible { outline:2px solid #007da5; outline-offset:3px; }
'''
home='''
/* Compact home hierarchy: tour selection first, supporting services second. */
.home-hero-rotation { display:inline-flex; align-items:center; gap:6px; min-height:36px; padding:6px 10px; margin:0 0 16px; border:1px solid #ffffff80; border-radius:8px; background:#13394bd9; color:#fff; font-size:12px; line-height:1.4; }
.home-hero-rotation[hidden] { display:none; }
.home-hero-rotation:hover { background:#13394b; }
.home-services-compact__grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
.home-services-compact__card { display:flex; align-items:flex-start; gap:12px; padding:20px; border:1px solid #dde9ee; border-radius:14px; background:#fff; color:#183c4e; }
.home-services-compact__card:hover { border-color:#82becf; background:#f5fbfd; color:#075e7b; }
.home-services-compact__card > i { display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; font-size:22px; line-height:1; margin-top:3px; color:#007da5; }
.home-services-compact__card > i:last-child { font-size:16px; margin-left:auto; }
.home-services-compact__card > span { display:grid; gap:6px; }
.home-services-compact__card strong { font-size:17px; line-height:1.3; }
.home-services-compact__card span span { font-size:14px; line-height:1.5; color:#546c79; }
.home-services-compact__card:focus-visible,.home-more-stories summary:focus-visible { outline:2px solid #0086ad; outline-offset:4px; }
.home-more-stories { margin-top:24px; margin-bottom:40px; }
.home-more-stories > summary { display:flex; align-items:center; justify-content:space-between; gap:12px; min-height:48px; padding:16px 0; border-top:1px solid #dce8ed; border-bottom:1px solid #dce8ed; color:#315363; font-size:15px; font-weight:600; cursor:pointer; list-style:none; }
.home-more-stories > summary::-webkit-details-marker { display:none; }
.home-more-stories[open] > summary i { transform:rotate(180deg); }
.home-page .home-tour-section { padding-top:32px; padding-bottom:32px; }
.home-page .home-hero-trust { padding:16px; }
.home-page .home-hero-trust strong { font-size:13px; line-height:1.4; }
.home-page .home-hero-trust small { font-size:12px; line-height:1.5; }
@media(max-width:767px) {
 .home-services-compact__grid { grid-template-columns:1fr; gap:10px; }
 .home-services-compact__card { padding:16px; }
 .home-page .home-hero-trust { grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px 12px; }
 .home-page .home-hero-trust li { align-items:flex-start; padding:0; border:0; gap:8px; }
 .home-page .home-hero-trust li > i { width:28px; height:28px; flex:0 0 28px; font-size:14px; }
 .home-page .home-section { margin-top:0; padding-top:28px; padding-bottom:28px; }
 .home-more-stories { margin-top:12px; margin-bottom:24px; }
 .home-more-stories > summary { font-size:14px; }
}
'''
for name,addition in [('style-common.css',common),('style-home.css',home),('style.css',common+home)]:
 p=Path('public/assets/css')/name;p.write_text(p.read_text(encoding='utf-8')+'\n'+addition,encoding='utf-8')
