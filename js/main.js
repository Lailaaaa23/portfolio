/* =========================================================
   PORTFOLIO – JavaScript für ALLE Seiten
   1. Mobile Navigation (Hamburger)
   2. Titel mit Umbruchstellen einsetzen
   3. Status im Header aus dem CMS laden
   4. Aktuelles Jahr in der Fusszeile

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


/* ---------- 3. STATUS IM HEADER AUS DEM CMS LADEN ----------
   Der Text neben dem pulsierenden Punkt, z. B. "Available for projects".
   Er steht auf jeder Seite, darum wird er hier in main.js geladen.
   Ist der Status im CMS leer, wird er auf der Webseite ausgeblendet. */
async function ladeStatus() {
  const status = document.querySelector(".status");
  if (!status) return;

  try {
    const antwort = await fetch("api/einstellungen.php");
    if (!antwort.ok) return;   // bei einem Fehler einfach den Standardtext stehen lassen
    const einstellungen = await antwort.json();

    const text = (einstellungen.status || "").trim();
    if (text) {
      status.querySelector(".status-text").textContent = text;
    } else {
      status.hidden = true;
    }
  } catch (fehler) {
    // Server nicht erreichbar: Standardtext bleibt stehen, das ist kein Problem
  }
}

ladeStatus();


/* ---------- 4. AKTUELLES JAHR IN DER FUSSZEILE ----------
   Im HTML steht eine feste Jahreszahl (falls JavaScript nicht läuft).
   Hier wird sie durch das aktuelle Jahr ersetzt, damit "© 2026" nicht
   im nächsten Jahr veraltet ist. */
document.querySelectorAll(".aktuelles-jahr").forEach((element) => {
  element.textContent = new Date().getFullYear();
});
