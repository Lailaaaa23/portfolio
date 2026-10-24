/* =========================================================
   PORTFOLIO – JavaScript nur für die DETAILSEITE (projekt.html)
   1. Video-Link in eine Einbettungs-Adresse umwandeln
   2. Seitenverhältnis für die Galerie
   3. Bild oder Video vergrössern (Lightbox)
   4. Videos: automatisch abspielen beim Scrollen
   5. Projekt aus der Datenbank laden und anzeigen
   ========================================================= */


/* ---------- 1. VIDEO-LINK UMWANDELN ----------
   Im CMS wird der normale Link eingetragen, so wie man ihn im Browser sieht.
   Für den eingebetteten Player brauchen YouTube und Vimeo aber eine
   andere Adresse ("embed"). Diese Funktion baut sie um.

   Beispiele:
   https://www.youtube.com/watch?v=abc123XYZ_-  -> https://www.youtube-nocookie.com/embed/abc123XYZ_-
   https://youtu.be/abc123XYZ_-                 -> (gleich wie oben)
   https://vimeo.com/123456789                  -> https://player.vimeo.com/video/123456789?dnt=1
   https://vimeo.com/123456789/a1b2c3d4e5       -> (nicht gelistetes Vimeo-Video, mit Freigabe-Code) */
function videoEinbettung(link) {
  // YouTube: die Video-ID ist immer 11 Zeichen lang
  const youtube = link.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{11})/);
  if (youtube) {
    // youtube-nocookie.com setzt erst Cookies, wenn man auf Play drückt (Datenschutz)
    return `https://www.youtube-nocookie.com/embed/${youtube[1]}`;
  }

  // Vimeo: eine Zahl, optional gefolgt von einem Freigabe-Code
  const vimeo = link.match(/vimeo\.com\/(?:video\/)?(\d+)(?:\/(\w+))?/);
  if (vimeo) {
    // dnt=1 ("do not track"): Vimeo soll die Besucher nicht verfolgen
    const freigabe = vimeo[2] ? `&h=${vimeo[2]}` : "";
    return `https://player.vimeo.com/video/${vimeo[1]}?dnt=1${freigabe}`;
  }

  // Unbekannter Anbieter: lieber kein Video als ein kaputtes
  return null;
}


/* ---------- 2. SEITENVERHÄLTNIS FÜR DIE GALERIE ----------
   In der Galerie haben alle Bilder einer Reihe dieselbe Höhe
   (siehe .gallery-grid in style.css). Dafür muss die CSS wissen, wie
   breit jedes Bild im Verhältnis zu seiner Höhe ist: 1.5 bei einem
   Querformat 3:2, 0.5625 bei einem Hochformat 9:16 usw.
   Diese Zahl erfährt man erst, wenn das Bild geladen ist. */
function setzeVerhaeltnis(figur, breite, hoehe) {
  if (breite > 0 && hoehe > 0) {
    figur.style.setProperty("--verhaeltnis", breite / hoehe);
  }
}


/* ---------- 3. BILD ODER VIDEO VERGRÖSSERN (Lightbox) ----------
   Alle Einträge der Galerie kommen in eine Liste, in derselben
   Reihenfolge wie auf der Seite (zuerst Bilder, dann Videos).
   Ein Klick öffnet den gewählten Eintrag gross im <dialog>,
   mit ← und → blättert man durch die Liste. */
const lightbox = document.querySelector("#lightbox");
const lightboxBild = document.querySelector("#lightbox-bild");
const lightboxVideo = document.querySelector("#lightbox-video");
const lightboxText = document.querySelector("#lightbox-text");
const galerieEintraege = [];   // alle Einträge der Galerie, Nummer = data-nr der <figure>
let lightboxListe = [];        // dieselben Einträge in der Reihenfolge, wie sie auf der Seite stehen
let aktuell = 0;

// Ermittelt die Reihenfolge so, wie man sie auf der Seite sieht:
// Bereich für Bereich (quer, hoch, Videos), innerhalb eines Bereichs nach "order".
function reihenfolgeAufDerSeite() {
  const figuren = [...document.querySelectorAll("#projekt-galerie .gallery-grid")].flatMap((bereich) =>
    [...bereich.children].sort((a, b) => a.style.order - b.style.order)
  );
  return figuren.map((figur) => galerieEintraege[figur.dataset.nr]);
}

function istVideo(eintrag) {
  return eintrag.pfad.endsWith(".mp4");
}

function zeigeEintrag(nummer) {
  // % (Rest beim Teilen) sorgt dafür, dass es nach dem letzten Eintrag
  // wieder beim ersten weitergeht und vor dem ersten beim letzten
  aktuell = (nummer + lightboxListe.length) % lightboxListe.length;
  const eintrag = lightboxListe[aktuell];

  if (istVideo(eintrag)) {
    // Video zeigen und abspielen, Bild verstecken
    lightboxBild.hidden = true;
    lightboxVideo.hidden = false;
    lightboxVideo.src = eintrag.pfad;
    lightboxVideo.setAttribute("aria-label", eintrag.beschreibung || "Video");
    lightboxVideo.play().catch(() => {});
  } else {
    // Bild zeigen, Video anhalten und verstecken
    lightboxVideo.pause();
    lightboxVideo.hidden = true;
    lightboxBild.hidden = false;
    lightboxBild.src = eintrag.pfad;
    lightboxBild.alt = eintrag.beschreibung;
  }
  lightboxText.textContent = eintrag.beschreibung;
}

function oeffneLightbox(eintrag) {
  // Liste erst beim Öffnen bilden: Bis dahin sind alle Bilder geladen
  // und in ihrer endgültigen Reihe (quer oder hoch)
  lightboxListe = reihenfolgeAufDerSeite();
  zeigeEintrag(lightboxListe.indexOf(eintrag));
  lightbox.showModal();   // öffnet den Dialog über der Seite

  // Blätter-Knöpfe nur zeigen, wenn es mehr als einen Eintrag gibt
  const mehrere = lightboxListe.length > 1;
  document.querySelector("#lightbox-zurueck").hidden = !mehrere;
  document.querySelector("#lightbox-weiter").hidden = !mehrere;
}

document.querySelector("#lightbox-schliessen").addEventListener("click", () => lightbox.close());
document.querySelector("#lightbox-zurueck").addEventListener("click", () => zeigeEintrag(aktuell - 1));
document.querySelector("#lightbox-weiter").addEventListener("click", () => zeigeEintrag(aktuell + 1));

// Beim Schliessen (egal ob ×, Esc oder Hintergrund) ein laufendes Video anhalten
lightbox.addEventListener("close", () => lightboxVideo.pause());

// Klick auf den dunklen Hintergrund schliesst. Der Hintergrund gehört zum
// <dialog> selbst, ein Klick aufs Bild oder auf einen Knopf dagegen nicht.
lightbox.addEventListener("click", (event) => {
  if (event.target === lightbox) {
    lightbox.close();
  }
});

// Pfeiltasten der Tastatur zum Blättern (Esc schliesst der Dialog von selbst)
lightbox.addEventListener("keydown", (event) => {
  if (event.key === "ArrowLeft") zeigeEintrag(aktuell - 1);
  if (event.key === "ArrowRight") zeigeEintrag(aktuell + 1);
});


/* ---------- 4. VIDEOS: AUTOMATISCH ABSPIELEN BEIM SCROLLEN ----------
   Browser erlauben automatisches Abspielen nur ohne Ton (muted).
   Darum laufen die Videos in der Galerie stumm und in Schleife,
   sobald sie ins Bild kommen, und pausieren, wenn man wegscrollt.
   Gross ansehen (mit Play-Leiste) kann man sie in der Lightbox.
   Wer im Betriebssystem "Bewegung reduzieren" eingestellt hat, sieht
   nur das erste Bild des Videos, nichts startet von selbst. */
const bewegungReduziert = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

// Der IntersectionObserver meldet, wenn ein Element ins Bild kommt oder es verlässt.
// threshold 0.2 = Bescheid geben, sobald ein Fünftel des Videos sichtbar ist.
const videoBeobachter = new IntersectionObserver((eintraege) => {
  eintraege.forEach((eintrag) => {
    const video = eintrag.target;
    if (eintrag.isIntersecting) {
      // play() gibt ein Versprechen (Promise) zurück. Lehnt der Browser ab,
      // z. B. im Stromsparmodus, fangen wir das still ab statt einen Fehler zu zeigen.
      video.play().catch(() => {});
    } else {
      video.pause();
    }
  });
}, { threshold: 0.2 });

function baueVideo(eintrag, figur) {
  const video = document.createElement("video");
  video.src = eintrag.pfad;
  video.muted = true;         // nur stumm darf ein Video von selbst starten
  video.loop = true;          // in Schleife
  video.playsInline = true;   // auf dem iPhone nicht automatisch im Vollbild
  // "auto": das ganze Video schon vorladen, damit es sofort starten kann.
  // Passt für kurze Clips von wenigen MB.
  video.preload = "auto";
  figur.classList.add("is-video");

  // Sobald das Video weiss, wie gross es ist: Seitenverhältnis merken
  video.addEventListener("loadedmetadata", () => {
    setzeVerhaeltnis(figur, video.videoWidth, video.videoHeight);
  });

  if (!bewegungReduziert) {
    videoBeobachter.observe(video);
  }

  return video;
}


/* ---------- 5. PROJEKT LADEN UND ANZEIGEN ---------- */
async function ladeProjekt() {
  const titel = document.querySelector("#projekt-titel");

  // Die Projekt-Nummer steht in der Adresse: projekt.html?id=3
  const parameter = new URLSearchParams(window.location.search);
  const id = parameter.get("id");

  try {
    if (!id) {
      throw new Error("Keine Projekt-ID in der Adresse");
    }

    // Projekt bei der Schnittstelle abfragen.
    // encodeURIComponent schützt davor, dass Sonderzeichen die Adresse kaputt machen.
    const antwort = await fetch(`api/projekte.php?id=${encodeURIComponent(id)}`);
    if (!antwort.ok) {
      throw new Error(`Server antwortet mit ${antwort.status}`);
    }
    const projekt = await antwort.json();

    // ----- Texte einsetzen (immer textContent, siehe startseite.js) -----
    document.title = `${projekt.titel} – Laila Keller`;
    setzeTitel(titel, projekt.titel);   // mit Umbruchstellen, siehe main.js
    document.querySelector("#projekt-kategorie").textContent = projekt.kategorie;
    document.querySelector("#projekt-rolle").textContent = projekt.rolle;
    document.querySelector("#projekt-rahmen").textContent = projekt.rahmen;
    document.querySelector("#projekt-beschreibung").textContent = projekt.beschreibung;

    // ----- Video oder Bild -----
    const medien = document.querySelector("#projekt-medien");
    const videoAdresse = projekt.video ? videoEinbettung(projekt.video) : null;

    if (videoAdresse) {
      // Ein <iframe> ist ein "Fenster" in eine andere Webseite, hier zum Video-Player
      const player = document.createElement("iframe");
      player.src = videoAdresse;
      player.title = `Video: ${projekt.titel}`;
      player.allow = "fullscreen; picture-in-picture";
      player.loading = "lazy";
      medien.append(player);
      medien.classList.add("is-video");
    } else {
      const bild = document.createElement("img");
      bild.src = projekt.bild;
      bild.alt = `Vorschaubild zum Projekt ${projekt.titel}`;
      medien.append(bild);
    }

    // ----- Optionaler Link (z. B. Spotify) -----
    if (projekt.link) {
      const link = document.querySelector("#projekt-link");
      link.href = projekt.link;
      link.hidden = false;
    }

    // ----- Weitere Bilder und Videos (nur wenn vorhanden) -----
    if (projekt.bilder.length > 0) {
      const quer = document.querySelector("#galerie-quer");
      const hoch = document.querySelector("#galerie-hoch");
      const videos = document.querySelector("#galerie-videos");

      projekt.bilder.forEach((eintrag, index) => {
        const figur = document.createElement("figure");
        // Nummer des Eintrags merken (für die Lightbox) und Reihenfolge aus
        // dem CMS festhalten: "order" sortiert innerhalb eines Bereichs,
        // auch wenn ein Bild später in einen anderen Bereich verschoben wird.
        figur.dataset.nr = galerieEintraege.length;
        galerieEintraege.push(eintrag);
        figur.style.order = index;

        let medium;
        if (istVideo(eintrag)) {
          medium = baueVideo(eintrag, figur);
          videos.append(figur);
        } else {
          medium = document.createElement("img");
          medium.src = eintrag.pfad;
          medium.alt = eintrag.beschreibung;
          medium.loading = "lazy";

          // Zuerst in den Bereich für Querformate. Ob das Bild hoch oder
          // quer ist, weiss man erst, wenn es geladen ist.
          quer.append(figur);
          medium.addEventListener("load", () => {
            setzeVerhaeltnis(figur, medium.naturalWidth, medium.naturalHeight);
            // Höher als breit? Dann in den Bereich für Hochformate verschieben
            if (medium.naturalHeight > medium.naturalWidth) {
              hoch.append(figur);
            }
          });
        }

        // Bild bzw. Video steckt in einem <button>, der die Lightbox öffnet.
        // So lässt es sich auch mit der Tastatur (Tab + Enter) anwählen.
        const knopf = document.createElement("button");
        knopf.type = "button";
        knopf.className = "gallery-open";
        const art = istVideo(eintrag) ? "Video" : "Bild";
        knopf.setAttribute("aria-label", `${art} vergrössern${eintrag.beschreibung ? ": " + eintrag.beschreibung : ""}`);
        knopf.append(medium);
        knopf.addEventListener("click", () => oeffneLightbox(eintrag));
        figur.append(knopf);

        // Bildunterschrift nur, wenn eine Beschreibung eingetragen ist
        if (eintrag.beschreibung) {
          const unterschrift = document.createElement("figcaption");
          unterschrift.textContent = eintrag.beschreibung;
          figur.append(unterschrift);
        }
      });

      document.querySelector("#projekt-galerie").hidden = false;
    }

  } catch (fehler) {
    // Keine ID, unbekannte ID oder Server-Problem: freundlicher Hinweis
    console.error("Projekt konnte nicht geladen werden:", fehler);
    document.title = "Projekt nicht gefunden – Laila Keller";
    titel.textContent = "Projekt nicht gefunden.";
    document.querySelector("#projekt-beschreibung").textContent =
      "Dieses Projekt gibt es nicht (mehr). Zurück zu allen Projekten geht es oben links.";
    document.querySelector(".project-facts").hidden = true;
  }
}

ladeProjekt();
