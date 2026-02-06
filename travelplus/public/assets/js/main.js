document.addEventListener('DOMContentLoaded', () => {
   const toggles = document.querySelectorAll('[data-toggle="dropdown"]');

    const closeAll = () => {
        document.querySelectorAll('.active, .show-menu').forEach(el => {
            el.classList.remove('active', 'show-menu');
        });
    };

    toggles.forEach(btn => {
        const activeClass = btn.dataset.active || 'active';

        btn.addEventListener('click', (e) => {
            e.stopPropagation();

            const wrapper = btn.closest(
                '.language-area, .contact-area, .search-bar, .nav-right'
            );


            if (!wrapper) return;

            const target = wrapper.querySelector(btn.dataset.target);
            if (!target) return;

            const isOpen = target.classList.contains(activeClass);
            closeAll();

            if (!isOpen) {
                target.classList.add(activeClass);
            }
        });
    });

    document.addEventListener('click', closeAll);

    const menuCloseBtn = document.querySelector('.menu-close-btn');
    const mainMenu = document.querySelector('.main-menu');

    if (!menuCloseBtn || !mainMenu) return;

    menuCloseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        mainMenu.classList.remove('show-menu');
    });

    const languageArea = document.querySelector('.language-area');
    const languageBtn  = document.querySelector('.language-btn');
    const languageList = document.querySelector('.language-list');

    if (!languageArea || !languageBtn || !languageList) return;

    // Click vào nút → toggle
    languageBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // không cho nổi ra document
        languageList.classList.toggle('active');
    });

    // Click ra ngoài → đóng
    document.addEventListener('click', () => {
        languageList.classList.remove('active');
    });

    // Click trong list → không bị đóng
    languageList.addEventListener('click', (e) => {
        e.stopPropagation();
    });
});

document.addEventListener('DOMContentLoaded', () => {
    flatpickr('#departure_date', {
        dateFormat: 'd/m/Y',
        minDate: 'today',
        enableTime: false,
        altInput: true,
        altFormat: 'D j F, Y', 
        locale: 'vn',
        disableMobile: true,
    });
});

document.querySelectorAll(".custom-select-wrap").forEach(wrap => {
  wrap.addEventListener("click", e => {
    e.stopPropagation();
  });
});


document.addEventListener("DOMContentLoaded", () => {

  /* ===============================
     CUSTOM SELECT DROPDOWN (Category, Country, Activity...)
  =============================== */
  document.querySelectorAll(".category-box").forEach(box => {

    const dropdown = box.querySelector(".custom-select-dropdown");
    const wrap = box.querySelector(".custom-select-wrap");
    if (!dropdown || !wrap) return; // ⛔ box này không phải dropdown

    const input = dropdown.querySelector("input");
    const items = wrap.querySelectorAll(".option-list .single-item");

    

    // toggle dropdown
    dropdown.addEventListener("click", e => {
      e.stopPropagation();
      closeAllDropdowns();
      wrap.classList.toggle("active");
    });

    // chọn item
    items.forEach(item => {
      item.addEventListener("click", () => {
        const text =
          item.querySelector("h6")?.innerText ||
          item.innerText;

        if (input) input.value = text.trim();
        wrap.classList.remove("active");
      });
    });
  });

  // click ra ngoài → đóng hết
  document.addEventListener("click", () => {
    closeAllDropdowns();
  });

  function closeAllDropdowns() {
    document
      .querySelectorAll(".custom-select-wrap.active")
      .forEach(el => el.classList.remove("active"));
  }

  /* ===============================
     FILTER TAB (Tours / Hotels / Visa / Experience)
  =============================== */
  const filterTabs = document.querySelectorAll(".filter-item-list .single-item");
  const filterForms = document.querySelectorAll(".filter-input");

  filterTabs.forEach((tab, index) => {
    tab.addEventListener("click", () => {
      filterTabs.forEach(t => t.classList.remove("active"));
      filterForms.forEach(f => f.classList.remove("show"));

      tab.classList.add("active");
      if (filterForms[index]) {
        filterForms[index].classList.add("show");
      }
    });
  });

});

document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll(".destination-box").forEach(box => {

    const dropdown = box.querySelector(".destination-dropdown");
    const wrap = box.querySelector(".custom-select-wrap");
    const items = box.querySelectorAll(".destination-item");

    const input = box.querySelector(".main-destination-input");
    const nameEl = box.querySelector(".dest-name");
    const countryEl = box.querySelector(".dest-country");
    const placeholder = box.querySelector(".placeholder-text");
    const selectedBox = box.querySelector(".destination.selected");

    // mở / đóng dropdown
    dropdown.addEventListener("click", e => {
      e.stopPropagation();
      closeAll();
      wrap.classList.toggle("active");
    });

    // click chọn nơi đến
    items.forEach(item => {
      item.addEventListener("click", e => {
        e.stopPropagation();

        const name = item.dataset.name;
        const country = item.dataset.country;

        nameEl.innerText = name;
        countryEl.innerText = country;
        input.value = country; // hoặc `${name}, ${country}`

        placeholder.classList.add("hidden");
        selectedBox.classList.remove("hidden");
        wrap.classList.remove("active");
      });
    });

    // không cho click trong dropdown bị đóng
    wrap.addEventListener("click", e => e.stopPropagation());
  });

  // click ngoài → đóng hết
  document.addEventListener("click", closeAll);

  function closeAll() {
    document
      .querySelectorAll(".custom-select-wrap.active")
      .forEach(el => el.classList.remove("active"));
  }
});


document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll(".destination-box").forEach(box => {

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
          .then(res => res.json())
          .then(data => renderList(data))
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

      data.forEach(item => {
        const li = document.createElement("li");
        li.className = "single-item";

        li.innerHTML = `
          <h6>
            ${item.type === 'country'
              ? item.name
              : `${item.name}, ${item.country}`}
          </h6>
        `;

        li.addEventListener("click", () => {
          input.value = item.type === 'country'
            ? item.name
            : `${item.name}, ${item.country}`;

          wrap.classList.remove("active");
          list.innerHTML = "";
        });

        list.appendChild(li);
      });
    }

    // click ra ngoài → đóng dropdown
    document.addEventListener("click", e => {
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

});

document.addEventListener("DOMContentLoaded", function () {

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

});

document.addEventListener("DOMContentLoaded", function () {

  // ===== SWIPER CHA: Popular Package =====
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
});

