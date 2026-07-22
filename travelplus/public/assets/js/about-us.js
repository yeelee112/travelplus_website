document.addEventListener("DOMContentLoaded", function () {

    
  new Swiper(".about-page-journey-slider", {
    slidesPerView: "auto",
    speed: 450,
    spaceBetween: 24,
    grabCursor: true,
    breakpoints: {
      280: { slidesPerView: 1.45, spaceBetween: 8 },
      350: { slidesPerView: 2.25, spaceBetween: 8 },
      576: { slidesPerView: 3 },
      768: { slidesPerView: 4, spaceBetween: 10 },
      992: { slidesPerView: 5, spaceBetween: 15 },
      1200: { slidesPerView: 6, spaceBetween: 10 },
      1400: { slidesPerView: 6 },
    }
  });


  const navLinks = document.querySelectorAll(".about-page-journey-slider .nav-link");
  const panes = document.querySelectorAll(".journey-pane");

  navLinks.forEach(link => {
    link.addEventListener("click", function () {

      // remove active nav
      navLinks.forEach(l => l.classList.remove("active"));
      this.classList.add("active");

      const target = this.dataset.target;

      // hide all content
      panes.forEach(p => p.classList.remove("active"));

      // show selected
      document.querySelector(`[data-content="${target}"]`)
        ?.classList.add("active");
    });
  });

});
