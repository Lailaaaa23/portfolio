# Portfolio Laila Keller

Persönliche Portfolio-Webseite mit eigenem CMS, umgesetzt im Modul Interaktive Medien 5 (HS26, FHGR).

**Live:** https://efanahiw.myhostpoint.ch

## Projektbeschreibung

_Folgt: Kurzbeschreibung des Projekts (500–1000 Zeichen)._

## Technologien

- HTML, CSS, JavaScript (Frontend)
- PHP und MySQL/MariaDB (CMS und Schnittstelle)
- Hosting: Hostpoint

## Ordnerstruktur

```
portfolio/
├── index.html           Startseite (One-Pager)
├── css/style.css        Gestaltung
├── js/main.js           Interaktionen
├── images/              Bilder der Webseite
├── api/                 Schnittstelle: liefert Inhalte als JSON
├── admin/               CMS (Login, Inhalte verwalten)
├── uploads/             Bilder aus dem CMS (nicht im Repo)
└── config.example.php   Vorlage für die Zugangsdaten
```

## Lokales Setup

1. Repository klonen
2. MAMP installieren und das Stammverzeichnis auf den Projektordner setzen
3. `config.example.php` kopieren, in `config.php` umbenennen und Zugangsdaten eintragen
4. `http://localhost:8888` öffnen

## Umsetzungsprozess

Das Arbeitsjournal hält fest, was wann umgesetzt wurde und welche Entscheidungen dahinterstehen.

### 19.10.2026 – Kick-off und Planung
- Proposal eingereicht
- Grobes Design in Figma Make erstellt: One-Pager mit Hero, Projekt-Karussell, About & Skills und Kontakt
- Entscheid: Den von Figma Make erzeugten React-Code nicht übernehmen, sondern das Design von Grund auf in HTML, CSS und JavaScript nachbauen. So ist der Code schlanker und vollständig nachvollziehbar.
- Architektur festgelegt: statisches Frontend plus eigenes CMS mit PHP und MySQL. Das Frontend lädt die Inhalte später über eine kleine JSON-Schnittstelle (`api/`).

### 20.10.2026 – Hosting, Entwicklungsumgebung und Grundstruktur
- **Hosting:** Bestehendes Hostpoint-Webhosting der FHGR geprüft (gültig bis 2029). Mangels eigener Domain wird vorerst die Testdomain `efanahiw.myhostpoint.ch` verwendet.
- **Datenbank:** Neue Datenbank `efanahiw_portfolio` mit eigenem Benutzer angelegt. Die Datenbank des Vorsemesters bleibt unberührt.
- **Lokale Entwicklung:** MAMP eingerichtet, damit PHP und MySQL auf dem eigenen Mac laufen. Änderungen werden zuerst lokal unter `localhost:8888` getestet und erst danach hochgeladen.
- **Versionierung:** Öffentliches GitHub-Repository erstellt und in VS Code geklont.
- **Sicherheit:** Zugangsdaten gehören in eine `config.php`, die über `.gitignore` vom Repository ausgeschlossen ist. Im Repo liegt nur die Vorlage `config.example.php`.
- **Startseite:** Header, Hero, Projekt-Karussell, About & Skills und Kontakt nach dem Figma-Entwurf umgesetzt. Farben und Schriften sind als CSS-Variablen zentral definiert. Für Mobile gibt es ein Hamburger-Menü und angepasste Layouts.
- **Interaktionen (JavaScript):** Menü auf- und zuklappen; Projektkarten per Pfeil-Buttons durchblättern.
- **Hero-Bild:** Die Sektion war zunächst immer so hoch wie das Browserfenster, wodurch das Foto stark vergrössert und beschnitten wurde. Gelöst mit `aspect-ratio: 3 / 2`, also dem Seitenverhältnis des Fotos. Auf dem Handy wird die Sektion höher und das Bild auf das Gesicht ausgerichtet. Ein dunkler Verlauf am unteren Rand sorgt für lesbaren Text.
- **Kontaktlinks:** Der LinkedIn-Link funktionierte nicht, weil `https://` fehlte. Ohne Protokoll interpretiert der Browser die Adresse als Datei im eigenen Projekt.

## Learnings und Schwierigkeiten

- **PHP braucht einen Server:** HTML, CSS und JavaScript laufen direkt im Browser, PHP dagegen nur auf einem Server. Deshalb braucht es lokal MAMP.
- **Datenbank-Host:** Der Host `efanahiw.mysql.db.internal` ist nur vom Hostpoint-Server aus erreichbar. Lokal und online gelten deshalb unterschiedliche Zugangsdaten. Genau dafür ist die `config.php` da.
- **Gross-/Kleinschreibung:** macOS unterscheidet bei Dateinamen nicht zwischen `hero.jpg` und `hero.JPG`, der Linux-Server bei Hostpoint schon. Dateinamen im Code müssen deshalb exakt stimmen.
- **Externe Links** brauchen immer `https://` am Anfang.

## Ressourcen und Hilfsmittel

- Schriften: Archivo und DM Sans (Google Fonts)
- Design-Entwurf: Figma Make
- KI-Unterstützung: Claude (Anthropic) für Code und Erklärungen
