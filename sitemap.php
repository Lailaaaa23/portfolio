<?php
/*
 * SITEMAP FÜR SUCHMASCHINEN
 *
 * Eine Sitemap ist eine Liste aller Seiten der Webseite im XML-Format.
 * Google liest sie und findet so auch die Projekt-Detailseiten, die
 * sonst nur über JavaScript verlinkt sind.
 * Die Projekte kommen direkt aus der Datenbank: Ein neues Projekt im CMS
 * steht automatisch auch hier, ein verstecktes nicht.
 */

require __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');

// Feste Seiten
$adressen = [
    'https://lailakeller.ch/',
    'https://lailakeller.ch/datenschutz.html',
];

// Alle sichtbaren Projekte
try {
    $db = verbindeDatenbank();
    $ids = $db->query('SELECT id FROM projekte WHERE sichtbar = 1 ORDER BY reihenfolge, id')
              ->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) {
        $adressen[] = 'https://lailakeller.ch/projekt.html?id=' . (int) $id;
    }
} catch (PDOException $fehler) {
    // Datenbank nicht erreichbar: wenigstens die festen Seiten ausgeben
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($adressen as $adresse) {
    // htmlspecialchars, weil in XML z. B. "&" als "&amp;" stehen muss
    echo '  <url><loc>' . htmlspecialchars($adresse, ENT_XML1) . '</loc></url>' . "\n";
}
echo '</urlset>' . "\n";
