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
├── projekt.html         Detailseite eines Projekts (projekt.html?id=3)
├── css/style.css        Gestaltung (alle Seiten)
├── css/admin.css        Zusätzliche Gestaltung fürs CMS
├── js/main.js           Navigation (alle Seiten)
├── js/startseite.js     Hero-Animation, Karussell, Projekte laden
├── js/projekt.js        Detailseite: Projekt laden, Video einbetten
├── images/              Bilder der Webseite
├── api/                 Schnittstelle: liefert Inhalte als JSON
├── includes/db.php      Verbindung zur Datenbank (von api/ und admin/ genutzt)
├── includes/admin.php   CMS-Grundlagen: Session, Login-Prüfung, CSRF-Schutz, Meldungen
├── includes/admin-kopf.php / admin-fuss.php   gemeinsamer Rahmen aller CMS-Seiten
├── admin/               CMS (Login, Inhalte verwalten)
├── .htaccess            sperrt config.php für den Browser
├── uploads/             Bilder aus dem CMS (nicht im Repo)
├── datenbank/           SQL-Dateien: Tabellen, Startinhalte, Updates
└── config.example.php   Vorlage für die Zugangsdaten
```

## Lokales Setup

1. Repository klonen
2. MAMP installieren und das Stammverzeichnis auf den Projektordner setzen
3. In phpMyAdmin (`http://localhost:8888/phpMyAdmin5/`) eine leere Datenbank `efanahiw_portfolio` mit Kollation `utf8mb4_unicode_ci` anlegen und die Dateien aus `datenbank/` der Reihe nach importieren: zuerst `portfolio.sql`, dann `update_01_video_und_bilder.sql`
4. `config.example.php` kopieren, in `config.php` umbenennen und Zugangsdaten eintragen
5. `http://localhost:8888` öffnen
6. Einmalig unter `http://localhost:8888/admin/einrichten.php` den eigenen CMS-Zugang anlegen, danach Login unter `admin/login.php`

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
- **Mehr Lavendel im Hero:** Der helle Hero wirkte zu leer. Drei Varianten skizziert (dezent mit Laufband, grosser Kreis, ganz lila Hintergrund) und zwei davon kombiniert:
  - Ein **lila Punkt** hinter dem Namen nimmt das Logo „LK.“ auf. Er ploppt nach den Buchstaben auf, löst sich dann, schwebt ein Stück weg und wächst zum **grossen lila Kreis** hinter dem Namen. Technisch sind Punkt und Kreis zwei Elemente: Sobald der Punkt fertig aufgeploppt ist (`animationend`), misst JavaScript mit `getBoundingClientRect()`, wo beide liegen und wie gross sie sind. Dann wird der Punkt ausgeblendet und der Kreis klein genau an seine Stelle gesetzt und mit der Web Animations API (`element.animate()`) an seinen Platz bewegt. Für das Auge ist es derselbe Punkt.
  - Danach **folgt der Kreis der Maus** über den ganzen Hero. JavaScript rechnet aus, wie weit die Maus von der Mitte des Kreises an seinem Ausgangsplatz entfernt ist (`offsetLeft`/`offsetTop`), und verschiebt ihn um genau diesen Abstand. Die Werte gehen als CSS-Variablen (`--mx`, `--my`) an die CSS, wo ein `transition` den Kreis weich hinterhergleiten lässt. Auf dem Handy (keine Maus) und bei „Bewegung reduzieren“ bleibt der Kreis still.
  - Ein **Laufband** zwischen Hero und Projekten zeigt meine Formate (Video, TV, Radio …) und trennt den hellen Hero vom dunklen Projektteil. Der Text steht zweimal hintereinander. Die Animation verschiebt ihn um genau die Hälfte, dann beginnt sie unbemerkt von vorn, so wirkt das Band endlos.

### 20.10.2026 – Datenbank
- **Tabellen geplant und angelegt** (`datenbank/portfolio.sql`):
  - `projekte`: alles, was eine Projektkarte und die Detailseite brauchen (Titel, Kategorie, Rolle, Rahmen, Beschreibung, Bild, optionaler Link). Dazu `reihenfolge`, um die Projekte im CMS sortieren zu können, und `sichtbar`, um ein Projekt zu verstecken, ohne es zu löschen.
  - `skills`: die Liste bei „Über mich“.
  - `einstellungen`: einzelne Werte nach dem Muster „Name → Wert“, z. B. der Status „Available for projects“. Neue Einstellungen brauchen so keine neue Spalte.
  - `benutzer`: Login fürs CMS. Gespeichert wird nur ein Hash des Passworts, nie das Passwort selbst.
- **Entscheid Kategorie:** Die Kategorie ist vorerst ein einfaches Textfeld statt einer eigenen Tabelle. Bei sechs Projekten und wenigen Kategorien reicht das und hält die Abfragen einfach.
- **Zeichensatz `utf8mb4`:** damit Umlaute wie in „Chäller“ und Sonderzeichen wie „·“ korrekt gespeichert werden.
- **Startinhalte:** Die sechs Projekte, drei Skills und der Status sind direkt in der SQL-Datei eingetragen. Die Projektbeschreibungen sind erste Entwürfe aus meinen Stichworten und werden später im CMS überarbeitet.
- Die Datei enthält keine Zugangsdaten und darf deshalb auf GitHub.

### 20.10.2026 – PHP-Verbindung und erste Schnittstelle
- **`config.php`** lokal aus der Vorlage erstellt (MAMP: root/root). Sie ist per `.gitignore` vom Repository ausgeschlossen und gibt im Browser nichts aus, weil sie nur ein Array zurückgibt.
- **`includes/db.php`** enthält die Funktion `verbindeDatenbank()`. Sie liest die Zugangsdaten aus `config.php` und baut mit **PDO** (PHPs eingebaute Datenbank-Schnittstelle) die Verbindung auf. Weil alle PHP-Dateien diese eine Funktion nutzen, steht der Verbindungscode nur an einer Stelle.
- **`api/projekte.php`** ist die erste eigene Schnittstelle. Sie liefert die Projekte als JSON:
  - ohne Parameter alle sichtbaren Projekte, sortiert nach `reihenfolge` (für das Karussell)
  - mit `?id=3` ein einzelnes Projekt inkl. Beschreibung (für die Detailseite)
- **Sicherheit:** Die `id` aus der Adresse wird geprüft (nur ganze Zahlen) und über ein **Prepared Statement** an die Datenbank übergeben. So kann niemand über die Adresse eigenen SQL-Code einschleusen. Datenbankfehler werden nicht im Detail angezeigt, weil sie Informationen über den Server verraten könnten.
- **HTTP-Statuscodes:** Die Schnittstelle antwortet mit 200 (OK), 400 (ungültige ID), 404 (Projekt nicht gefunden) oder 500 (Datenbankfehler). So kann das Frontend später unterscheiden, was schiefging.
- **Getestet** im Browser unter `localhost:8888/api/projekte.php` und mit `?id=5`, `?id=99` und `?id=abc`.

### 20.10.2026 – Projektkarten per fetch aus der Datenbank
- Die sechs Projektkarten stehen nicht mehr fest im HTML. `js/main.js` holt sie beim Laden der Seite mit `fetch("api/projekte.php")` ab und baut daraus die Karten.
- **Vorlage mit `<template>`:** Der Aufbau einer Karte steht einmal als `<template id="karten-vorlage">` im HTML. Der Browser zeigt ein Template nicht an. JavaScript kopiert es für jedes Projekt (`cloneNode`) und setzt Titel, Kategorie, Rolle, Rahmen und Bild ein. So bleibt das HTML-Gerüst im HTML und muss nicht als Text im JavaScript stehen.
- **`textContent` statt `innerHTML`:** Inhalte aus der Datenbank werden immer als reiner Text eingesetzt. Würde im CMS jemand HTML- oder Script-Code in ein Feld schreiben, erscheint er nur als Text und wird nicht ausgeführt.
- **`async` / `await`:** Die Funktion `ladeProjekte()` wartet auf die Antwort des Servers, ohne die restliche Seite zu blockieren. Die Animationen im Hero laufen also weiter, während die Projekte laden.
- **Fehlerfall:** Antwortet der Server mit einem Fehler oder ist er nicht erreichbar, erscheint ein Hinweis statt einer leeren Fläche (`try` / `catch`).
- Die Pfeil-Links der Karten zeigen jetzt auf `projekt.html?id=…`. Die Detailseite folgt im nächsten Schritt.

### 20.10.2026 – Detailseite für Projekte
- **Datenbank erweitert** (`datenbank/update_01_video_und_bilder.sql`):
  - neues Feld `video` in `projekte` für einen YouTube- oder Vimeo-Link
  - neue Tabelle `projekt_bilder` für beliebig viele weitere Bilder pro Projekt (z. B. Behind the Scenes). Das ist eine **1:n-Beziehung**: Ein Projekt hat n Bilder. Jedes Bild verweist über `projekt_id` auf sein Projekt. Ein **Fremdschlüssel** stellt sicher, dass es dieses Projekt wirklich gibt, und `ON DELETE CASCADE` löscht die Bilder mit, wenn das Projekt gelöscht wird.
  - Die Erweiterung ist eine eigene Datei statt einer Änderung an `portfolio.sql`, weil die Datenbank schon Inhalte hat. So lässt sich die bestehende Datenbank (lokal und später online) ergänzen, ohne sie neu aufzusetzen.
- **Schnittstelle:** `api/projekte.php?id=…` liefert jetzt zusätzlich `video` und eine Liste `bilder`.
- **`projekt.html`:** grosser Titel, Rolle und Rahmen, dann Video oder Vorschaubild, Beschreibung, optionaler Link (z. B. Spotify) und weitere Bilder. Abschnitte ohne Inhalt werden ausgeblendet (`hidden`). Die Seite liest die Projektnummer aus der Adresse (`URLSearchParams`) und lädt das Projekt per `fetch`.
- **Video einbetten:** Im CMS wird später der normale YouTube- oder Vimeo-Link eingetragen. `projekt.js` wandelt ihn in die Adresse für den eingebetteten Player um. Für YouTube wird `youtube-nocookie.com` verwendet, für Vimeo `dnt=1`: Beides reduziert das Tracking der Besucherinnen und Besucher.
- **JavaScript aufgeteilt:** `main.js` enthält nur noch, was alle Seiten brauchen (Navigation). Hero-Animation und Karussell stehen in `startseite.js`, die Detailseite hat `projekt.js`. Sonst würde der Code auf der Detailseite nach Elementen suchen, die es dort nicht gibt, und mit Fehlern abbrechen.
- **Getestet:** echtes Projekt, Projekt mit (simuliertem) Video, Link und Galerie, unbekannte ID (`?id=99` zeigt „Projekt nicht gefunden“), Handy-Ansicht.

### 20.10.2026 – CMS: Login
- **Erster Zugang über `admin/einrichten.php`:** Ein Formular, das nur funktioniert, solange es noch keinen Benutzer gibt. Danach sperrt es sich selbst (403). So steht mein Passwort nie im Code oder in einer SQL-Datei, und niemand kann später über diese Seite einen eigenen Zugang anlegen.
- **Passwörter:** Gespeichert wird nur der Hash von `password_hash()` (Verfahren bcrypt). Beim Login vergleicht `password_verify()` das eingegebene Passwort mit diesem Hash. Mindestlänge 12 Zeichen.
- **Login mit Session:** Nach erfolgreichem Login merkt sich der Server in der Session die Benutzer-ID. Jede geschützte Seite ruft `loginErforderlich()` auf und schickt alle ohne Login zur Login-Seite. Nach dem Login wird die Session-Nummer erneuert (`session_regenerate_id`), beim Logout wird die Session vollständig gelöscht.
- **Sicherheit, zusammengefasst in `includes/admin.php`:**
  - **CSRF-Token:** Jedes Formular im CMS enthält ein geheimes Zufalls-Token. Formulare ohne gültiges Token werden abgelehnt. Das verhindert, dass eine fremde Webseite heimlich Aktionen in meinem CMS auslöst.
  - **Ausgabe mit `e()`:** Alle Texte aus Datenbank oder Formular werden mit `htmlspecialchars` entschärft, bevor sie im HTML landen (Schutz vor XSS).
  - **Session-Cookie** nur für den Server lesbar (`httponly`), nicht bei Anfragen von fremden Seiten (`samesite`), online nur über https (`secure`).
  - Bei falschem Login wird nicht verraten, ob Name oder Passwort falsch war, und die Antwort kommt eine Sekunde verzögert. Das bremst automatisches Durchprobieren.
- **Ordner gesperrt mit `.htaccess`:** `config.php`, `includes/` und `datenbank/` liefern im Browser „403 Verboten“. PHP-Dateien können sie trotzdem einbinden.
- **Getestet:** Anlegen ohne Token (abgelehnt), zu kurzes Passwort, Anlegen, Einrichten danach gesperrt, falsches und richtiges Passwort, CMS nach Login erreichbar, Abmelden, CMS danach wieder gesperrt.

### 23.10.2026 – CMS: Projekt-Übersicht
- **`admin/index.php`** zeigt nach dem Login alle Projekte, auch die versteckten, in derselben Reihenfolge wie im Karussell. Pro Projekt gibt es die Knöpfe ← / → (Reihenfolge), Löschen, Bearbeiten und Verstecken bzw. Zeigen.
- **Kacheln statt Liste:** Die erste Version war eine Liste über die ganze Bildschirmbreite, das wirkte zu wuchtig. Jetzt sind die Projekte Kacheln in einem Raster: `grid-template-columns: repeat(auto-fill, minmax(260px, 1fr))` setzt so viele Kacheln nebeneinander, wie mit mindestens 260 px Platz haben. Die Knöpfe stehen dank `margin-top: auto` in allen Kacheln einer Reihe auf gleicher Höhe.
- **Überlappender Button:** „+ Neues Projekt“ ragte in den Text darunter. Ursache: Ein Link ist ein inline-Element, bei dem `padding` oben und unten nicht fürs Layout zählt. Gelöst mit `display: inline-block`.
- **`admin/projekt-aktion.php`** führt die Aktionen aus und leitet danach zurück zur Übersicht. Jeder Knopf ist ein kleines Formular (POST) mit CSRF-Token, Projekt-ID und Aktion. Aktionen, die etwas verändern, gehen bewusst nie über einen einfachen Link (GET).
- **Reihenfolge ändern:** Das Projekt tauscht seinen Platz (`reihenfolge`) mit dem direkten Nachbarn. Beide Änderungen laufen in einer **Transaktion**: Entweder klappen beide oder keine, so können nie zwei Projekte auf demselben Platz landen.
- **Verstecken statt löschen:** `sichtbar` wird zwischen 1 und 0 umgeschaltet. Die öffentliche Schnittstelle liefert nur sichtbare Projekte, das CMS zeigt alle (versteckte blasser und mit Label).
- **Löschen** nur nach Rückfrage im Browser (`confirm`). Die zugehörigen Einträge in `projekt_bilder` löscht die Datenbank automatisch mit (`ON DELETE CASCADE`).
- **Meldungen** wie „„Somedia“ ist jetzt versteckt.“ werden kurz in der Session gespeichert, damit sie nach der Weiterleitung noch angezeigt werden können, und danach gelöscht.
- **Gemeinsamer Rahmen:** Kopf und Fuss aller CMS-Seiten stehen in `includes/admin-kopf.php` und `includes/admin-fuss.php`, damit sie nicht in jeder Datei wiederholt werden.
- **Sicherheitslücke beim eigenen Code gefunden:** Der Projekttitel stand zuerst direkt im JavaScript der Lösch-Rückfrage. Ein Titel mit Apostroph (z. B. „O'Brien“) hätte den Code kaputt gemacht oder sogar fremden Code einschleusen können, weil der Browser `&#039;` vor dem Ausführen wieder in `'` zurückverwandelt. Gelöst, indem der Titel über ein `data-titel`-Attribut übergeben und in JavaScript mit `this.dataset.titel` gelesen wird.
- **Getestet** mit einem vorübergehenden Test-Zugang und Test-Projekt (danach gelöscht): Reihenfolge hoch/runter, erstes Projekt kann nicht weiter nach oben, Verstecken (API liefert dann ein Projekt weniger), Zeigen, Löschen, Aktion ohne Token (400), Aktion ohne Login (Umleitung zum Login), Titel mit Sonderzeichen wird sicher angezeigt.

## Learnings und Schwierigkeiten

- **PHP braucht einen Server:** HTML, CSS und JavaScript laufen direkt im Browser, PHP dagegen nur auf einem Server. Deshalb braucht es lokal MAMP.
- **Datenbank-Host:** Der Host `efanahiw.mysql.db.internal` ist nur vom Hostpoint-Server aus erreichbar. Lokal und online gelten deshalb unterschiedliche Zugangsdaten. Genau dafür ist die `config.php` da.
- **Gross-/Kleinschreibung:** macOS unterscheidet bei Dateinamen nicht zwischen `hero.jpg` und `hero.JPG`, der Linux-Server bei Hostpoint schon. Dateinamen im Code müssen deshalb exakt stimmen.
- **Externe Links** brauchen immer `https://` am Anfang.
- **Git und iCloud vertragen sich nicht:** iCloud kann Dateien jederzeit auslagern oder gleichzeitig synchronisieren, während Git sie bearbeitet. Code-Projekte gehören deshalb in einen Ordner ohne Cloud-Sync. Als Backup dient GitHub.
- **Bilder fürs Web:** Bilder direkt aus der Kamera oder aus InDesign sind oft viel zu gross. Für eine Webseite reichen etwa 1600 px Breite, sonst lädt die Seite unnötig lange. PDFs können nicht als Bild eingebunden werden.
- **Flex-Elemente wachsen mit dem Inhalt:** Ein Element in einem Flexbox ist standardmässig mindestens so breit wie sein längstes Wort. `min-width: 0` hebt das auf.
- **Browser-Cache:** Nach einer Änderung an JavaScript oder CSS zeigt der Browser manchmal noch die alte Version an, weil er die Datei zwischengespeichert hat. Abhilfe schafft ein harter Reload mit cmd + shift + R. Im CMS ist das dauerhaft gelöst: PHP hängt an die CSS-Adresse das Änderungsdatum der Datei an (`admin.css?v=…`, mit `filemtime`). Ändert sich die Datei, ändert sich die Adresse, und der Browser muss die neue Version laden („Cache Busting“).
- **fetch braucht einen Server:** Öffnet man `index.html` direkt als Datei (Doppelklick), kann `fetch()` die Schnittstelle nicht erreichen und die Projekte fehlen. Die Seite muss über `localhost:8888` (MAMP) oder online aufgerufen werden.
- **Datenbank nachträglich ändern:** Ist eine Datenbank schon in Gebrauch, ändert man sie mit `ALTER TABLE` in einer eigenen Update-Datei, statt sie neu aufzusetzen. Sonst gehen die vorhandenen Inhalte verloren.
- **Passwörter nie im Klartext:** Weder im Code, noch in der Datenbank, noch im Chat mit der KI. Gespeichert wird nur ein Hash, aus dem sich das Passwort nicht zurückrechnen lässt.
- **inline vs. inline-block:** Links (`<a>`) sind inline-Elemente. Gibt man ihnen einen Innenabstand oben und unten, werden sie grösser gezeichnet, schieben aber nichts weg. Als Button gestaltete Links brauchen deshalb `display: inline-block`.
- **Daten in JavaScript-Attributen:** `htmlspecialchars` schützt HTML, aber nicht JavaScript in Attributen wie `onsubmit`, weil der Browser die Zeichen vorher zurückverwandelt. Daten deshalb über `data-*`-Attribute übergeben.
- **SQL-Injection:** Werte, die von aussen kommen (z. B. aus der Adresse), dürfen nie direkt in einen SQL-Befehl geklebt werden. Prepared Statements mit Platzhaltern (`?`) trennen Befehl und Daten sauber.
- **Elemente mittig ausrichten:** `space-between` zentriert das mittlere Element nur, wenn die äusseren gleich breit sind. Ein Grid mit `1fr auto 1fr` zentriert zuverlässig.

## Ressourcen und Hilfsmittel

- Schriften: Archivo und DM Sans (Google Fonts)
- Design-Entwurf: Figma Make
- KI-Unterstützung: Claude (Anthropic) für Code und Erklärungen
