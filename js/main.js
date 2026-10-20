/* =========================================================
   PORTFOLIO – JavaScript
   1. Mobile Navigation (Hamburger)
   2. Projekt-Karussell mit Pfeil-Buttons
   3. Animation des Namens im Hero
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


/* ---------- 3. ANIMATION DES NAMENS IM HERO ---------- */
const heroName = document.querySelector(".hero-name");

// Der ganze Name bleibt für Screenreader lesbar,
// die einzelnen Buchstaben-Spans werden für sie ausgeblendet.
heroName.setAttribute("aria-label", heroName.textContent);

// "Laila Keller" -> ["Laila", "Keller"]
const words = heroName.textContent.split(" ");
let letterNumber = 0;

// Jedes Wort und jeden Buchstaben in ein eigenes <span> packen
const wordSpans = words.map((word) => {
  const wordSpan = document.createElement("span");
  wordSpan.className = "word";
  wordSpan.setAttribute("aria-hidden", "true");

  for (const char of word) {
    const letterSpan = document.createElement("span");
    letterSpan.className = "letter";
    letterSpan.textContent = char;
    // Die Nummer bestimmt in der CSS, wann der Buchstabe startet
    letterSpan.style.setProperty("--i", letterNumber);
    letterNumber++;
    wordSpan.append(letterSpan);
  }

  return wordSpan;
});

// Alten Text ersetzen: Wörter wieder mit Leerzeichen dazwischen einsetzen
heroName.textContent = "";
wordSpans.forEach((wordSpan, index) => {
  if (index > 0) heroName.append(" ");
  heroName.append(wordSpan);
});
