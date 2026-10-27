/* =========================================================
   PORTFOLIO – JavaScript nur für die STARTSEITE (index.html)
   1. Projekt-Karussell mit Pfeil-Buttons
   2. Animation des Namens im Hero
   3. Aus dem Punkt wird der Kreis
   4. Lila Kreis folgt der Maus
   5. Projekte aus der Datenbank laden
   6. Skills aus der Datenbank laden
   ========================================================= */


/* ---------- 1. PROJEKT-KARUSSELL ---------- */
const track = document.querySelector(".work-track");
const arrowButtons = document.querySelectorAll(".work-controls .round-btn");

arrowButtons.forEach((button) => {
  button.addEventListener("click", () => {
    // data-dir ist -1 (zurück) oder 1 (weiter), siehe index.html
    const direction = Number(button.dataset.dir);

    // Eine Karte + Abstand weiter scrollen
    const card = track.querySelector(".card");
    // columnGap: Abstand zwischen den Karten (Spalten des Rasters)
    const gap = parseFloat(getComputedStyle(track).columnGap);
    const distance = card.offsetWidth + gap;

    track.scrollBy({ left: direction * distance, behavior: "smooth" });
  });
});


/* ---------- 2. ANIMATION DES NAMENS IM HERO ---------- */
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

// Lila Punkt hinter dem Namen anhängen (wird später zum grossen Kreis)
const dot = document.createElement("span");
dot.className = "dot";
dot.setAttribute("aria-hidden", "true");
heroName.append(dot);


/* ---------- 3. AUS DEM PUNKT WIRD DER KREIS ---------- */
const hero = document.querySelector(".hero");
const circle = document.querySelector(".hero-circle");
const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

if (!reduceMotion) {
  // Der Kreis ist am Anfang unsichtbar, nur der kleine Punkt ploppt auf
  circle.style.opacity = "0";

  // Sobald der Punkt fertig aufgeploppt ist (Ende seiner CSS-Animation) ...
  dot.addEventListener("animationend", () => {
    // ... messen wir, wo Punkt und Kreis gerade auf dem Bildschirm sind
    const dotBox = dot.getBoundingClientRect();
    const circleBox = circle.getBoundingClientRect();

    // Abstand von der Kreismitte zur Punktmitte
    const dx = dotBox.left + dotBox.width / 2 - (circleBox.left + circleBox.width / 2);
    const dy = dotBox.top + dotBox.height / 2 - (circleBox.top + circleBox.height / 2);

    // Wie viel kleiner der Punkt ist als der Kreis (z. B. 0.07 = 7 %)
    const startScale = dotBox.width / circleBox.width;

    // Punkt ausblenden und den Kreis genau an seine Stelle setzen.
    // Für das Auge sieht das so aus, als wäre es derselbe Punkt.
    dot.style.visibility = "hidden";
    circle.style.opacity = "1";

    // Web Animations API: wie @keyframes in CSS, aber mit Werten,
    // die JavaScript eben erst ausgerechnet hat
    circle.animate(
      [
        // 1. Start: klein, genau dort, wo der Punkt war
        { transform: `translate(${dx}px, ${dy}px) scale(${startScale})` },
        // 2. Löst sich: schwebt nach rechts oben weg und wird etwas grösser
        {
          transform: `translate(${dx + 60}px, ${dy - 90}px) scale(${startScale * 2})`,
          offset: 0.35,
        },
        // 3. Ziel: volle Grösse an seinem Platz
        { transform: "translate(0px, 0px) scale(1)" },
      ],
      { duration: 1600, easing: "cubic-bezier(0.65, 0, 0.35, 1)" }
    );
  });
} else {
  // Ohne Animation gibt es keinen Punkt, nur den fertigen Kreis
  dot.remove();
}


/* ---------- 4. LILA KREIS FOLGT DER MAUS ---------- */
// Nur auf Geräten mit Maus (nicht auf dem Handy)
const hasMouse = window.matchMedia("(hover: hover)").matches;

if (hasMouse && !reduceMotion) {
  hero.addEventListener("mousemove", (event) => {
    const box = hero.getBoundingClientRect();

    // Wo ist die Maus im Hero? (in Pixeln, 0/0 = obere linke Ecke des Hero)
    const mouseX = event.clientX - box.left;
    const mouseY = event.clientY - box.top;

    // Wo ist die Mitte des Kreises an seinem normalen Platz?
    // offsetLeft/offsetTop ignorieren die Verschiebung, liefern also immer den Ausgangspunkt.
    const homeX = circle.offsetLeft + circle.offsetWidth / 2;
    const homeY = circle.offsetTop + circle.offsetHeight / 2;

    // Kreis so weit verschieben, dass seine Mitte bei der Maus liegt.
    // Das transition in der CSS lässt ihn weich hinterhergleiten.
    circle.style.setProperty("--mx", `${mouseX - homeX}px`);
    circle.style.setProperty("--my", `${mouseY - homeY}px`);
  });

  // Maus verlässt den Hero: Kreis gleitet zurück an seinen Platz
  hero.addEventListener("mouseleave", () => {
    circle.style.setProperty("--mx", "0px");
    circle.style.setProperty("--my", "0px");
  });
}


/* ---------- 5. PROJEKTE AUS DER DATENBANK LADEN ---------- */
// "async" heisst: Diese Funktion darf auf etwas warten (hier: auf die Antwort
// des Servers), ohne dass die restliche Seite in der Zeit einfriert.
async function ladeProjekte() {
  const vorlage = document.querySelector("#karten-vorlage");

  try {
    // 1. Schnittstelle aufrufen und auf die Antwort warten
    const antwort = await fetch("api/projekte.php");

    // 2. Hat der Server einen Fehler gemeldet (z. B. 500)? Dann abbrechen.
    if (!antwort.ok) {
      throw new Error(`Server antwortet mit ${antwort.status}`);
    }

    // 3. JSON-Text in ein JavaScript-Array umwandeln
    const projekte = await antwort.json();

    // 4. Für jedes Projekt eine Karte aus der Vorlage bauen
    projekte.forEach((projekt, index) => {
      // Vorlage kopieren (true = mit allem, was darin steckt)
      const karte = vorlage.content.cloneNode(true);

      // Laufnummer mit führender Null: 1 -> "01"
      const nummer = String(index + 1).padStart(2, "0");

      // Texte einsetzen. textContent statt innerHTML: So wird alles als
      // reiner Text behandelt, auch wenn jemand im CMS HTML-Code eintippt.
      karte.querySelector(".card-kategorie").textContent = `${nummer} / ${projekt.kategorie}`;
      karte.querySelector(".card-rolle").textContent = projekt.rolle;
      karte.querySelector(".card-client").textContent = projekt.rahmen;
      // Titel mit erlaubten Umbruchstellen (Funktion steht in main.js)
      setzeTitel(karte.querySelector("h3"), projekt.titel);

      // Bild einsetzen
      const bild = karte.querySelector("img");
      bild.src = projekt.bild;
      bild.alt = `Vorschaubild zum Projekt ${projekt.titel}`;

      // Pfeil-Link führt zur Detailseite, z. B. projekt.html?id=3
      const link = karte.querySelector("a");
      link.href = `projekt.html?id=${projekt.id}`;
      link.setAttribute("aria-label", `Projekt ${projekt.titel} ansehen`);

      // Fertige Karte ins Karussell einfügen
      track.append(karte);
    });

  } catch (fehler) {
    // Etwas ging schief (Server nicht erreichbar, Datenbankfehler …):
    // Statt einer leeren Fläche einen Hinweis anzeigen.
    console.error("Projekte konnten nicht geladen werden:", fehler);
    const hinweis = document.createElement("p");
    hinweis.className = "work-error";
    hinweis.textContent = "Die Projekte konnten gerade nicht geladen werden. Bitte später nochmals versuchen.";
    track.append(hinweis);
  }
}

ladeProjekte();


/* ---------- 6. SKILLS AUS DER DATENBANK LADEN ----------
   Gleiches Prinzip wie bei den Projekten: Schnittstelle abfragen,
   Vorlage für jeden Skill kopieren, befüllen und einsetzen. */
async function ladeSkills() {
  const liste = document.querySelector("#skills-liste");
  const vorlage = document.querySelector("#skill-vorlage");

  try {
    const antwort = await fetch("api/skills.php");
    if (!antwort.ok) {
      throw new Error(`Server antwortet mit ${antwort.status}`);
    }
    const skills = await antwort.json();

    skills.forEach((skill, index) => {
      const eintrag = vorlage.content.cloneNode(true);
      eintrag.querySelector(".skill-nr").textContent = String(index + 1).padStart(2, "0");
      eintrag.querySelector(".skill-name").textContent = skill.name;
      eintrag.querySelector(".skill-tool").textContent = skill.werkzeug;
      liste.append(eintrag);
    });

  } catch (fehler) {
    // Ohne Skills ist die Seite trotzdem vollständig: Liste einfach ausblenden
    console.error("Skills konnten nicht geladen werden:", fehler);
    liste.hidden = true;
  }
}

ladeSkills();
