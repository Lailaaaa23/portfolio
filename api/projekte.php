<?php
/*
 * SCHNITTSTELLE: PROJEKTE
 *
 * Liefert die Projekte aus der Datenbank als JSON.
 * JavaScript im Frontend holt sie sich mit fetch() ab.
 *
 *   api/projekte.php        → alle sichtbaren Projekte (für das Karussell)
 *   api/projekte.php?id=3   → nur das Projekt mit der Nummer 3 (für die Detailseite)
 */

require __DIR__ . '/../includes/db.php';

// Dem Browser sagen: Die Antwort ist JSON, nicht HTML
header('Content-Type: application/json; charset=utf-8');

try {
    $db = verbindeDatenbank();

    if (isset($_GET['id'])) {
        // ---------- Ein einzelnes Projekt ----------

        // Die id muss eine ganze Zahl sein. Alles andere (z. B. "abc") wird abgelehnt.
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);   // 400 = "Fehlerhafte Anfrage"
            echo json_encode(['fehler' => 'Ungültige Projekt-ID'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Prepared Statement: Das Fragezeichen ist ein Platzhalter.
        // Die Datenbank setzt die id selbst ein, so kann niemand
        // über die Adresse eigenen SQL-Code einschleusen (SQL-Injection).
        $abfrage = $db->prepare(
            'SELECT id, titel, kategorie, rolle, rahmen, beschreibung, bild, video, link
             FROM projekte
             WHERE id = ? AND sichtbar = 1'
        );
        $abfrage->execute([$id]);
        $projekt = $abfrage->fetch();

        if (!$projekt) {
            http_response_code(404);   // 404 = "Nicht gefunden"
            echo json_encode(['fehler' => 'Projekt nicht gefunden'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Weitere Bilder dieses Projekts aus der zweiten Tabelle holen
        // und als Liste unter "bilder" ans Projekt anhängen
        $bilderAbfrage = $db->prepare(
            'SELECT pfad, beschreibung
             FROM projekt_bilder
             WHERE projekt_id = ?
             ORDER BY reihenfolge, id'
        );
        $bilderAbfrage->execute([$id]);
        $projekt['bilder'] = $bilderAbfrage->fetchAll();

        echo json_encode($projekt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } else {
        // ---------- Alle sichtbaren Projekte ----------
        // Hier gibt es keine Eingabe von aussen, darum reicht query() statt prepare().
        $projekte = $db->query(
            'SELECT id, titel, kategorie, rolle, rahmen, bild
             FROM projekte
             WHERE sichtbar = 1
             ORDER BY reihenfolge, id'
        )->fetchAll();

        echo json_encode($projekte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

} catch (PDOException $fehler) {
    // Etwas mit der Datenbank ging schief (z. B. falsches Passwort).
    // Die genaue Fehlermeldung zeigen wir NICHT an, weil sie Details
    // über den Server verraten könnte.
    http_response_code(500);   // 500 = "Fehler auf dem Server"
    echo json_encode(['fehler' => 'Datenbank nicht erreichbar'], JSON_UNESCAPED_UNICODE);
}
