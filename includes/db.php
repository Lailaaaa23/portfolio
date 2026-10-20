<?php
/*
 * VERBINDUNG ZUR DATENBANK
 *
 * Diese Datei wird von allen PHP-Dateien eingebunden, die mit der
 * Datenbank arbeiten (Schnittstelle in api/, später das CMS in admin/).
 * So steht der Code für die Verbindung nur an einer einzigen Stelle.
 *
 * Verwendung in einer anderen Datei:
 *   require __DIR__ . '/../includes/db.php';
 *   $db = verbindeDatenbank();
 */

function verbindeDatenbank(): PDO
{
    // Zugangsdaten aus config.php holen (liegt eine Ebene höher, nicht auf GitHub)
    $config = require __DIR__ . '/../config.php';

    // DSN = "Data Source Name": sagt PDO, welche Art Datenbank wo liegt.
    // charset=utf8mb4 sorgt dafür, dass Umlaute richtig ankommen.
    $dsn = 'mysql:host=' . $config['db_host']
         . ';dbname=' . $config['db_name']
         . ';charset=utf8mb4';

    // PDO ist die in PHP eingebaute Art, mit Datenbanken zu sprechen.
    return new PDO($dsn, $config['db_user'], $config['db_pass'], [
        // Bei einem Fehler eine Exception werfen, statt still weiterzumachen
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Ergebnisse als Array mit Spaltennamen liefern, z. B. $zeile['titel']
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Echte Prepared Statements verwenden (Schutz vor SQL-Injection)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}
