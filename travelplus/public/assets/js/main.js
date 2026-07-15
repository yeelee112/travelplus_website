document.addEventListener("DOMContentLoaded", () => {
  /* =====================================================
     HELPER
  ===================================================== */
  const qs = (s, p = document) => p.querySelector(s);
  const qsa = (s, p = document) => [...p.querySelectorAll(s)];
  const appConfig = document.body?.dataset || {};
  const BASE_URL = appConfig.baseUrl || window.BASE_URL || "/";
  const URL_API = appConfig.localizedUrl || window.URL_API || BASE_URL;
  const CSRF_TOKEN_NAME = appConfig.csrfTokenName || window.CSRF_TOKEN_NAME || "";
  const CSRF_TOKEN = appConfig.csrfToken || window.CSRF_TOKEN || "";

  const closeActive = (selector) => {
    qsa(selector).forEach((el) => el.classList.remove("active"));
  };

  /* =====================================================
     SIMPLE IMAGE LIGHTBOX
  ===================================================== */
  const galleryLinks = qsa('[data-fancybox="gallery-01"]');

  if (galleryLinks.length > 0) {
    const lightbox = document.createElement("div");
    lightbox.className = "tp-image-lightbox";
    lightbox.hidden = true;
    lightbox.innerHTML = `
      <div class="tp-image-lightbox__backdrop" data-lightbox-close></div>
      <div class="tp-image-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Image preview">
        <button type="button" class="tp-image-lightbox__close" data-lightbox-close aria-label="Close image preview">
          <i class="bi bi-x-lg"></i>
        </button>
        <img class="tp-image-lightbox__image" alt="">
      </div>
    `;

    document.body.appendChild(lightbox);

    const lightboxImage = qs(".tp-image-lightbox__image", lightbox);
    const closeLightbox = () => {
      lightbox.hidden = true;
      document.body.classList.remove("tp-lightbox-open");
      if (lightboxImage) {
        lightboxImage.removeAttribute("src");
        lightboxImage.alt = "";
      }
    };
    const openLightbox = (link) => {
      if (!lightboxImage) {
        return;
      }

      const image = qs("img", link);
      lightboxImage.src = link.href;
      lightboxImage.alt = image?.alt || link.getAttribute("aria-label") || "";
      lightbox.hidden = false;
      document.body.classList.add("tp-lightbox-open");
      qs(".tp-image-lightbox__close", lightbox)?.focus();
    };

    galleryLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();
        openLightbox(link);
      });
    });

    qsa("[data-lightbox-close]", lightbox).forEach((button) => {
      button.addEventListener("click", closeLightbox);
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !lightbox.hidden) {
        closeLightbox();
      }
    });
  }

  /* =====================================================
     PROMOTION COUNTDOWN
  ===================================================== */
  qsa("[data-countdown]").forEach((countdown) => {
    const endValue = countdown.dataset.countdownEnd || "";
    const endDate = new Date(endValue);
    const expiredLabel = countdown.dataset.expiredLabel || "Expired";
    const daysEl = qs("[data-countdown-days]", countdown);
    const hoursEl = qs("[data-countdown-hours]", countdown);
    const minutesEl = qs("[data-countdown-minutes]", countdown);
    const secondsEl = qs("[data-countdown-seconds]", countdown);
    const labelEl = qs(".home-promo-countdown__label", countdown);

    if (
      Number.isNaN(endDate.getTime()) ||
      !daysEl ||
      !hoursEl ||
      !minutesEl ||
      !secondsEl
    ) {
      return;
    }

    const pad = (value) => String(Math.max(0, value)).padStart(2, "0");
    const render = () => {
      const remaining = endDate.getTime() - Date.now();

      if (remaining <= 0) {
        countdown.classList.add("is-expired");
        if (labelEl) {
          labelEl.textContent = expiredLabel;
        }
        daysEl.textContent = "00";
        hoursEl.textContent = "00";
        minutesEl.textContent = "00";
        secondsEl.textContent = "00";
        return false;
      }

      const totalSeconds = Math.floor(remaining / 1000);
      const days = Math.floor(totalSeconds / 86400);
      const hours = Math.floor((totalSeconds % 86400) / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;

      daysEl.textContent = pad(days);
      hoursEl.textContent = pad(hours);
      minutesEl.textContent = pad(minutes);
      secondsEl.textContent = pad(seconds);
      return true;
    };

    if (render()) {
      const timer = window.setInterval(() => {
        if (!render()) {
          window.clearInterval(timer);
        }
      }, 1000);
    }
  });

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

  qsa("[data-tour-filter-toggle]").forEach((toggle) => {
    const panelId = toggle.getAttribute("aria-controls");
    const panel = panelId ? document.getElementById(panelId) : null;

    if (!panel) {
      return;
    }

    toggle.addEventListener("click", () => {
      const isOpen = panel.classList.toggle("is-open");
      toggle.classList.toggle("is-active", isOpen);
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
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
     DESKTOP MENU CLICK OPEN
  ===================================================== */
  const desktopHeaderItems = qsa(
    "header.site-header-modern .main-menu > .menu-list > li.menu-item-has-children",
  );
  const desktopMenuBreakpoint = 1200;

  const closeDesktopMenus = () => {
    desktopHeaderItems.forEach((item) => {
      item.classList.remove("menu-open");
    });
  };

  desktopHeaderItems.forEach((item) => {
    const trigger = qs(":scope > a.drop-down", item);
    const panel = qs(":scope > .sub-menu, :scope > .mega-menu", item);

    if (!trigger || !panel) return;

    trigger.addEventListener("click", (event) => {
      if (window.innerWidth < desktopMenuBreakpoint) return;

      event.preventDefault();
      event.stopPropagation();

      const isOpen = item.classList.contains("menu-open");
      closeDesktopMenus();

      if (!isOpen) {
        item.classList.add("menu-open");
      }
    });

    panel.addEventListener("click", (event) => {
      if (window.innerWidth < desktopMenuBreakpoint) return;
      event.stopPropagation();
    });
  });

  document.addEventListener("click", () => {
    if (window.innerWidth >= desktopMenuBreakpoint) {
      closeDesktopMenus();
    }
  });

  /* =====================================================
     MOBILE SUB MENU
  ===================================================== */
  qsa("header.site-header-modern .main-menu > .menu-list > .menu-item-has-children").forEach((item) => {
    const dropdownIcon = qs(":scope > .dropdown-icon", item);
    const submenu = qs(":scope > .sub-menu, :scope > .mega-menu", item);

    if (!dropdownIcon || !submenu) return;

    dropdownIcon.addEventListener("click", (e) => {
      if (window.innerWidth >= desktopMenuBreakpoint) return;

      e.preventDefault();
      e.stopPropagation();

      item.classList.toggle("active");
      dropdownIcon.classList.toggle("active");
    });
  });

  qsa("header.site-header-modern .mega-menu .menu-single-item").forEach((item) => {
    const dropdownIcon = qs(":scope > .dropdown-icon", item);
    const submenu = qs(":scope > ul", item);

    if (!dropdownIcon || !submenu) return;

    dropdownIcon.addEventListener("click", (e) => {
      if (window.innerWidth >= desktopMenuBreakpoint) return;

      e.preventDefault();
      e.stopPropagation();

      item.classList.toggle("active");
      dropdownIcon.classList.toggle("active");
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

    const getQuantityName = (field) => field.name.replace("_quantity", "");

    const getQuantityInput = (quantityName) =>
      countInputs.find((field) => getQuantityName(field) === quantityName) || null;

    const getQuantityValue = (quantityName) => {
      const field = getQuantityInput(quantityName);
      const value = Number.parseInt(field?.value || "0", 10);
      return Number.isNaN(value) ? 0 : value;
    };

    const getTotalTravelers = () =>
      countInputs.reduce((sum, field) => {
        const value = Number.parseInt(field.value, 10);
        return sum + (Number.isNaN(value) ? 0 : value);
      }, 0);

    const clampTravelerMix = () => {
      const adultInput = getQuantityInput("adult");
      const childInput = getQuantityInput("child");
      const infantInput = getQuantityInput("infant");

      if (!adultInput || !childInput || !infantInput) return;

      const adultValue = Math.max(1, Number.parseInt(adultInput.value || "1", 10) || 1);
      let childValue = Math.max(0, Number.parseInt(childInput.value || "0", 10) || 0);
      let infantValue = Math.max(0, Number.parseInt(infantInput.value || "0", 10) || 0);

      if (childValue + infantValue > adultValue) {
        infantValue = Math.min(infantValue, adultValue);
        childValue = Math.max(0, adultValue - infantValue);
      }

      adultInput.value = String(adultValue);
      childInput.value = String(childValue);
      infantInput.value = String(infantValue);
    };

    const updateTravelerSummary = () => {
      if (countInputs.length === 0) return;

      countInputs.forEach((field) => {
        const quantityName = getQuantityName(field);
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
        const quantityName = getQuantityName(quantityInput);
        let nextValue = button.classList.contains("guest-quantity__minus")
          ? Math.max(minValue, currentValue - 1)
          : currentValue + 1;

        if (button.classList.contains("guest-quantity__plus")) {
          const totalWithoutCurrent = getTotalTravelers() - currentValue;
          nextValue = Math.min(nextValue, maxTravelers - totalWithoutCurrent);

          if (quantityName === "child") {
            nextValue = Math.min(nextValue, Math.max(0, getQuantityValue("adult") - getQuantityValue("infant")));
          }

          if (quantityName === "infant") {
            nextValue = Math.min(nextValue, Math.max(0, getQuantityValue("adult") - getQuantityValue("child")));
          }

          nextValue = Math.max(minValue, nextValue);
        }

        quantityInput.value = String(nextValue);
        clampTravelerMix();
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
        const quantityName = getQuantityName(field);
        let maxAllowedForField = Math.max(minValue, maxTravelers - totalWithoutCurrent);

        if (quantityName === "child") {
          maxAllowedForField = Math.min(maxAllowedForField, Math.max(0, getQuantityValue("adult") - getQuantityValue("infant")));
        }

        if (quantityName === "infant") {
          maxAllowedForField = Math.min(maxAllowedForField, Math.max(0, getQuantityValue("adult") - getQuantityValue("child")));
        }

        if (currentValue > maxAllowedForField) {
          currentValue = maxAllowedForField;
        }

        field.value = String(currentValue);
        clampTravelerMix();
        updateTravelerSummary();
      });
    });

    clampTravelerMix();
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

    if (!input || !wrap || !list || !clearBtn) return;

    const setHomeSearchLayerState = () => {
      document.body.classList.toggle(
        "home-search-layer-open",
        document.querySelector(".home-modern-search .custom-select-wrap.active") !== null ||
          document.querySelector(".home-modern-search .home-search-date__panel:not([hidden])") !== null
      );
    };

    const openDestinationWrap = () => {
      document.querySelectorAll(".home-modern-search .home-search-date__panel").forEach((panel) => {
        panel.hidden = true;
      });
      document.querySelectorAll(".home-modern-search [data-home-date-trigger]").forEach((trigger) => {
        trigger.setAttribute("aria-expanded", "false");
      });
      wrap.classList.add("active");
      setHomeSearchLayerState();
    };

    const closeDestinationWrap = () => {
      wrap.classList.remove("active");
      setHomeSearchLayerState();
    };

    box.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    let timer = null;
    let currentItems = [];
    const locale = document.documentElement.lang === "en" ? "en" : "vi";
    const suggestionCopy = {
      popular: locale === "en" ? "Popular destination" : "Điểm đến phổ biến",
      country: locale === "en" ? "Country" : "Quốc gia",
      landmark: locale === "en" ? "Landmark" : "Điểm tham quan",
      noResults: locale === "en" ? "No results found" : "Không tìm thấy điểm đến",
      error: locale === "en" ? "Error loading data" : "Không tải được dữ liệu",
    };
    suggestionCopy.searchFor = locale === "en" ? "Search with this keyword" : "Tìm theo từ khóa đã nhập";
    const popularSuggestions = locale === "en"
      ? [
          { type: "popular", name: "Japan", subtitle: "Tokyo, Osaka, Kyoto" },
          { type: "popular", name: "South Korea", subtitle: "Seoul, Nami, Busan" },
          { type: "popular", name: "France", subtitle: "Paris and Europe routes" },
          { type: "popular", name: "Thailand", subtitle: "Bangkok, Pattaya, Phuket" },
          { type: "popular", name: "Da Nang", subtitle: "Central Vietnam" },
        ]
      : [
          { type: "popular", name: "Nhật Bản", subtitle: "Tokyo, Osaka, Kyoto" },
          { type: "popular", name: "Hàn Quốc", subtitle: "Seoul, Nami, Busan" },
          { type: "popular", name: "Pháp", subtitle: "Paris và tuyến châu Âu" },
          { type: "popular", name: "Thái Lan", subtitle: "Bangkok, Pattaya, Phuket" },
          { type: "popular", name: "Đà Nẵng", subtitle: "Miền Trung Việt Nam" },
        ];

    const truncateText = function (value, maxLength = 42) {
      const text = String(value || "").trim();

      if (text.length <= maxLength) {
        return text;
      }

      return `${text.slice(0, Math.max(0, maxLength - 3)).trimEnd()}...`;
    };

    const getItemLabel = function (item) {
      return truncateText(item.name || "", 54);
    };

    const getItemSubtitle = function (item) {
      if (item.subtitle) {
        return item.subtitle;
      }

      if (item.type === "country") {
        return suggestionCopy.country;
      }

      if (item.type === "gallery") {
        return suggestionCopy.landmark;
      }

      if (item.country && item.country !== "undefined") {
        return item.country;
      }

      if (item.type === "popular") {
        return suggestionCopy.popular;
      }

      return "";
    };

    const prependQueryOption = function (keyword, data) {
      const normalizedKeyword = String(keyword || "").trim().toLowerCase();
      const items = Array.isArray(data) ? data.slice() : [];

      if (normalizedKeyword === "") {
        return items;
      }

      const firstMatchIndex = items.findIndex((item) => String(item && item.name ? item.name : "").trim().toLowerCase() === normalizedKeyword);

      if (firstMatchIndex === 0) {
        return items;
      }

      if (firstMatchIndex > 0) {
        const [match] = items.splice(firstMatchIndex, 1);
        items.unshift(match);
        return items;
      }

      return items;
    };

    const selectItem = function (item) {
      input.value = item.name || "";
      currentItems = [];
      clearBtn.classList.toggle("hidden", input.value.trim().length === 0);
      closeDestinationWrap();
      list.innerHTML = "";
    };

    const renderList = function (data) {
      list.innerHTML = "";
      currentItems = Array.isArray(data) ? data : [];

      if (currentItems.length === 0) {
        const emptyItem = document.createElement("li");
        emptyItem.className = "single-item destination-empty-item";
        const title = document.createElement("h6");
        title.textContent = suggestionCopy.noResults;
        emptyItem.appendChild(title);
        list.appendChild(emptyItem);
        return;
      }

      currentItems.forEach((item) => {
        const li = document.createElement("li");
        const destination = document.createElement("div");
        const title = document.createElement("h6");
        const subtitle = document.createElement("span");

        li.className = "single-item";
        destination.className = "destination";
        title.textContent = getItemLabel(item);
        subtitle.textContent = getItemSubtitle(item);

        destination.appendChild(title);
        if (subtitle.textContent !== "") {
          destination.appendChild(subtitle);
        }

        li.appendChild(destination);
        li.addEventListener("click", (event) => {
          event.stopPropagation();
          selectItem(item);
        });

        list.appendChild(li);
      });
    };

    const showPopularSuggestions = function () {
      clearTimeout(timer);
      renderList(popularSuggestions);
      openDestinationWrap();
    };

    input.addEventListener("focus", () => {
      if (input.value.trim() === "") {
        showPopularSuggestions();
      }
    });

    input.addEventListener("click", () => {
      if (input.value.trim() === "") {
        showPopularSuggestions();
      }
    });

    input.addEventListener("input", () => {
      const keyword = input.value.trim();

      clearBtn.classList.toggle("hidden", keyword.length === 0);
      clearTimeout(timer);
      currentItems = [];

      if (keyword.length < 2) {
        if (keyword.length === 0) {
          showPopularSuggestions();
        } else {
          closeDestinationWrap();
          list.innerHTML = "";
        }
        return;
      }

      openDestinationWrap();
        list.innerHTML = `
          <li class="single-item destination-loading-item" aria-hidden="true">
            <span class="tp-skeleton tp-skeleton-line tp-skeleton-line--wide"></span>
            <span class="tp-skeleton tp-skeleton-line tp-skeleton-line--short"></span>
          </li>`;

      timer = setTimeout(() => {
        fetch(`${BASE_URL}api/destinations?q=${encodeURIComponent(keyword)}`)
          .then((res) => res.json())
          .then((data) => renderList(prependQueryOption(keyword, data)))
          .catch(() => {
            list.innerHTML = `
              <li class="single-item">
                <h6>${suggestionCopy.error}</h6>
              </li>`;
          });
      }, 300);
    });

    // click ra ngoài → đóng dropdown
    input.addEventListener("keydown", (event) => {
      if (event.key !== "Enter") {
        return;
      }

      if (currentItems.length === 0) {
        return;
      }

      event.preventDefault();
      closeDestinationWrap();
      list.innerHTML = "";
      currentItems = [];

      if (form) {
        form.requestSubmit();
      }
    });

    if (form && form.hasAttribute("data-tour-search-form")) {
      form.addEventListener("submit", (event) => {
        const departureFromInput = form.querySelector('[name="departure_from"]');
        const departureToInput = form.querySelector('[name="departure_to"]');
        const departureInput = form.querySelector('[name="departure_date"]');
        const keyword = input.value.trim();
        const departureValue = [
          departureFromInput ? departureFromInput.value.trim() : "",
          departureToInput ? departureToInput.value.trim() : "",
          departureInput ? departureInput.value.trim() : "",
        ].find((value) => value !== "") || "";

        if (keyword === "" && departureValue === "") {
          event.preventDefault();
          input.focus();
          showPopularSuggestions();
          return;
        }

        closeDestinationWrap();
      });
    }

    document.addEventListener("click", (e) => {
      if (!box.contains(e.target)) {
        closeDestinationWrap();
      }
    });

    clearBtn.addEventListener("click", (e) => {
      e.stopPropagation();

      input.value = "";
      clearBtn.classList.add("hidden");

      closeDestinationWrap();
      list.innerHTML = "";

      input.focus();
      showPopularSuggestions();
    });
  });

  /* =====================================================
  BANNER SLIDE
  ===================================================== */

  const heroRotator = document.querySelector("[data-hero-rotator]");

  if (heroRotator) {
    const heroSlides = [...heroRotator.querySelectorAll("img")];
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const configuredInterval = Number.parseInt(heroRotator.dataset.interval || "7000", 10);
    const rotationInterval = Number.isFinite(configuredInterval) && configuredInterval >= 5000
      ? configuredInterval
      : 7000;

    if (heroSlides.length > 1 && !reduceMotion) {
      let activeHeroIndex = 0;

      const loadHeroSlide = (slide) => new Promise((resolve) => {
        const pendingSource = slide.dataset.heroSrc || "";

        if (pendingSource === "") {
          resolve(slide.complete && slide.naturalWidth > 0);
          return;
        }

        const handleLoad = () => {
          delete slide.dataset.heroSrc;
          resolve(true);
        };
        const handleError = () => {
          slide.removeAttribute("src");
          resolve(false);
        };

        slide.addEventListener("load", handleLoad, { once: true });
        slide.addEventListener("error", handleError, { once: true });
        slide.src = pendingSource;
      });

      const preloadNextHero = () => {
        const nextIndex = (activeHeroIndex + 1) % heroSlides.length;
        loadHeroSlide(heroSlides[nextIndex]);
      };

      const rotateHero = async () => {
        const nextIndex = (activeHeroIndex + 1) % heroSlides.length;
        const nextSlide = heroSlides[nextIndex];

        if (await loadHeroSlide(nextSlide)) {
          heroSlides[activeHeroIndex].classList.remove("is-active");
          nextSlide.classList.add("is-active");
          activeHeroIndex = nextIndex;
        }

        window.setTimeout(preloadNextHero, Math.max(1000, rotationInterval - 2000));
        window.setTimeout(rotateHero, rotationInterval);
      };

      window.setTimeout(preloadNextHero, Math.max(1000, rotationInterval - 2000));
      window.setTimeout(rotateHero, rotationInterval);
    }
  }

  const sliderEl = document.querySelector(".home-page__hero-slider");

  if (sliderEl) {
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
  }




  /* =====================================================
  TOUR SLIDE
  ===================================================== */

  const featuredTourSlider = document.querySelector(".home-page__featured-tour-slider");

  if (featuredTourSlider) {
    new Swiper(featuredTourSlider, {
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
    fetch(`${BASE_URL}assets/js/data/destinations.json`)
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
        <div class="row g-xl-4 g-lg-3 gy-4 home-page__destination-grid" id="content-${key}"></div>
      </div>
    `;

      renderCards(data[key], `content-${key}`);
      isFirst = false;
    });

    if (typeof WOW !== "undefined") {
      new WOW().init();
    }
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
              <img src="${item.image}" alt="${item.title}" loading="lazy" decoding="async" width="640" height="420">
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

  const testimonialSlider = document.querySelector(".home-page__testimonial-slider");

  if (testimonialSlider) {
    new Swiper(testimonialSlider, {
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
  }

  qsa("[data-date-range-picker]").forEach((datePicker) => {
    const startInput = qs("[data-date-range-input-start]", datePicker);
    const endInput = qs("[data-date-range-input-end]", datePicker);
    const trigger = qs("[data-date-range-trigger]", datePicker);
    const display = qs("[data-date-range-display]", datePicker);
    const panel = qs("[data-date-range-panel]", datePicker);
    const monthEl = qs("[data-date-range-month]", datePicker);
    const weekdaysEl = qs("[data-date-range-weekdays]", datePicker);
    const daysEl = qs("[data-date-range-days]", datePicker);
    const prevBtn = qs("[data-date-range-prev]", datePicker);
    const nextBtn = qs("[data-date-range-next]", datePicker);
    const clearBtn = qs("[data-date-range-clear]", datePicker);
    const previewStart = qs("[data-date-range-preview-start]", datePicker);
    const previewEnd = qs("[data-date-range-preview-end]", datePicker);

    if (
      !startInput ||
      !endInput ||
      !trigger ||
      !display ||
      !panel ||
      !monthEl ||
      !weekdaysEl ||
      !daysEl ||
      !prevBtn ||
      !nextBtn ||
      !previewStart ||
      !previewEnd
    ) {
      return;
    }

    const locale = datePicker.dataset.locale === "en" ? "en" : "vi";
    const emptyDateLabel = trigger.dataset.emptyLabel || "";
    const startEmptyLabel = trigger.dataset.startEmptyLabel || emptyDateLabel;
    const endEmptyLabel = trigger.dataset.endEmptyLabel || emptyDateLabel;
    const monthFormatter = new Intl.DateTimeFormat(locale === "en" ? "en-US" : "vi-VN", {
      month: "long",
      year: "numeric",
    });
    const weekdayNames = {
      vi: ["T2", "T3", "T4", "T5", "T6", "T7", "CN"],
      en: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    };
    const padDatePart = (value) => String(value).padStart(2, "0");
    const today = new Date();
    const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const isHomePicker = datePicker.closest(".home-modern-search") !== null;

    const parseInputDate = (value) => {
      const rawValue = String(value || "").trim();
      const isoMatch = rawValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);

      if (isoMatch) {
        const isoDate = new Date(Number(isoMatch[1]), Number(isoMatch[2]) - 1, Number(isoMatch[3]));
        return Number.isNaN(isoDate.getTime()) ? null : isoDate;
      }

      const displayMatch = rawValue.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
      if (!displayMatch) {
        return null;
      }

      const displayDate = new Date(Number(displayMatch[3]), Number(displayMatch[2]) - 1, Number(displayMatch[1]));
      return Number.isNaN(displayDate.getTime()) ? null : displayDate;
    };

    const isSameDate = (first, second) => (
      first &&
      second &&
      first.getFullYear() === second.getFullYear() &&
      first.getMonth() === second.getMonth() &&
      first.getDate() === second.getDate()
    );

    const formatValue = (date) => (
      `${date.getFullYear()}-${padDatePart(date.getMonth() + 1)}-${padDatePart(date.getDate())}`
    );

    const formatDisplayDate = (date) => (
      `${padDatePart(date.getDate())}/${padDatePart(date.getMonth() + 1)}/${date.getFullYear()}`
    );

    let selectedStart = parseInputDate(startInput.value);
    let selectedEnd = parseInputDate(endInput.value);

    if (selectedStart && selectedEnd && selectedEnd < selectedStart) {
      [selectedStart, selectedEnd] = [selectedEnd, selectedStart];
    }

    let viewDate = selectedStart
      ? new Date(selectedStart.getFullYear(), selectedStart.getMonth(), 1)
      : new Date(todayDate.getFullYear(), todayDate.getMonth(), 1);

    const syncBodyOverlayState = () => {
      if (!isHomePicker) {
        return;
      }

      document.body.classList.toggle(
        "home-search-layer-open",
        document.querySelector(".home-modern-search .custom-select-wrap.active") !== null ||
          document.querySelector(".home-modern-search .home-search-date__panel:not([hidden])") !== null
      );
    };

    const closeDatePicker = () => {
      panel.hidden = true;
      trigger.setAttribute("aria-expanded", "false");
      syncBodyOverlayState();
    };

    const updateSummary = () => {
      startInput.value = selectedStart ? formatValue(selectedStart) : "";
      endInput.value = selectedEnd ? formatValue(selectedEnd) : "";
      previewStart.textContent = selectedStart ? formatDisplayDate(selectedStart) : startEmptyLabel;
      previewEnd.textContent = selectedEnd ? formatDisplayDate(selectedEnd) : endEmptyLabel;

      if (selectedStart && selectedEnd) {
        display.textContent = `${formatDisplayDate(selectedStart)} - ${formatDisplayDate(selectedEnd)}`;
        trigger.classList.add("is-selected");
      } else if (selectedStart) {
        display.textContent = `${formatDisplayDate(selectedStart)} - ...`;
        trigger.classList.add("is-selected");
      } else {
        display.textContent = emptyDateLabel;
        trigger.classList.remove("is-selected");
      }
    };

    const isDateInRange = (date) => (
      selectedStart &&
      selectedEnd &&
      date.getTime() > selectedStart.getTime() &&
      date.getTime() < selectedEnd.getTime()
    );

    const renderDatePicker = () => {
      const year = viewDate.getFullYear();
      const month = viewDate.getMonth();
      const firstDay = new Date(year, month, 1);
      const monthStartOffset = (firstDay.getDay() + 6) % 7;
      const lastDay = new Date(year, month + 1, 0).getDate();
      const currentMonthStart = new Date(year, month, 1);
      const todayMonthStart = new Date(todayDate.getFullYear(), todayDate.getMonth(), 1);

      monthEl.textContent = monthFormatter.format(new Date(year, month, 1));
      prevBtn.disabled = currentMonthStart <= todayMonthStart;
      daysEl.innerHTML = "";

      for (let index = 0; index < monthStartOffset; index += 1) {
        const blankDay = document.createElement("span");
        blankDay.className = "home-search-date__day home-search-date__day--blank";
        blankDay.setAttribute("aria-hidden", "true");
        daysEl.appendChild(blankDay);
      }

      for (let day = 1; day <= lastDay; day += 1) {
        const date = new Date(year, month, day);
        const dateButton = document.createElement("button");
        const isPastDate = date < todayDate;

        dateButton.type = "button";
        dateButton.className = "home-search-date__day";
        dateButton.textContent = String(day);
        dateButton.disabled = isPastDate;
        dateButton.setAttribute("aria-label", formatDisplayDate(date));

        if (isSameDate(date, selectedStart)) {
          dateButton.classList.add("is-range-start", "is-selected");
        }

        if (isSameDate(date, selectedEnd)) {
          dateButton.classList.add("is-range-end", "is-selected");
        }

        if (isDateInRange(date)) {
          dateButton.classList.add("is-in-range");
        }

        if (isSameDate(date, selectedStart) || isSameDate(date, selectedEnd)) {
          dateButton.setAttribute("aria-current", "date");
        }

        dateButton.addEventListener("click", () => {
          if (!selectedStart || (selectedStart && selectedEnd)) {
            selectedStart = date;
            selectedEnd = null;
          } else if (date.getTime() < selectedStart.getTime()) {
            selectedEnd = selectedStart;
            selectedStart = date;
          } else if (isSameDate(date, selectedStart)) {
            selectedStart = date;
            selectedEnd = null;
          } else {
            selectedEnd = date;
          }

          updateSummary();
          renderDatePicker();

          if (selectedStart && selectedEnd) {
            closeDatePicker();
          }
        });

        daysEl.appendChild(dateButton);
      }
    };

    weekdayNames[locale].forEach((weekday) => {
      const weekdayEl = document.createElement("span");
      weekdayEl.textContent = weekday;
      weekdaysEl.appendChild(weekdayEl);
    });

    updateSummary();

    trigger.addEventListener("click", (event) => {
      event.preventDefault();

      if (panel.hidden) {
        qsa("[data-date-range-panel]").forEach((otherPanel) => {
          if (otherPanel !== panel) {
            otherPanel.hidden = true;
          }
        });

        if (isHomePicker) {
          document.querySelectorAll(".home-modern-search .custom-select-wrap.active").forEach((wrap) => {
            wrap.classList.remove("active");
          });
        }

        renderDatePicker();
        panel.hidden = false;
        trigger.setAttribute("aria-expanded", "true");
        syncBodyOverlayState();
      } else {
        closeDatePicker();
      }
    });

    prevBtn.addEventListener("click", () => {
      viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
      renderDatePicker();
    });

    nextBtn.addEventListener("click", () => {
      viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
      renderDatePicker();
    });

    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        selectedStart = null;
        selectedEnd = null;
        updateSummary();
        renderDatePicker();
      });
    }

    document.addEventListener("click", (event) => {
      const eventPath = typeof event.composedPath === "function" ? event.composedPath() : [];

      if (!eventPath.includes(datePicker) && !datePicker.contains(event.target)) {
        closeDatePicker();
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        closeDatePicker();
      }
    });
  });

});
