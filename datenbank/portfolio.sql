-- =========================================================
-- PORTFOLIO – Datenbank
-- Legt die Tabellen an und füllt sie mit den ersten Inhalten.
--
-- So verwendest du die Datei:
-- 1. In phpMyAdmin die (leere) Datenbank efanahiw_portfolio auswählen
-- 2. Reiter "Importieren" → diese Datei auswählen → "Importieren"
--
-- Nur EINMAL auf einer leeren Datenbank ausführen.
-- Gibt es eine Tabelle schon, bricht der Import mit einem Fehler ab,
-- statt etwas zu überschreiben. Das ist Absicht.
-- =========================================================


-- ---------- 1. PROJEKTE ----------
-- Eine Zeile pro Projekt. Daraus entstehen später die Karten im
-- Karussell und die Detailseiten.
CREATE TABLE projekte (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,  -- eindeutige Nummer, zählt automatisch hoch
  titel         VARCHAR(100) NOT NULL,                 -- z. B. "Amygdala"
  kategorie     VARCHAR(50)  NOT NULL,                 -- z. B. "Video & Reportage"
  rolle         VARCHAR(150) NOT NULL,                 -- kurz, für die Karte: "Co-Projektleitung · Host"
  rahmen        VARCHAR(150) NOT NULL,                 -- Modul oder Auftraggeberin
  beschreibung  TEXT         NOT NULL,                 -- ausführlicher Text für die Detailseite
  bild          VARCHAR(255) NOT NULL,                 -- Pfad zum Vorschaubild
  link          VARCHAR(255) NULL,                     -- optional: Link zum Video, zu Spotify …
  reihenfolge   INT          NOT NULL DEFAULT 0,       -- kleinere Zahl = weiter vorne
  sichtbar      TINYINT(1)   NOT NULL DEFAULT 1,       -- 1 = auf der Webseite zeigen, 0 = versteckt
  erstellt_am   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  geaendert_am  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------- 2. SKILLS ----------
-- Die Liste bei "Über mich".
CREATE TABLE skills (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name         VARCHAR(100) NOT NULL,                  -- z. B. "Video Editing"
  werkzeug     VARCHAR(100) NOT NULL,                  -- z. B. "Premiere Pro"
  reihenfolge  INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------- 3. EINSTELLUNGEN ----------
-- Einzelne Werte der Webseite, die man im CMS ändern kann,
-- z. B. der Status oben rechts im Header.
-- Aufbau "Name → Wert": So kommen später neue Einstellungen dazu,
-- ohne dass die Tabelle geändert werden muss.
CREATE TABLE einstellungen (
  name  VARCHAR(50)  NOT NULL,                         -- z. B. "status"
  wert  VARCHAR(255) NOT NULL,                         -- z. B. "Available for projects"
  PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------- 4. BENUTZER ----------
-- Wer sich ins CMS einloggen darf.
-- Das Passwort wird NIE im Klartext gespeichert, sondern nur als
-- "Hash" (eine Art Fingerabdruck), den PHP mit password_hash() erzeugt.
-- Den ersten Benutzer legen wir später mit einem PHP-Skript an.
CREATE TABLE benutzer (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  benutzername   VARCHAR(50)  NOT NULL,
  passwort_hash  VARCHAR(255) NOT NULL,
  erstellt_am    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY (benutzername)                            -- jeder Name darf nur einmal vorkommen
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- STARTINHALTE
-- Die Beschreibungen sind erste Entwürfe aus meinen Stichworten.
-- Sie werden später im CMS überarbeitet.
-- =========================================================

INSERT INTO projekte (titel, kategorie, rolle, rahmen, beschreibung, bild, reihenfolge) VALUES
(
  'Amygdala',
  'Video & Reportage',
  'Co-Projektleitung · Host',
  'Major Journalismus Multimedial · FHGR',
  'Amygdala ist eine Videoreportage-Reihe unserer achtköpfigen Redaktion im Major Journalismus Multimedial. In Zweierteams setzen wir je eine eigene Reportage um. Als Co-Projektleiterin koordiniere ich die Redaktion und stehe zusätzlich als Host und Acting Reporterin vor der Kamera.',
  'images/projekte/amygdala.jpg',
  1
),
(
  'Somedia',
  'TV',
  'Konzept · Moderation',
  'Modul Digezz · TV Südostschweiz',
  'Zusammen mit zwei Studienkolleginnen habe ich Fernsehsendungen für TV Südostschweiz konzipiert. Gezeigt wurden Videos, die im Studium entstanden sind. Ich war für die Absprachen mit Somedia zuständig, habe die Moderationen vorbereitet und die Sendungen moderiert.',
  'images/projekte/somedia.jpg',
  2
),
(
  'Berlin',
  'Video & Reportage',
  'Reporterin · Interviews · Planung',
  'Modul Audiovisuelles Erzählen · FHGR',
  'Eine Videoreportage, gedreht in Berlin. Ich habe Protagonistinnen und Protagonisten gesucht, die Drehs geplant, die Interviews vorbereitet und geführt und stand als Acting Reporterin vor der Kamera. Dazu kam die ganze Organisation rund um die Produktion.',
  'images/projekte/berlin.jpg',
  3
),
(
  'Multitalk',
  'Radio',
  'Moderation · Studiotechnik · Ablauf',
  'Campusradio der FHGR',
  'Multitalk ist das Campusradio der FHGR und wird von Semester zu Semester weitergegeben. Unser Team hat ein eigenes Sendungskonzept entwickelt und umgesetzt. Ich bereite Moderationen und den Sendungsablauf vor und bin im Studio für die gesamte Technik verantwortlich.',
  'images/projekte/multitalk.jpg',
  4
),
(
  'Podcast Chäller',
  'Podcast',
  'Konzept · Aufnahme · Schnitt · Design',
  'Modul Digezz · auf Spotify',
  'Ein eigener Podcast von der Idee bis zur Veröffentlichung: Ich habe das Konzept erstellt, Gesprächspartnerinnen und -partner gesucht, darunter Nationalrätinnen und Nationalräte, die Gespräche aufgenommen und geschnitten. Jingles und Logo habe ich ebenfalls selbst gestaltet. Der Podcast ist auf Spotify veröffentlicht.',
  'images/projekte/podcast-chaeller.jpg',
  5
),
(
  'Nulltoleranz',
  'Design & Kampagne',
  'Design · Wording · Pitch',
  'Kampagne im Auftrag der FHGR',
  'Eine Kampagne gegen Sexismus, Rassismus und Mobbing an der FHGR. Als Fünferteam haben wir wie eine Agentur für die Fachhochschule gearbeitet. Alle waren an allem beteiligt: vom ersten Designentwurf über das Wording bis zum Pitch bei der FH als Kundin.',
  'images/projekte/nulltoleranz.jpg',
  6
);

INSERT INTO skills (name, werkzeug, reihenfolge) VALUES
('Video Editing',    'Premiere Pro', 1),
('Audio',            'Audition',     2),
('Editorial Design', 'InDesign',     3);

INSERT INTO einstellungen (name, wert) VALUES
('status', 'Available for projects');
