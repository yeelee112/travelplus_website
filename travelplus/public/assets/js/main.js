document.addEventListener("DOMContentLoaded", () => {
  /* =====================================================
     HELPER
  ===================================================== */
  const qs = (s, p = document) => p.querySelector(s);
  const qsa = (s, p = document) => [...p.querySelectorAll(s)];

  const closeActive = (selector) => {
    qsa(selector).forEach((el) => el.classList.remove("active"));
  };

  /* =====================================================
     HEADER DROPDOWN (language / contact / search)
  ===================================================== */
  qsa('[data-toggle="dropdown"]').forEach((btn) => {
    btn.addEventListener("click", (e) => {
      if (btn.closest(".search-bar")) {
        return;
      }

      e.stopPropagation();

      const wrapper = btn.closest(
        ".language-area, .contact-area, .search-bar, .nav-right",
      );
      if (!wrapper) return;

      const target = wrapper.querySelector(btn.dataset.target);
      if (!target) return;

      const isOpen = target.classList.contains("active");

      closeActive(
        ".language-list.active, .contact-list.active, .search-box.active, .search-input.active",
      );

      if (!isOpen) target.classList.add("active");
    });
  });

  qsa(".search-bar").forEach((bar) => {
    const searchBtn = qs(".search-btn", bar);
    const panel = qs(".search-input", bar);
    const closeBtn = qs(".search-close", bar);

    if (searchBtn && panel) {
      searchBtn.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        const shouldOpen = !panel.classList.contains("active");
        closeActive(".search-input.active");

        if (shouldOpen) {
          panel.classList.add("active");
        }
      });
    }

    if (panel) {
      panel.addEventListener("click", (event) => event.stopPropagation());
    }

    if (closeBtn && panel) {
      closeBtn.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        panel.classList.remove("active");
      });
    }
  });

  /* =====================================================
     MENU CLOSE
  ===================================================== */
  const mobileMenuBtn = qs(".mobile-menu-btn");
  const menuCloseBtn = qs(".menu-close-btn");
  const mainMenu = qs(".main-menu");

  if (mobileMenuBtn && mainMenu) {
    mobileMenuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      mainMenu.classList.add("show-menu");
    });
  }

  if (menuCloseBtn && mainMenu) {
    menuCloseBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      mainMenu.classList.remove("show-menu");
    });
  }

  /* =====================================================
     LANGUAGE DROPDOWN (RIÊNG)
  ===================================================== */
  qsa(".language-area").forEach((area) => {
    const languageBtn = qs(".language-btn", area);
    const languageList = qs(".language-list", area);

    if (!languageBtn || !languageList) return;

    languageBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      languageList.classList.toggle("active");
    });

    languageList.addEventListener("click", (e) => e.stopPropagation());
  });

  qsa(".account-dropdown").forEach((area) => {
    const accountBtn = qs(".account-btn", area);
    const accountList = qs(".account-list", area);

    if (!accountBtn || !accountList) return;

    accountBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      closeActive(".account-list.active");
      accountList.classList.toggle("active");
    });

    accountList.addEventListener("click", (e) => e.stopPropagation());
  });

  /* =====================================================
     MOBILE SUB MENU
  ===================================================== */
  qsa("header .menu-item-has-children").forEach((item) => {
    const dropdownIcon = qs(".dropdown-icon", item);
    const submenu = qs(".sub-menu, .mega-menu", item);

    if (!dropdownIcon || !submenu) return;

    dropdownIcon.addEventListener("click", (e) => {
      if (window.innerWidth >= 992) return;

      e.preventDefault();
      e.stopPropagation();

      item.classList.toggle("active");
      dropdownIcon.classList.toggle("active");

      const isOpen = submenu.style.display === "block";
      submenu.classList.toggle("none", isOpen);
      submenu.style.display = isOpen ? "none" : "block";
    });
  });

  qsa("header .mega-menu .menu-single-item").forEach((item) => {
    const dropdownIcon = qs(".dropdown-icon", item);
    const submenu = qs("ul", item);

    if (!dropdownIcon || !submenu) return;

    dropdownIcon.addEventListener("click", (e) => {
      if (window.innerWidth >= 992) return;

      e.preventDefault();
      e.stopPropagation();

      dropdownIcon.classList.toggle("active");

      const isOpen = submenu.style.display === "block";
      submenu.classList.toggle("none", isOpen);
      submenu.style.display = isOpen ? "none" : "block";
    });
  });

  /* =====================================================
     CUSTOM SELECT (Category / Country / Activity)
  ===================================================== */
  qsa(".category-box, .single-field").forEach((box) => {
    const dropdown = qs(".custom-select-dropdown", box);
    const wrap = qs(".custom-select-wrap", box);
    if (!dropdown || !wrap) return;

    const input = qs("input", dropdown);
    const items = qsa(".option-list .single-item", wrap);
    const countInputs = qsa(".quantity__input", wrap);
    const maxTravelers = 15;
    const summaryCounts = {
      adult: qs('[data-summary="adult"]', box),
      child: qs('[data-summary="child"]', box),
      infant: qs('[data-summary="infant"]', box),
    };

    const getTotalTravelers = () =>
      countInputs.reduce((sum, field) => {
        const value = Number.parseInt(field.value, 10);
        return sum + (Number.isNaN(value) ? 0 : value);
      }, 0);

    const updateTravelerSummary = () => {
      if (countInputs.length === 0) return;

      countInputs.forEach((field) => {
        const quantityName = field.name.replace("_quantity", "");
        const summaryTarget = summaryCounts[quantityName];

        if (!summaryTarget) return;

        const value = Number.parseInt(field.value, 10);
        const normalizedValue = Number.isNaN(value) ? 0 : value;
        summaryTarget.textContent = String(normalizedValue);

        const summaryItem = summaryTarget.closest(".traveler-summary__item");
        if (!summaryItem) return;

        if (quantityName === "adult") {
          summaryItem.style.display = "";
          return;
        }

        summaryItem.style.display = normalizedValue > 0 ? "" : "none";
      });
    };

    dropdown.addEventListener("click", (e) => {
      e.stopPropagation();
      closeActive(".custom-select-wrap.active");
      wrap.classList.toggle("active");
    });

    items.forEach((item) => {
      item.addEventListener("click", () => {
        const text = item.querySelector("h6")?.innerText || item.innerText;
        if (input) input.value = text.trim();
        wrap.classList.remove("active");
      });
    });

    wrap.addEventListener("click", (e) => e.stopPropagation());

    qsa(".guest-quantity__minus, .guest-quantity__plus", wrap).forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        const container = button.closest(".quantity-counter");
        const quantityInput = qs(".quantity__input", container);

        if (!quantityInput) return;

        const currentValue = Number.parseInt(quantityInput.value, 10) || 0;
        const minValue = Number.parseInt(quantityInput.dataset.min ?? "0", 10) || 0;
        let nextValue = button.classList.contains("guest-quantity__minus")
          ? Math.max(minValue, currentValue - 1)
          : currentValue + 1;

        if (button.classList.contains("guest-quantity__plus")) {
          const totalWithoutCurrent = getTotalTravelers() - currentValue;
          nextValue = Math.min(nextValue, maxTravelers - totalWithoutCurrent);
          nextValue = Math.max(minValue, nextValue);
        }

        quantityInput.value = String(nextValue);
        updateTravelerSummary();
      });
    });

    countInputs.forEach((field) => {
      field.addEventListener("click", (e) => e.stopPropagation());
      field.addEventListener("input", () => {
        const minValue = Number.parseInt(field.dataset.min ?? "0", 10) || 0;
        let currentValue = Number.parseInt(field.value, 10);

        if (Number.isNaN(currentValue)) {
          currentValue = minValue;
        }

        if (currentValue < minValue) {
          currentValue = minValue;
        }

        const totalWithoutCurrent = getTotalTravelers() - (Number.parseInt(field.value, 10) || 0);
        const maxAllowedForField = Math.max(minValue, maxTravelers - totalWithoutCurrent);

        if (currentValue > maxAllowedForField) {
          currentValue = maxAllowedForField;
        }

        field.value = String(currentValue);
        updateTravelerSummary();
      });
    });

    updateTravelerSummary();
  });

  /* =====================================================
     DESTINATION DROPDOWN (CHỌN NHANH)
  ===================================================== */
  qsa(".destination-box").forEach((box) => {
    const dropdown = qs(".destination-dropdown", box);
    const wrap = qs(".custom-select-wrap", box);
    const items = qsa(".destination-item", box);

    const input = qs(".main-destination-input", box);
    const nameEl = qs(".dest-name", box);
    const countryEl = qs(".dest-country", box);
    const placeholder = qs(".placeholder-text", box);
    const selectedBox = qs(".destination.selected", box);

    if (!dropdown || !wrap) return;

    dropdown.addEventListener("click", (e) => {
      e.stopPropagation();
      closeActive(".custom-select-wrap.active");
      wrap.classList.toggle("active");
    });

    items.forEach((item) => {
      item.addEventListener("click", (e) => {
        e.stopPropagation();

        nameEl.innerText = item.dataset.name;
        countryEl.innerText = item.dataset.country;
        input.value = item.dataset.country;

        placeholder?.classList.add("hidden");
        selectedBox?.classList.remove("hidden");
        wrap.classList.remove("active");
      });
    });

    wrap.addEventListener("click", (e) => e.stopPropagation());
  });

  /* =====================================================
     FILTER TAB (Tours / Hotels / Visa / Experience)
  ===================================================== */
  const filterTabs = qsa(".filter-item-list .single-item");
  const filterForms = qsa(".filter-input");

  filterTabs.forEach((tab, index) => {
    tab.addEventListener("click", () => {
      filterTabs.forEach((t) => t.classList.remove("active"));
      filterForms.forEach((f) => f.classList.remove("show"));

      tab.classList.add("active");
      filterForms[index]?.classList.add("show");
    });
  });

  /* =====================================================
     CLICK OUTSIDE – ĐÓNG DROPDOWN (CHUNG)
  ===================================================== */
  document.addEventListener("click", () => {
    closeActive(
      ".language-list.active, .contact-list.active, .search-box.active, .search-input.active, .custom-select-wrap.active",
    );
    closeActive(".account-list.active");
  });

  /* =====================================================
     SEARCH DESTINATION
  ===================================================== */

  document.querySelectorAll(".destination-box").forEach((box) => {
    const input = box.querySelector(".destination-input");
    const wrap = box.querySelector(".custom-select-wrap");
    const list = box.querySelector(".option-list-destination");
    const clearBtn = box.querySelector(".clear-destination");
    const form = box.closest("form");

    input.addEventListener("input", () => {
      const keyword = input.value.trim();

      clearBtn.classList.toggle("hidden", keyword.length === 0);
    });

    if (!input || !wrap || !list || !clearBtn) return;

    let timer = null;
    let currentItems = [];

    const truncateText = function (value, maxLength = 42) {
      const text = String(value || "").trim();

      if (text.length <= maxLength) {
        return text;
      }

      return `${text.slice(0, Math.max(0, maxLength - 3)).trimEnd()}...`;
    };

    const getItemLabel = function (item) {
      if (item.type === "country") {
        return item.name;
      }

      if (item.type === "gallery") {
        const tourLabel = truncateText(item.tour || "", 34);
        return tourLabel ? `${item.name} - ${tourLabel}` : item.name;
      }

      if (!item.country || item.country === "undefined") {
        return item.name;
      }

      return `${item.name}, ${item.country}`;
    };

    const selectItem = function (item) {
      input.value = item.name || "";
      wrap.classList.remove("active");
      list.innerHTML = "";
    };

    input.addEventListener("input", () => {
      const keyword = input.value.trim();

      clearTimeout(timer);
      currentItems = [];

      if (keyword.length < 2) {
        wrap.classList.remove("active");
        list.innerHTML = "";
        return;
      }

      wrap.classList.add("active");
      list.innerHTML = `<li class="single-item"><h6>Loading...</h6></li>`;

      timer = setTimeout(() => {
        fetch(`${BASE_URL}api/destinations?q=${encodeURIComponent(keyword)}`)
          .then((res) => res.json())
          .then((data) => renderList(data))
          .catch(() => {
            list.innerHTML = `
              <li class="single-item">
                <h6>Error loading data</h6>
              </li>`;
          });
      }, 300);
    });

    function renderList(data) {
      list.innerHTML = "";
      currentItems = Array.isArray(data) ? data : [];

      if (currentItems.length === 0) {
        list.innerHTML = `
          <li class="single-item">
            <h6>No results found</h6>
          </li>`;
        return;
      }

      currentItems.forEach((item) => {
        const li = document.createElement("li");
        li.className = "single-item";

        li.innerHTML = `<h6>${getItemLabel(item)}</h6>`;

        li.addEventListener("click", () => {
          selectItem(item);
        });

        list.appendChild(li);
      });
    }

    // click ra ngoài → đóng dropdown
    input.addEventListener("keydown", (event) => {
      if (event.key !== "Enter") {
        return;
      }

      if (currentItems.length === 0) {
        return;
      }

      event.preventDefault();
      selectItem(currentItems[0]);

      if (form) {
        form.requestSubmit();
      }
    });

    if (form && form.hasAttribute("data-tour-search-form")) {
      form.addEventListener("submit", (event) => {
        const departureInput = form.querySelector('[name="departure_date"]');
        const keyword = input.value.trim();
        const departureValue = departureInput ? departureInput.value.trim() : "";

        if (keyword === "" && departureValue === "") {
          event.preventDefault();
          input.focus();
          return;
        }

        if (currentItems.length > 0) {
          selectItem(currentItems[0]);
        }
      });
    }

    document.addEventListener("click", (e) => {
      if (!box.contains(e.target)) {
        wrap.classList.remove("active");
      }
    });

    clearBtn.addEventListener("click", (e) => {
      e.stopPropagation();

      input.value = "";
      clearBtn.classList.add("hidden");

      wrap.classList.remove("active");
      list.innerHTML = "";

      input.focus();
    });
  });

  /* =====================================================
  BANNER SLIDE
  ===================================================== */

  const sliderEl = document.querySelector(".home6-banner-slider");

  if (!sliderEl) return;

  new Swiper(sliderEl, {
    slidesPerView: "auto",
    speed: 1500,
    spaceBetween: 24,

    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },

    effect: "fade",
    fadeEffect: {
      crossFade: true,
    },

    navigation: {
      nextEl: ".banner-slider-next",
      prevEl: ".banner-slider-prev",
    },

    pagination: {
      el: ".franctional-pagi1",
      type: "fraction",
    },
  });




  /* =====================================================
  TOUR SLIDE
  ===================================================== */

  if (document.querySelector(".home-trip-slider")) {
    new Swiper(".home-trip-slider", {
      slidesPerView: "auto",
      speed: 1500,
      spaceBetween: 24,

      autoplay: {
        delay: 2500,
        pauseOnMouseEnter: true,
        disableOnInteraction: false,
      },

      pagination: {
        el: ".swiper-pagination2",
        clickable: true,
      },

      breakpoints: {
        280: { slidesPerView: 1 },
        386: { slidesPerView: 1 },
        576: { slidesPerView: 1 },
        768: { slidesPerView: 2, spaceBetween: 15 },
        992: { slidesPerView: 3 },
        1200: { slidesPerView: 3 },
        1400: { slidesPerView: 3 },
      },
    });
  }

  /* =====================================================
  FEATURED DESTINATION – CONTINENT TABS
  ===================================================== */

  const tabList = document.getElementById("continent-tabs");
  const tabContent = document.getElementById("continent-contents");

  if (tabList && tabContent && !tabList.dataset.serverRendered) {
    fetch(`${window.BASE_URL}assets/js/data/destinations.json`)
      .then((res) => res.json())
      .then((data) => initContinents(data));
  }

  function initContinents(data) {
    const tabList = document.getElementById("continent-tabs");
    const tabContent = document.getElementById("continent-contents");

    let isFirst = true;

    Object.keys(data).forEach((key) => {
      const tabId = `tab-${key}`;

      /* ---------- TAB BUTTON ---------- */
      tabList.innerHTML += `
      <li class="nav-item">
        <button class="nav-link ${isFirst ? "active" : ""}"
          data-bs-toggle="pill"
          data-bs-target="#${tabId}"
          type="button">
          ${capitalize(key)}
        </button>
      </li>
    `;

      /* ---------- TAB CONTENT ---------- */
      tabContent.innerHTML += `
      <div class="tab-pane fade ${isFirst ? "show active" : ""}" id="${tabId}">
        <div class="row g-xl-4 g-lg-3 gy-4" id="content-${key}"></div>
      </div>
    `;

      renderCards(data[key], `content-${key}`);
      isFirst = false;
    });

    new WOW().init();
  }

  function renderCards(items, containerId) {
    const container = document.getElementById(containerId);
    let html = "";

    items.forEach((item) => {
      html += `
      
      <div class="${item.col}">
            <a href="${item.link}">

        <div class="destination-card2 four">
          <div class="destination-img">
              <img src="${item.image}" alt="${item.title}">
          </div>
          <div class="destination-content-wrap">
            <div class="destination-content">
              <h5 class="text-white">${item.title}</h5>
            </div>
          </div>
        </div>
            </a>

      </div>
     
    `;
    });

    container.innerHTML = html;
  }

  function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  const progressWrap = document.getElementById("progressWrap");
  const progressPath = document.getElementById("progressPath");

  if (progressPath) {
    const pathLength = progressPath.getTotalLength();

    progressPath.style.strokeDasharray = pathLength + " " + pathLength;
    progressPath.style.strokeDashoffset = pathLength;

    const updateProgress = () => {
      const scrollY = window.scrollY;
      const height =
        document.documentElement.scrollHeight - window.innerHeight;

      const progress = pathLength - (scrollY * pathLength) / height;

      progressPath.style.strokeDashoffset = progress;

      if (scrollY > 50) {
        progressWrap.classList.add("active-progress");
      } else {
        progressWrap.classList.remove("active-progress");
      }
    };

    updateProgress();
    window.addEventListener("scroll", updateProgress);
  }

  // Scroll to top
  if (progressWrap) {
    progressWrap.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }

  /* =========================
     COUNTER ANIMATION
  ========================== */

  function initCounter(selector = ".counter", duration = 2000, threshold = 0.8) {
    const counters = document.querySelectorAll(selector);
    if (!counters.length) return;

    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && entry.intersectionRatio >= threshold) {
            const el = entry.target;
            const target = parseInt(
              el.innerText.replace(/,/g, ""),
              10
            );

            animateCounter(el, target, duration);
            obs.unobserve(el);
          }
        });
      },
      {
        threshold: Array.from({ length: 21 }, (_, i) => i / 20),
        rootMargin: "0px 0px -10% 0px",
      }
    );

    counters.forEach((el) => observer.observe(el));
  }

  function animateCounter(element, target, duration) {
    const start = performance.now();

    function update(now) {
      const progress = Math.min((now - start) / duration, 1);

      const value = Math.floor(
        target * (1 - Math.pow(1 - progress, 3))
      );

      element.innerText = value.toLocaleString();

      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        element.innerText = target.toLocaleString();
      }
    }

    requestAnimationFrame(update);
  }

  initCounter();

  const testimonialSwiper = new Swiper(".home1-testimonial-slider", {
    slidesPerView: "auto",
    speed: 1500,
    spaceBetween: 24,

    autoplay: {
      delay: 2500,
      pauseOnMouseEnter: true,
      disableOnInteraction: false,
    },

    navigation: {
      nextEl: ".testimonial-slider-next",
      prevEl: ".testimonial-slider-prev",
    },

    breakpoints: {
      280: { slidesPerView: 1 },
      386: { slidesPerView: 1 },
      576: { slidesPerView: 1 },
      768: { slidesPerView: 2, spaceBetween: 15 },
      992: { slidesPerView: 3, spaceBetween: 15 },
      1200: { slidesPerView: 3, spaceBetween: 15 },
      1400: { slidesPerView: 3 },
    },
  });

    flatpickr("#departure_date", {
    dateFormat: "d/m/Y",
    minDate: "today",
    enableTime: false,
    altInput: true,
    altFormat: "D j F, Y",
    locale: "vn",
    disableMobile: true,
  });

});
