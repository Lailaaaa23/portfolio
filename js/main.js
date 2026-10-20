/* =========================================================
   PORTFOLIO – JavaScript
   1. Mobile Navigation (Hamburger)
   2. Projekt-Karussell mit Pfeil-Buttons
   ========================================================= */


/* ---------- 1. MOBILE NAVIGATION ---------- */
const navToggle = document.querySelector(".nav-toggle");
const mainNav = document.querySelector(".main-nav");

// Beim Klick auf den Hamburger: Menü auf- oder zuklappen
navToggle.addEventListener("click", () => {
  const isOpen = mainNav.classList.toggle("open");
  navToggle.setAttribute("aria-expanded", isOpen);
});

// Nach Klick auf einen Menüpunkt das Menü wieder schliessen
mainNav.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => {
    mainNav.classList.remove("open");
    navToggle.setAttribute("aria-expanded", false);
  });
});


/* ---------- 2. PROJEKT-KARUSSELL ---------- */
const track = document.querySelector(".work-track");
const arrowButtons = document.querySelectorAll(".work-controls .round-btn");

arrowButtons.forEach((button) => {
  button.addEventListener("click", () => {
    // data-dir ist -1 (zurück) oder 1 (weiter), siehe index.html
    const direction = Number(button.dataset.dir);

    // Eine Karte + Abstand weiter scrollen
    const card = track.querySelector(".card");
    const gap = parseFloat(getComputedStyle(track).gap);
    const distance = card.offsetWidth + gap;

    track.scrollBy({ left: direction * distance, behavior: "smooth" });
  });
});
