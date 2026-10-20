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
- **Projektordner verschoben:** Der Ordner lag auf dem iCloud-synchronisierten Schreibtisch. Bei vollem Speicher hat iCloud die Dateien ausgelagert, sodass weder VS Code noch Git sie lesen konnten. Das Projekt wurde deshalb frisch von GitHub nach `~/Developer/portfolio` geklont, einen Ordner ohne iCloud-Sync. Das MAMP-Stammverzeichnis zeigt jetzt dorthin.
- **Header:** Logo von „MP“ auf meine Initialen „LK“ geändert. Die Navigation stand nicht genau in der Mitte, weil `justify-content: space-between` den Platz nur *zwischen* die Elemente verteilt und Logo und Status unterschiedlich breit sind. Gelöst mit einem CSS-Grid mit drei Spalten (`1fr auto 1fr`): Die äusseren Spalten sind immer gleich breit, die Navigation steht dadurch exakt mittig. Auf dem Handy bleibt der Header bei Flexbox (Logo links, Hamburger rechts).
- **Hero ohne Foto:** Ein bildschirmfüllendes Porträt direkt beim Einstieg wirkte zu dominant, und das Foto ist mit 1280 px Breite für grosse Bildschirme zu klein. Der Hero zeigt jetzt nur Typografie (Name, Stern, Claim) auf dunklem Grund. Das Porträt ist kleiner im Teil „Über mich“ eingebunden, dort reicht die Auflösung gut aus.
- **Porträt bei „Über mich“:** Im Hochformat 4:5 zugeschnitten mit `object-fit: cover`, schwarz-weiss per `filter: grayscale(1)`, beim Darüberfahren farbig. Die Datei heisst jetzt `portrait.jpg` (Kleinbuchstaben, passender Name statt `hero.JPG`). Das Sternchen sitzt neu unter der Headline, weil es rechts daneben ins Foto geragt hätte.
- **Echte Projekte im Karussell:** Die vier Platzhalterkarten mit Grafiken wurden durch sechs echte Projekte ersetzt: Amygdala, Somedia, Berlin, Multitalk, Podcast Chäller und Nulltoleranz. Jede Karte zeigt Kategorie, meine Rolle, den Rahmen (Modul bzw. Auftraggeberin) und ein Vorschaubild. Die Bilder sind schwarz-weiss und werden beim Darüberfahren farbig.
- **Vorschaubilder fürs Web aufbereitet:** Die Originale hatten verschiedene Formate (PNG, JPG, PDF) und bis zu 3000 px Breite. Mit dem Mac-Werkzeug `sips` wurden sie auf 1600 px Breite verkleinert und als JPG gespeichert (`images/projekte/`). Das PDF der Nulltoleranz-Kampagne wurde dabei in ein JPG umgewandelt, weil ein `<img>`-Element keine PDFs anzeigen kann. Zusammen sind die Bilder jetzt rund 1 MB statt 4 MB gross. Im Browser haben alle Karten dasselbe Format 3:2 (`aspect-ratio` und `object-fit: cover`).
- **3D entfernt:** Die Karte „3D & Motion“ und der Skill Blender wurden gestrichen, weil es dazu keine Projekte gibt.
- **Fehler auf dem Handy:** Die Karte „Nulltoleranz“ war breiter als die anderen, weil das lange Wort nicht umbrechen konnte und die Karte deshalb mitwuchs. Gelöst mit `min-width: 0` auf der Karte und `hyphens: auto` beim Titel (Silbentrennung).
- **Heller Hero:** Ohne Foto war der Hero eine grosse schwarze Fläche, die nahtlos in den ebenfalls dunklen Teil „Selected Work“ überging. Der Hero ist jetzt hell mit dunkler Schrift, so wechseln sich helle und dunkle Bereiche auf der ganzen Seite ab.
- **Animation des Namens:** Beim Laden gleiten die Buchstaben von „Laila Keller“ nacheinander von unten ins Bild, danach erscheinen Stern und Claim. JavaScript verpackt dafür jeden Buchstaben in ein eigenes `<span>` und gibt ihm eine Nummer (`--i`). Die CSS-Animation startet jeden Buchstaben um diese Nummer × 60 ms verzögert. Jedes Wort wirkt wie ein Fenster (`overflow: hidden`), deshalb sind die Buchstaben vorher unsichtbar. Für Screenreader bleibt der Name über `aria-label` normal lesbar. Wer im Betriebssystem „Bewegung reduzieren“ eingestellt hat, sieht den Namen sofort ohne Animation (`prefers-reduced-motion`).

## Learnings und Schwierigkeiten

- **PHP braucht einen Server:** HTML, CSS und JavaScript laufen direkt im Browser, PHP dagegen nur auf einem Server. Deshalb braucht es lokal MAMP.
- **Datenbank-Host:** Der Host `efanahiw.mysql.db.internal` ist nur vom Hostpoint-Server aus erreichbar. Lokal und online gelten deshalb unterschiedliche Zugangsdaten. Genau dafür ist die `config.php` da.
- **Gross-/Kleinschreibung:** macOS unterscheidet bei Dateinamen nicht zwischen `hero.jpg` und `hero.JPG`, der Linux-Server bei Hostpoint schon. Dateinamen im Code müssen deshalb exakt stimmen.
- **Externe Links** brauchen immer `https://` am Anfang.
- **Git und iCloud vertragen sich nicht:** iCloud kann Dateien jederzeit auslagern oder gleichzeitig synchronisieren, während Git sie bearbeitet. Code-Projekte gehören deshalb in einen Ordner ohne Cloud-Sync. Als Backup dient GitHub.
- **Bilder fürs Web:** Bilder direkt aus der Kamera oder aus InDesign sind oft viel zu gross. Für eine Webseite reichen etwa 1600 px Breite, sonst lädt die Seite unnötig lange. PDFs können nicht als Bild eingebunden werden.
- **Flex-Elemente wachsen mit dem Inhalt:** Ein Element in einem Flexbox ist standardmässig mindestens so breit wie sein längstes Wort. `min-width: 0` hebt das auf.
- **Browser-Cache:** Nach einer Änderung an JavaScript oder CSS zeigt der Browser manchmal noch die alte Version an, weil er die Datei zwischengespeichert hat. Abhilfe schafft ein harter Reload mit cmd + shift + R.
- **Elemente mittig ausrichten:** `space-between` zentriert das mittlere Element nur, wenn die äusseren gleich breit sind. Ein Grid mit `1fr auto 1fr` zentriert zuverlässig.

## Ressourcen und Hilfsmittel

- Schriften: Archivo und DM Sans (Google Fonts)
- Design-Entwurf: Figma Make
- KI-Unterstützung: Claude (Anthropic) für Code und Erklärungen
