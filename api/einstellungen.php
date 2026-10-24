<?php
/*
 * SCHNITTSTELLE: EINSTELLUNGEN
 *
 * Liefert die Einstellungen der Webseite als JSON-Objekt "Name → Wert".
 *   api/einstellungen.php  → {"status": "Available for projects"}
 *
 * Aus der Tabelle mit Zeilen wird ein Objekt mit Schlüsseln, weil man so
 * in JavaScript bequem darauf zugreifen kann: einstellungen.status
 */

require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = verbindeDatenbank();

    // FETCH_KEY_PAIR macht aus zwei Spalten direkt ein Array "name => wert"
    $einstellungen = $db->query('SELECT name, wert FROM einstellungen')->fetchAll(PDO::FETCH_KEY_PAIR);

    // (object) sorgt dafür, dass auch ohne Einträge {} statt [] ausgegeben wird
    echo json_encode((object) $einstellungen, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $fehler) {
    http_response_code(500);
    echo json_encode(['fehler' => 'Datenbank nicht erreichbar'], JSON_UNESCAPED_UNICODE);
}
