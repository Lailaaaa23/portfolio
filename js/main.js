/* =========================================================
   PORTFOLIO – JavaScript für ALLE Seiten
   1. Mobile Navigation (Hamburger)

   Was nur auf einer bestimmten Seite gebraucht wird, steht in
   einer eigenen Datei: startseite.js und projekt.js
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
