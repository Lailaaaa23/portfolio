<?php
/*
 * SCHNITTSTELLE: SKILLS
 *
 * Liefert die Skills für "Über mich" als JSON, in der Reihenfolge aus dem CMS.
 *   api/skills.php  → [{"name": "Video Editing", "werkzeug": "Premiere Pro"}, …]
 */

require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = verbindeDatenbank();
    $skills = $db->query(
        'SELECT name, werkzeug FROM skills ORDER BY reihenfolge, id'
    )->fetchAll();

    echo json_encode($skills, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $fehler) {
    http_response_code(500);
    echo json_encode(['fehler' => 'Datenbank nicht erreichbar'], JSON_UNESCAPED_UNICODE);
}
