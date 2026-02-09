document.addEventListener("DOMContentLoaded", () => {
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
      e.stopPropagation();

      const wrapper = btn.closest(
        ".language-area, .contact-area, .search-bar, .nav-right",
      );
      if (!wrapper) return;

      const target = wrapper.querySelector(btn.dataset.target);
      if (!target) return;

      const isOpen = target.classList.contains("active");

      // ❗ chỉ đóng dropdown header
      closeActive(
        ".language-list.active, .contact-list.active, .search-box.active",
      );

      if (!isOpen) target.classList.add("active");
    });
  });

  /* =====================================================
     MENU CLOSE
  ===================================================== */
  const menuCloseBtn = qs(".menu-close-btn");
  const mainMenu = qs(".main-menu");

  if (menuCloseBtn && mainMenu) {
    menuCloseBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      mainMenu.classList.remove("show-menu");
    });
  }

  /* =====================================================
     LANGUAGE DROPDOWN (RIÊNG)
  ===================================================== */
  const languageBtn = qs(".language-btn");
  const languageList = qs(".language-list");

  if (languageBtn && languageList) {
    languageBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      languageList.classList.toggle("active");
    });

    languageList.addEventListener("click", (e) => e.stopPropagation());
  }

  /* =====================================================
     CUSTOM SELECT (Category / Country / Activity)
  ===================================================== */
  qsa(".category-box").forEach((box) => {
    const dropdown = qs(".custom-select-dropdown", box);
    const wrap = qs(".custom-select-wrap", box);
    if (!dropdown || !wrap) return;

    const input = qs("input", dropdown);
    const items = qsa(".option-list .single-item", wrap);

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
      ".language-list.active, .contact-list.active, .search-box.active, .custom-select-wrap.active",
    );
  });

  /* =====================================================
     SEARCH DESTINATION
  ===================================================== */

  document.querySelectorAll(".destination-box").forEach((box) => {
    const input = box.querySelector(".destination-input");
    const wrap = box.querySelector(".custom-select-wrap");
    const list = box.querySelector(".option-list-destination");
    const clearBtn = box.querySelector(".clear-destination");

    input.addEventListener("input", () => {
      const keyword = input.value.trim();

      clearBtn.classList.toggle("hidden", keyword.length === 0);
    });

    if (!input || !wrap || !list) return;

    let timer = null;

    input.addEventListener("input", () => {
      const keyword = input.value.trim();

      clearTimeout(timer);

      if (keyword.length < 2) {
        wrap.classList.remove("active");
        list.innerHTML = "";
        return;
      }

      wrap.classList.add("active");
      list.innerHTML = `<li class="single-item"><h6>Loading...</h6></li>`;

      timer = setTimeout(() => {
        fetch(`/api/destinations?q=${encodeURIComponent(keyword)}`)
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

      if (data.length === 0) {
        list.innerHTML = `
          <li class="single-item">
            <h6>No results found</h6>
          </li>`;
        return;
      }

      data.forEach((item) => {
        const li = document.createElement("li");
        li.className = "single-item";

        li.innerHTML = `
          <h6>
            ${
              item.type === "country"
                ? item.name
                : `${item.name}, ${item.country}`
            }
          </h6>
        `;

        li.addEventListener("click", () => {
          input.value =
            item.type === "country"
              ? item.name
              : `${item.name}, ${item.country}`;

          wrap.classList.remove("active");
          list.innerHTML = "";
        });

        list.appendChild(li);
      });
    }

    // click ra ngoài → đóng dropdown
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

  fetch(`${window.BASE_URL}assets/js/data/destinations.json`)
    .then((res) => res.json())
    .then((data) => initContinents(data));

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
});
