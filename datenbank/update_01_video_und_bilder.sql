-- =========================================================
-- UPDATE 01: Video und weitere Bilder pro Projekt
--
-- Ergänzt die bestehende Datenbank (portfolio.sql muss schon
-- importiert sein). Nur EINMAL ausführen.
-- =========================================================


-- ---------- 1. Neues Feld für ein Video ----------
-- Hier kommt der normale Link zu YouTube oder Vimeo hinein,
-- z. B. https://vimeo.com/123456789. Darf leer sein (NULL),
-- dann zeigt die Detailseite stattdessen das Vorschaubild.
ALTER TABLE projekte
  ADD COLUMN video VARCHAR(255) NULL AFTER bild;


-- ---------- 2. Neue Tabelle für weitere Bilder ----------
-- Ein Projekt kann beliebig viele Bilder haben (1:n-Beziehung).
-- Jede Zeile ist ein Bild und weiss über projekt_id, zu welchem
-- Projekt es gehört.
CREATE TABLE projekt_bilder (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  projekt_id    INT UNSIGNED NOT NULL,                 -- verweist auf projekte.id
  pfad          VARCHAR(255) NOT NULL,                 -- z. B. "uploads/berlin-bts-1.jpg"
  beschreibung  VARCHAR(255) NOT NULL DEFAULT '',      -- Bildunterschrift, dient auch als Alt-Text
  reihenfolge   INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  -- Fremdschlüssel: Die Datenbank prüft, dass es das Projekt wirklich gibt.
  -- ON DELETE CASCADE: Wird ein Projekt gelöscht, verschwinden seine
  -- Bilder-Einträge automatisch mit.
  CONSTRAINT fk_bilder_projekt
    FOREIGN KEY (projekt_id) REFERENCES projekte (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
