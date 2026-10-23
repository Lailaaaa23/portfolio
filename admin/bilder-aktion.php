<?php
/*
 * CMS: AKTIONEN FÜR DIE WEITEREN BILDER UND VIDEOS (BTS) EINES PROJEKTS
 *
 * Wird vom Abschnitt "Weitere Bilder" in projekt-bearbeiten.php aufgerufen.
 * Zeigt selbst nichts an: führt die Aktion aus und leitet zurück.
 *
 * Mögliche Aktionen (im Formularfeld "aktion"):
 *   hochladen     – ein oder mehrere neue Bilder oder MP4-Videos hinzufügen
 *   beschreibung  – Bildunterschrift eines Bildes ändern
 *   hoch / runter – Bild eine Position nach vorne / hinten
 *   loeschen      – Bild entfernen (Eintrag und Datei)
 */

require __DIR__ . '/../includes/admin.php';
require __DIR__ . '/../includes/bild-upload.php';
loginErforderlich();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Sonderfall: Ist die Anfrage grösser als der Server erlaubt (post_max_size),
// verwirft PHP ALLES, auch das Token. Dann lieber eine klare Meldung zeigen.
if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    meldungSetzen('Die Bilder sind zusammen zu gross. Bitte weniger Bilder auf einmal hochladen.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

pruefeCsrf();

$db        = verbindeDatenbank();
$aktion    = $_POST['aktion'] ?? '';
$projektId = filter_var($_POST['projekt_id'] ?? '', FILTER_VALIDATE_INT);

// Gibt es das Projekt?
$abfrage = $db->prepare('SELECT id FROM projekte WHERE id = ?');
$abfrage->execute([$projektId]);
if (!$abfrage->fetch()) {
    meldungSetzen('Dieses Projekt gibt es nicht.');
    header('Location: index.php');
    exit;
}

// Nach jeder Aktion zurück zum Abschnitt "Weitere Bilder" dieses Projekts
$zurueck = 'projekt-bearbeiten.php?id=' . $projektId . '#bilder';


// ---------- Neue Bilder hochladen ----------
if ($aktion === 'hochladen') {
    $dateien = $_FILES['bilder'] ?? null;

    // Bei <input type="file" multiple> ordnet PHP die Daten ungewohnt an:
    // nicht "Bild 1 mit Name, Grösse …", sondern "alle Namen, alle Grössen …".
    // Hier werden sie wieder pro Bild zusammengesetzt.
    $einzelne = [];
    if ($dateien && is_array($dateien['name'])) {
        foreach ($dateien['name'] as $i => $name) {
            if ($dateien['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;   // leeres Feld überspringen
            }
            $einzelne[] = [
                'name'     => $name,
                'tmp_name' => $dateien['tmp_name'][$i],
                'error'    => $dateien['error'][$i],
                'size'     => $dateien['size'][$i],
            ];
        }
    }

    if (!$einzelne) {
        meldungSetzen('Bitte mindestens ein Bild oder Video auswählen.');
        header('Location: ' . $zurueck);
        exit;
    }

    // Die neuen Bilder kommen hinter die bestehenden
    $abfrage = $db->prepare('SELECT COALESCE(MAX(reihenfolge), 0) FROM projekt_bilder WHERE projekt_id = ?');
    $abfrage->execute([$projektId]);
    $reihenfolge = (int) $abfrage->fetchColumn();

    $einfuegen = $db->prepare(
        'INSERT INTO projekt_bilder (projekt_id, pfad, beschreibung, reihenfolge) VALUES (?, ?, ?, ?)'
    );

    $gespeichert = 0;
    $probleme = [];

    // Jede Datei einzeln: Klappt eine nicht, werden die anderen trotzdem gespeichert
    foreach ($einzelne as $datei) {
        try {
            // Bild oder Video? Entscheidet, welche Funktion die Datei prüft.
            // Ein Video erkennt man am Inhalt (finfo) oder an der Endung .mp4.
            // Die Endung dient nur dazu, bei einer kaputten .mp4 die passende
            // Fehlermeldung zu zeigen. Ob die Datei gültig ist, prüft speichereVideo().
            $istVideo = preg_match('/\.mp4$/i', $datei['name'])
                || ($datei['error'] === UPLOAD_ERR_OK
                    && str_starts_with((new finfo(FILEINFO_MIME_TYPE))->file($datei['tmp_name']), 'video/'));
            $pfad = $istVideo ? speichereVideo($datei, 'bts') : speichereBild($datei, 'bts');
            $reihenfolge++;
            $einfuegen->execute([$projektId, $pfad, '', $reihenfolge]);
            $gespeichert++;
        } catch (RuntimeException $problem) {
            $probleme[] = $datei['name'] . ': ' . $problem->getMessage();
        }
    }

    $text = $gespeichert === 1 ? '1 Datei hinzugefügt.' : "$gespeichert Dateien hinzugefügt.";
    if ($gespeichert > 0) {
        $text .= ' Denk an die Bildunterschriften.';
    }
    if ($probleme) {
        $text .= ' Nicht gespeichert: ' . implode(' / ', $probleme);
    }
    meldungSetzen($text);
    header('Location: ' . $zurueck);
    exit;
}


// ---------- Alle übrigen Aktionen betreffen EIN bestehendes Bild ----------
$bildId = filter_var($_POST['bild_id'] ?? '', FILTER_VALIDATE_INT);

// Das Bild muss zu DIESEM Projekt gehören. So kann niemand über eine
// veränderte Nummer ein Bild eines anderen Projekts bearbeiten.
$abfrage = $db->prepare('SELECT id, pfad, reihenfolge FROM projekt_bilder WHERE id = ? AND projekt_id = ?');
$abfrage->execute([$bildId, $projektId]);
$bild = $abfrage->fetch();

if (!$bild) {
    meldungSetzen('Dieses Bild gibt es nicht.');
    header('Location: ' . $zurueck);
    exit;
}


// ---------- Bildunterschrift speichern ----------
if ($aktion === 'beschreibung') {
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    if (mb_strlen($beschreibung) > 255) {
        meldungSetzen('Die Bildunterschrift ist zu lang (höchstens 255 Zeichen).');
    } else {
        $db->prepare('UPDATE projekt_bilder SET beschreibung = ? WHERE id = ?')->execute([$beschreibung, $bildId]);
        meldungSetzen('Bildunterschrift gespeichert.');
    }
}


// ---------- Reihenfolge ändern (gleiches Prinzip wie bei den Projekten) ----------
elseif ($aktion === 'hoch' || $aktion === 'runter') {
    $nachbarAbfrage = $db->prepare($aktion === 'hoch'
        ? 'SELECT id, reihenfolge FROM projekt_bilder WHERE projekt_id = ? AND reihenfolge < ? ORDER BY reihenfolge DESC LIMIT 1'
        : 'SELECT id, reihenfolge FROM projekt_bilder WHERE projekt_id = ? AND reihenfolge > ? ORDER BY reihenfolge ASC LIMIT 1'
    );
    $nachbarAbfrage->execute([$projektId, $bild['reihenfolge']]);
    $nachbar = $nachbarAbfrage->fetch();

    if ($nachbar) {
        $db->beginTransaction();
        $tauschen = $db->prepare('UPDATE projekt_bilder SET reihenfolge = ? WHERE id = ?');
        $tauschen->execute([$nachbar['reihenfolge'], $bild['id']]);
        $tauschen->execute([$bild['reihenfolge'], $nachbar['id']]);
        $db->commit();
    }
}


// ---------- Bild löschen ----------
elseif ($aktion === 'loeschen') {
    $db->prepare('DELETE FROM projekt_bilder WHERE id = ?')->execute([$bildId]);
    loescheHochgeladenesBild($bild['pfad']);
    meldungSetzen('Bild gelöscht.');
}


header('Location: ' . $zurueck);
exit;
