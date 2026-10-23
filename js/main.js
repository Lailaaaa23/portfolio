/* =========================================================
   PORTFOLIO – JavaScript für ALLE Seiten
   1. Mobile Navigation (Hamburger)
   2. Titel mit Umbruchstellen einsetzen

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


/* ---------- 2. TITEL MIT UMBRUCHSTELLEN EINSETZEN ----------
   Ein Titel wie "Medienmacher:innen" ist für den Browser EIN Wort
   und wird deshalb nie umgebrochen, auch wenn er zu lang ist.
   Diese Funktion setzt den Titel ein und erlaubt nach jedem
   Doppelpunkt einen Zeilenumbruch: Das Element <wbr> ("word break")
   ist unsichtbar und sagt nur "hier darfst du umbrechen, wenn nötig".

   Verwendung: setzeTitel(element, "Medienmacher:innen")
   Wird von startseite.js und projekt.js genutzt. */
function setzeTitel(element, text) {
  element.textContent = "";

  // "Medienmacher:innen" -> ["Medienmacher", "innen"]
  const teile = text.split(":");

  teile.forEach((teil, index) => {
    // Text immer als reinen Text einfügen (append mit einem String),
    // nie als HTML: so bleibt der Schutz vor eingeschleustem Code bestehen
    element.append(teil);

    // Nach jedem Teil ausser dem letzten: Doppelpunkt + Umbruchstelle
    if (index < teile.length - 1) {
      element.append(":", document.createElement("wbr"));
    }
  });
}
