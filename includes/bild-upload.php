<?php
/*
 * BILD- UND VIDEO-UPLOAD
 *
 * speichereBild():  nimmt ein hochgeladenes Bild entgegen, prüft es,
 *                   dreht es richtig, verkleinert es und speichert es als JPG.
 * speichereVideo(): nimmt ein MP4-Video entgegen, prüft es und speichert es.
 * Beide speichern im Ordner uploads/ und geben den Pfad zurück.
 *
 * Verwendung:
 *   $pfad = speichereBild($_FILES['bild'], 'projekte');
 *   → z. B. "uploads/projekte/20261023-a1b2c3d4.jpg"
 *
 * Geht etwas schief, werfen die Funktionen eine RuntimeException
 * mit einer verständlichen Fehlermeldung.
 */

// Grösste erlaubte Datei (15 MB). Online kann der Server weniger erlauben.
const BILD_MAX_BYTES = 15 * 1024 * 1024;

// Längste Seite in Pixeln: Querformate werden auf 1600 px Breite,
// Hochformate auf 1600 px Höhe verkleinert
const BILD_MAX_SEITE = 1600;

function speichereBild(array $datei, string $unterordner): string
{
    // ---------- 1. Hat der Upload überhaupt geklappt? ----------
    // PHP meldet in $datei['error'] eine Nummer. UPLOAD_ERR_OK (0) heisst: alles gut.
    if ($datei['error'] === UPLOAD_ERR_INI_SIZE || $datei['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('Das Bild ist zu gross. Erlaubt sind höchstens 15 MB.');
    }
    if ($datei['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Das Bild konnte nicht hochgeladen werden. Bitte nochmals versuchen.');
    }
    if ($datei['size'] > BILD_MAX_BYTES) {
        throw new RuntimeException('Das Bild ist zu gross. Erlaubt sind höchstens 15 MB.');
    }

    // ---------- 2. Ist es wirklich ein Bild? ----------
    // Dem Dateinamen (z. B. "foto.jpg") darf man nicht trauen, er lässt sich
    // beliebig umbenennen. finfo schaut deshalb in die Datei selbst hinein.
    $erlaubt = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png'  => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    $typ = (new finfo(FILEINFO_MIME_TYPE))->file($datei['tmp_name']);
    if (!isset($erlaubt[$typ])) {
        throw new RuntimeException('Bitte ein Bild im Format JPG, PNG oder WebP hochladen.');
    }

    // ---------- 3. Bild öffnen und bei Bedarf verkleinern ----------
    $oeffnen = $erlaubt[$typ];
    $original = @$oeffnen($datei['tmp_name']);
    if (!$original) {
        throw new RuntimeException('Das Bild ist beschädigt und konnte nicht gelesen werden.');
    }

    // Handyfotos richtig herum drehen (siehe Funktion weiter unten)
    if ($typ === 'image/jpeg') {
        $original = richtigDrehen($original, $datei['tmp_name']);
    }

    $breite = imagesx($original);
    $hoehe  = imagesy($original);
    $laengsteSeite = max($breite, $hoehe);

    if ($laengsteSeite > BILD_MAX_SEITE) {
        // Beide Seiten mit demselben Faktor verkleinern, damit nichts verzerrt wird
        $faktor = BILD_MAX_SEITE / $laengsteSeite;
        $bild = imagescale($original, (int) round($breite * $faktor), (int) round($hoehe * $faktor), IMG_BICUBIC);
        imagedestroy($original);
    } else {
        $bild = $original;
    }

    // ---------- 4. Als JPG speichern ----------
    // PNGs können durchsichtige Stellen haben. JPG kennt das nicht,
    // darum kommt das Bild auf einen hellen Hintergrund in der Seitenfarbe.
    $leinwand = imagecreatetruecolor(imagesx($bild), imagesy($bild));
    imagefill($leinwand, 0, 0, imagecolorallocate($leinwand, 246, 246, 242));
    imagecopy($leinwand, $bild, 0, 0, 0, 0, imagesx($bild), imagesy($bild));
    imagedestroy($bild);

    // Ordner anlegen, falls es ihn noch nicht gibt
    $ordner = __DIR__ . '/../uploads/' . $unterordner;
    if (!is_dir($ordner)) {
        mkdir($ordner, 0755, true);
    }

    // Neuer, zufälliger Dateiname: Datum + Zufallszeichen.
    // So überschreiben sich nie zwei Bilder, und niemand kann über den
    // Dateinamen etwas einschmuggeln (z. B. "../../index.php").
    $dateiname = date('Ymd') . '-' . bin2hex(random_bytes(4)) . '.jpg';

    // 82 = JPG-Qualität: kaum sichtbarer Unterschied, deutlich kleinere Datei
    imagejpeg($leinwand, $ordner . '/' . $dateiname, 82);
    imagedestroy($leinwand);

    // Pfad so zurückgeben, wie er in der Datenbank steht (von der Webseite aus gesehen)
    return 'uploads/' . $unterordner . '/' . $dateiname;
}


/*
 * VIDEO SPEICHERN (für die BTS-Galerie)
 *
 * Anders als Bilder werden Videos nicht verkleinert oder umgewandelt.
 * Das wäre auf einem Webserver sehr aufwendig. Deshalb sind nur MP4-Dateien
 * erlaubt, die jeder Browser abspielen kann, und höchstens 40 MB.
 * Tipp: vorher in Premiere oder HandBrake als H.264-MP4 exportieren.
 */
const VIDEO_MAX_BYTES = 40 * 1024 * 1024;

function speichereVideo(array $datei, string $unterordner): string
{
    // Dieselben ersten Prüfungen wie bei Bildern
    if ($datei['error'] === UPLOAD_ERR_INI_SIZE || $datei['error'] === UPLOAD_ERR_FORM_SIZE
        || ($datei['error'] === UPLOAD_ERR_OK && $datei['size'] > VIDEO_MAX_BYTES)) {
        throw new RuntimeException('Das Video ist zu gross. Erlaubt sind höchstens 40 MB.');
    }
    if ($datei['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Das Video konnte nicht hochgeladen werden. Bitte nochmals versuchen.');
    }

    // Auch hier: Inhalt prüfen, nicht den Dateinamen
    $typ = (new finfo(FILEINFO_MIME_TYPE))->file($datei['tmp_name']);
    if ($typ !== 'video/mp4') {
        throw new RuntimeException('Bitte ein Video im Format MP4 hochladen.');
    }

    $ordner = __DIR__ . '/../uploads/' . $unterordner;
    if (!is_dir($ordner)) {
        mkdir($ordner, 0755, true);
    }

    // Neuer, zufälliger Dateiname wie bei den Bildern
    $dateiname = date('Ymd') . '-' . bin2hex(random_bytes(4)) . '.mp4';

    // move_uploaded_file verschiebt die Datei aus dem Zwischenspeicher von PHP
    // an ihren endgültigen Ort. Es funktioniert nur mit echten Uploads.
    if (!move_uploaded_file($datei['tmp_name'], $ordner . '/' . $dateiname)) {
        throw new RuntimeException('Das Video konnte nicht gespeichert werden.');
    }

    return 'uploads/' . $unterordner . '/' . $dateiname;
}


/*
 * HANDYFOTOS RICHTIG HERUM DREHEN
 *
 * Handys speichern ein Foto oft "liegend" und schreiben nur einen Vermerk
 * in die Datei, wie es gedreht werden muss (EXIF-Orientierung, Zahl 1–8).
 * Fotoprogramme und Browser beachten den Vermerk. GD beim Verkleinern nicht,
 * und das gespeicherte JPG enthält den Vermerk danach nicht mehr.
 * Darum wird das Bild hier vorher selbst richtig gedreht.
 *
 * Die 8 Werte: 1 = alles richtig, 3 = auf dem Kopf,
 * 6 = um 90° nach rechts drehen, 8 = um 90° nach links drehen,
 * 2/4/5/7 = dasselbe zusätzlich gespiegelt (z. B. Selfie-Kamera).
 */
function richtigDrehen(GdImage $bild, string $datei): GdImage
{
    // exif_read_data braucht die PHP-Erweiterung "exif". Fehlt sie,
    // bleibt das Bild einfach, wie es ist.
    if (!function_exists('exif_read_data')) {
        return $bild;
    }
    $exif = @exif_read_data($datei);
    $orientierung = (int) ($exif['Orientation'] ?? 1);

    // Zuerst spiegeln (nur bei 2, 4, 5, 7) ...
    if (in_array($orientierung, [2, 4, 5, 7], true)) {
        imageflip($bild, IMG_FLIP_HORIZONTAL);
    }

    // ... dann drehen. imagerotate dreht GEGEN den Uhrzeigersinn,
    // darum heisst "90° nach rechts" hier 270.
    $winkel = match ($orientierung) {
        3, 4    => 180,
        6, 7    => 270,
        5, 8    => 90,
        default => 0,
    };
    if ($winkel !== 0) {
        $gedreht = imagerotate($bild, $winkel, 0);
        imagedestroy($bild);
        $bild = $gedreht;
    }

    return $bild;
}


/*
 * Löscht ein früher hochgeladenes Bild oder Video, z. B. wenn es ersetzt wird.
 * Löscht NUR Dateien im Ordner uploads/. Die Bilder in images/
 * gehören zum Code (GitHub) und bleiben immer stehen.
 */
function loescheHochgeladenesBild(?string $pfad): void
{
    if (!$pfad || !str_starts_with($pfad, 'uploads/') || str_contains($pfad, '..')) {
        return;
    }
    $datei = __DIR__ . '/../' . $pfad;
    if (is_file($datei)) {
        unlink($datei);
    }
}
