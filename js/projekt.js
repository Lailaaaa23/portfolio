/* =========================================================
   PORTFOLIO – JavaScript nur für die DETAILSEITE (projekt.html)
   1. Video-Link in eine Einbettungs-Adresse umwandeln
   2. Projekt aus der Datenbank laden und anzeigen
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


/* ---------- 2. PROJEKT LADEN UND ANZEIGEN ---------- */
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
    titel.textContent = projekt.titel;
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

    // ----- Weitere Bilder (nur wenn vorhanden) -----
    if (projekt.bilder.length > 0) {
      const raster = document.querySelector("#galerie-raster");

      projekt.bilder.forEach((eintrag) => {
        const figur = document.createElement("figure");
        const bild = document.createElement("img");
        bild.src = eintrag.pfad;
        bild.alt = eintrag.beschreibung;
        bild.loading = "lazy";
        figur.append(bild);

        // Bildunterschrift nur, wenn eine Beschreibung eingetragen ist
        if (eintrag.beschreibung) {
          const unterschrift = document.createElement("figcaption");
          unterschrift.textContent = eintrag.beschreibung;
          figur.append(unterschrift);
        }

        raster.append(figur);
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
