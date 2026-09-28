from pathlib import Path
p=Path('app/Views/partials/header.php');s=p.read_text(encoding='utf-8');s=s.replace('<div class="main-menu">','<div class="main-menu" id="site-navigation" aria-label="<?= $currentLocale === \'en\' ? \'Main navigation\' : \'Điều hướng chính\' ?>">',1);s=s.replace('<div class="menu-close-btn"><i class="bi bi-x"></i></div>','<button type="button" class="menu-close-btn" aria-label="<?= $currentLocale === \'en\' ? \'Close menu\' : \'Đóng menu\' ?>"><i class="bi bi-x" aria-hidden="true"></i></button>');s=s.replace('<div class="sidebar-button mobile-menu-btn">','<button type="button" class="sidebar-button mobile-menu-btn" aria-controls="site-navigation" aria-expanded="false" aria-label="<?= $currentLocale === \'en\' ? \'Open menu\' : \'Mở menu\' ?>">');anchor='</svg>\n            </div>\n        </div>\n        </div>';assert anchor in s;s=s.replace(anchor,'</svg>\n            </button>\n        </div>\n        </div>',1);p.write_text(s,encoding='utf-8')
p=Path('app/Views/layouts/main.php');s=p.read_text(encoding='utf-8');s=s.replace('<?php if ($showLanguageEntry): ?>','<a class="site-skip-link" href="#site-main-content"><?= $currentLocale === \'en\' ? \'Skip to content\' : \'Bỏ qua menu, đến nội dung\' ?></a>\n<?php if ($showLanguageEntry): ?>',1);s=s.replace('<?= $contentSection ?>', '''<?php $contentWrapperTag = preg_match('/<main\\b/i', $contentSection) === 1 ? 'div' : 'main'; ?>
<<?= $contentWrapperTag ?> id="site-main-content" tabindex="-1">
<?= $contentSection ?>
</<?= $contentWrapperTag ?>>''',1);p.write_text(s,encoding='utf-8')
p=Path('app/Views/sections/hero-search.php');s=p.read_text(encoding='utf-8');s=s.replace('<div class="home-modern-hero__content">','''<button type="button" class="home-hero-rotation" data-hero-toggle hidden aria-pressed="false"
            data-pause-label="<?= $locale === 'en' ? 'Pause slideshow' : 'Dừng chuyển ảnh' ?>"
            data-play-label="<?= $locale === 'en' ? 'Play slideshow' : 'Chạy chuyển ảnh' ?>">
            <i class="bi bi-pause-fill" aria-hidden="true"></i><span><?= $locale === 'en' ? 'Pause slideshow' : 'Dừng chuyển ảnh' ?></span>
        </button>
        <div class="home-modern-hero__content">''',1);p.write_text(s,encoding='utf-8')
p=Path('public/assets/js/main.js');s=p.read_text(encoding='utf-8');start=s.index('  if (mobileMenuBtn && mainMenu) {');end=s.index('  /* =====================================================\n     LANGUAGE DROPDOWN',start)
s=s[:start]+'''  if (mobileMenuBtn && mainMenu) {
    const mobileViewport = window.matchMedia("(max-width: 1199px)");
    let previousOverflow = "";
    let menuOpen = false;
    const setMobileMenu = (open, restoreFocus = true) => {
      const next = open && mobileViewport.matches;
      if (next && !menuOpen) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
      } else if (!next && menuOpen) {
        document.body.style.overflow = previousOverflow;
      }
      menuOpen = next;
      mainMenu.classList.toggle("show-menu", next);
      mainMenu.inert = mobileViewport.matches && !next;
      mobileMenuBtn.setAttribute("aria-expanded", String(next));
      if (next) {
        mainMenu.setAttribute("role", "dialog");
        mainMenu.setAttribute("aria-modal", "true");
        menuCloseBtn?.focus();
      } else {
        mainMenu.removeAttribute("role");
        mainMenu.removeAttribute("aria-modal");
        if (restoreFocus && mobileViewport.matches) mobileMenuBtn.focus();
      }
    };
    mobileMenuBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      setMobileMenu(!menuOpen);
    });
    menuCloseBtn?.addEventListener("click", () => setMobileMenu(false));
    document.addEventListener("click", (event) => {
      if (menuOpen && !mainMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) setMobileMenu(false);
    });
    document.addEventListener("keydown", (event) => {
      if (!menuOpen) return;
      if (event.key === "Escape") {
        event.preventDefault();
        setMobileMenu(false);
      } else if (event.key === "Tab") {
        const focusable = [...mainMenu.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select, [tabindex="0"]')]
          .filter(el => el.getClientRects().length && getComputedStyle(el).visibility !== "hidden");
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
      }
    });
    mobileViewport.addEventListener("change", () => setMobileMenu(false, false));
    setMobileMenu(false, false);
  }

''' +s[end:]
# Make existing icon toggles operable from keyboard, maintaining their existing click handlers.
anchor='  qsa("header.site-header-modern .main-menu > .menu-list > .menu-item-has-children").forEach'
insert='''  qsa("header.site-header-modern .dropdown-icon").forEach((toggle, index) => {
    const panel = toggle.parentElement.querySelector(":scope > .mega-menu, :scope > .sub-menu, :scope > ul");
    if (!panel) return;
    panel.id ||= `navigation-submenu-${index}`;
    toggle.setAttribute("role", "button");
    toggle.setAttribute("tabindex", "0");
    toggle.setAttribute("aria-controls", panel.id);
    toggle.setAttribute("aria-expanded", "false");
    const label = toggle.parentElement.querySelector(":scope > a, :scope > h6, :scope > h5")?.textContent.trim() || "Menu";
    toggle.setAttribute("aria-label", label);
    toggle.addEventListener("keydown", event => {
      if (event.key === "Enter" || event.key === " ") { event.preventDefault(); toggle.click(); }
    });
    toggle.addEventListener("click", () => {
      if (window.innerWidth < 1200) toggle.setAttribute("aria-expanded", String(!toggle.classList.contains("active")));
    });
  });

'''
assert anchor in s;s=s.replace(anchor,insert+anchor,1)
start=s.index('  if (heroRotator) {');end=s.index('  const sliderEl =',start)
s=s[:start]+'''  if (heroRotator) {
    const slides = [...heroRotator.querySelectorAll("img")];
    const toggle = document.querySelector("[data-hero-toggle]");
    const motionPreference = window.matchMedia("(prefers-reduced-motion: reduce)");
    const interval = Math.max(5000, Number(heroRotator.dataset.interval) || 7000);
    let paused = motionPreference.matches;
    let index = 0;
    let timer;
    let generation = 0;
    const loads = new WeakMap();
    const loadSlide = slide => {
      if (loads.has(slide)) return loads.get(slide);
      const pending = new Promise(resolve => {
        if (!slide.dataset.heroSrc) { resolve(slide.complete && slide.naturalWidth > 0); return; }
        const finish = ok => {
          slide.removeEventListener("load", loaded);
          slide.removeEventListener("error", failed);
          resolve(ok);
        };
        const loaded = () => finish(true);
        const failed = () => { loads.delete(slide); finish(false); };
        slide.addEventListener("load", loaded);
        slide.addEventListener("error", failed);
        slide.loading = "eager";
        if (slide.dataset.heroSrcset) slide.srcset = slide.dataset.heroSrcset;
        slide.src = slide.dataset.heroSrc;
      });
      loads.set(slide, pending);
      return pending;
    };
    const schedule = () => {
      clearTimeout(timer);
      const currentGeneration = ++generation;
      if (paused || document.hidden || slides.length < 2) return;
      timer = setTimeout(async () => {
        const next = (index + 1) % slides.length;
        const ready = await loadSlide(slides[next]);
        if (currentGeneration !== generation || paused || document.hidden) return;
        if (ready) {
          slides[index].classList.remove("is-active");
          slides[next].classList.add("is-active");
          index = next;
        }
        schedule();
      }, interval);
    };
    const updateToggle = () => {
      if (!toggle) return;
      toggle.hidden = slides.length < 2;
      toggle.setAttribute("aria-pressed", String(paused));
      toggle.querySelector("span").textContent = paused ? toggle.dataset.playLabel : toggle.dataset.pauseLabel;
      toggle.querySelector("i").className = paused ? "bi bi-play-fill" : "bi bi-pause-fill";
    };
    toggle?.addEventListener("click", () => { paused = !paused; updateToggle(); schedule(); });
    motionPreference.addEventListener("change", event => { paused = event.matches; updateToggle(); schedule(); });
    document.addEventListener("visibilitychange", schedule);
    updateToggle();
    schedule();
  }

''' +s[end:];p.write_text(s,encoding='utf-8')
