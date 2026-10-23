<?php
/*
 * CMS: AKTIONEN FÜR EIN PROJEKT
 *
 * Wird von den Knöpfen in der Projekt-Übersicht (index.php) aufgerufen.
 * Zeigt selbst nichts an: führt die Aktion aus und leitet zurück.
 *
 * Mögliche Aktionen (im Formularfeld "aktion"):
 *   sichtbar  – Projekt auf der Webseite zeigen bzw. verstecken
 *   hoch      – im Karussell eine Position nach vorne
 *   runter    – im Karussell eine Position nach hinten
 *   loeschen  – Projekt endgültig löschen
 */

require __DIR__ . '/../includes/admin.php';
require __DIR__ . '/../includes/bild-upload.php';
loginErforderlich();

// Aktionen, die etwas verändern, nur per Formular (POST) und mit gültigem Token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
pruefeCsrf();

$db     = verbindeDatenbank();
$aktion = $_POST['aktion'] ?? '';
$id     = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

// Gibt es das Projekt überhaupt?
$abfrage = $db->prepare('SELECT id, titel, bild, sichtbar, reihenfolge FROM projekte WHERE id = ?');
$abfrage->execute([$id]);
$projekt = $abfrage->fetch();

if (!$projekt) {
    meldungSetzen('Dieses Projekt gibt es nicht.');
    header('Location: index.php');
    exit;
}


// ---------- Zeigen / Verstecken ----------
if ($aktion === 'sichtbar') {
    // 1 wird zu 0 und 0 wird zu 1
    $neu = $projekt['sichtbar'] ? 0 : 1;
    $db->prepare('UPDATE projekte SET sichtbar = ? WHERE id = ?')->execute([$neu, $id]);

    meldungSetzen($neu
        ? "„{$projekt['titel']}“ ist jetzt auf der Webseite sichtbar."
        : "„{$projekt['titel']}“ ist jetzt versteckt.");
}


// ---------- Reihenfolge ändern ----------
elseif ($aktion === 'hoch' || $aktion === 'runter') {
    // Den direkten Nachbarn suchen: bei "hoch" das Projekt davor, bei "runter" das danach
    if ($aktion === 'hoch') {
        $nachbarAbfrage = $db->prepare(
            'SELECT id, reihenfolge FROM projekte
             WHERE reihenfolge < ? ORDER BY reihenfolge DESC LIMIT 1'
        );
    } else {
        $nachbarAbfrage = $db->prepare(
            'SELECT id, reihenfolge FROM projekte
             WHERE reihenfolge > ? ORDER BY reihenfolge ASC LIMIT 1'
        );
    }
    $nachbarAbfrage->execute([$projekt['reihenfolge']]);
    $nachbar = $nachbarAbfrage->fetch();

    // Ganz vorne bzw. ganz hinten gibt es keinen Nachbarn: dann nichts tun
    if ($nachbar) {
        // Die beiden tauschen ihre Plätze.
        // Transaktion: Entweder klappen beide Änderungen oder keine.
        // So können nie zwei Projekte auf demselben Platz landen.
        $db->beginTransaction();
        $tauschen = $db->prepare('UPDATE projekte SET reihenfolge = ? WHERE id = ?');
        $tauschen->execute([$nachbar['reihenfolge'], $projekt['id']]);
        $tauschen->execute([$projekt['reihenfolge'], $nachbar['id']]);
        $db->commit();
    }
}


// ---------- Löschen ----------
elseif ($aktion === 'loeschen') {
    // Vor dem Löschen merken, welche weiteren Bilder (BTS) zum Projekt gehören
    $bilderAbfrage = $db->prepare('SELECT pfad FROM projekt_bilder WHERE projekt_id = ?');
    $bilderAbfrage->execute([$id]);
    $btsBilder = $bilderAbfrage->fetchAll(PDO::FETCH_COLUMN);

    // Dank ON DELETE CASCADE (siehe datenbank/update_01) werden die
    // Einträge der weiteren Bilder in projekt_bilder automatisch mitgelöscht.
    // Die Bild-DATEIEN kennt die Datenbank aber nicht, die löschen wir selbst.
    $db->prepare('DELETE FROM projekte WHERE id = ?')->execute([$id]);

    // Hochgeladenes Vorschaubild und BTS-Bilder als Dateien löschen
    // (Bilder in images/ bleiben stehen, siehe bild-upload.php)
    loescheHochgeladenesBild($projekt['bild']);
    foreach ($btsBilder as $pfad) {
        loescheHochgeladenesBild($pfad);
    }

    meldungSetzen("„{$projekt['titel']}“ wurde gelöscht.");
}


header('Location: index.php');
exit;
