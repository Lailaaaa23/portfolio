<?php
/*
 * CMS: PROJEKT BEARBEITEN ODER NEU ANLEGEN
 *
 *   projekt-bearbeiten.php        → leeres Formular für ein neues Projekt
 *   projekt-bearbeiten.php?id=3   → Formular mit den Daten von Projekt 3
 *
 * Ablauf:
 * 1. Beim ersten Aufruf (GET) wird das Formular angezeigt.
 * 2. Beim Absenden (POST) werden die Eingaben geprüft.
 *    - Fehler: Formular nochmals zeigen, mit Hinweisen und den eingetippten Werten
 *    - Alles gut: speichern und zurück zur Übersicht
 */

require __DIR__ . '/../includes/admin.php';
require __DIR__ . '/../includes/bild-upload.php';
loginErforderlich();

$db = verbindeDatenbank();

// ---------- Neues Projekt oder bestehendes? ----------
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$istNeu = ($id === null);

if ($istNeu) {
    // Leere Vorlage für ein neues Projekt
    $projekt = [
        'titel' => '', 'kategorie' => '', 'rolle' => '', 'rahmen' => '',
        'beschreibung' => '', 'bild' => '', 'video' => '', 'link' => '', 'sichtbar' => 1,
    ];
} else {
    $abfrage = $db->prepare('SELECT * FROM projekte WHERE id = ?');
    $abfrage->execute([$id]);
    $projekt = $abfrage->fetch();

    if (!$projekt) {
        meldungSetzen('Dieses Projekt gibt es nicht.');
        header('Location: index.php');
        exit;
    }
}

// Fehlermeldungen pro Feld, z. B. $fehler['titel'] = 'Bitte einen Titel eingeben.'
$fehler = [];


// ---------- Formular wurde abgeschickt ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pruefeCsrf();

    // Eingaben übernehmen. trim() entfernt Leerzeichen am Anfang und Ende.
    $projekt['titel']        = trim($_POST['titel'] ?? '');
    $projekt['kategorie']    = trim($_POST['kategorie'] ?? '');
    $projekt['rolle']        = trim($_POST['rolle'] ?? '');
    $projekt['rahmen']       = trim($_POST['rahmen'] ?? '');
    $projekt['beschreibung'] = trim($_POST['beschreibung'] ?? '');
    $projekt['video']        = trim($_POST['video'] ?? '');
    $projekt['link']         = trim($_POST['link'] ?? '');
    // Eine Checkbox wird nur mitgeschickt, wenn sie angehakt ist
    $projekt['sichtbar']     = isset($_POST['sichtbar']) ? 1 : 0;

    // ----- Pflichtfelder und Längen prüfen -----
    // [Feldname, Bezeichnung, höchstens so viele Zeichen (passend zur Datenbank)]
    $pflichtfelder = [
        ['titel',        'einen Titel',       100],
        ['kategorie',    'eine Kategorie',     50],
        ['rolle',        'deine Rolle',       150],
        ['rahmen',       'den Rahmen',        150],
        ['beschreibung', 'eine Beschreibung', 5000],
    ];
    foreach ($pflichtfelder as [$feld, $bezeichnung, $maximum]) {
        if ($projekt[$feld] === '') {
            $fehler[$feld] = "Bitte $bezeichnung eingeben.";
        } elseif (mb_strlen($projekt[$feld]) > $maximum) {
            $fehler[$feld] = "Höchstens $maximum Zeichen.";
        }
    }

    // ----- Links prüfen (optional, aber wenn, dann gültig) -----
    foreach (['video', 'link'] as $feld) {
        $wert = $projekt[$feld];
        if ($wert === '') {
            continue;
        }
        if (!filter_var($wert, FILTER_VALIDATE_URL) || !preg_match('#^https?://#', $wert)) {
            $fehler[$feld] = 'Bitte eine vollständige Adresse eingeben, die mit https:// beginnt.';
        } elseif (mb_strlen($wert) > 255) {
            $fehler[$feld] = 'Die Adresse ist zu lang (höchstens 255 Zeichen).';
        }
    }
    // Video: nur YouTube und Vimeo werden auf der Webseite als Player angezeigt
    if (!isset($fehler['video']) && $projekt['video'] !== ''
        && !preg_match('#(youtube\.com|youtu\.be|vimeo\.com)#', $projekt['video'])) {
        $fehler['video'] = 'Als Video-Link gehen YouTube und Vimeo. Andere Seiten bitte unter „Externer Link“ eintragen.';
    }

    // ----- Bild: bei neuen Projekten Pflicht, beim Bearbeiten optional -----
    $bildHochgeladen = isset($_FILES['bild']) && $_FILES['bild']['error'] !== UPLOAD_ERR_NO_FILE;

    if (!$bildHochgeladen && $istNeu) {
        $fehler['bild'] = 'Bitte ein Vorschaubild auswählen.';
    }

    // Das Bild erst speichern, wenn alle anderen Felder stimmen.
    // Sonst würden bei jedem Fehlversuch Bilder im Ordner liegen bleiben.
    $altesBild = $projekt['bild'];
    if ($bildHochgeladen && !$fehler) {
        try {
            $projekt['bild'] = speichereBild($_FILES['bild'], 'projekte');
        } catch (RuntimeException $problem) {
            $fehler['bild'] = $problem->getMessage();
        }
    }

    // ----- Speichern, wenn es keine Fehler gibt -----
    if (!$fehler) {
        // Leere optionale Felder als NULL ("nichts") speichern statt als leeren Text
        $video = $projekt['video'] !== '' ? $projekt['video'] : null;
        $link  = $projekt['link'] !== '' ? $projekt['link'] : null;

        if ($istNeu) {
            // Neues Projekt kommt ans Ende des Karussells
            $letzter = (int) $db->query('SELECT COALESCE(MAX(reihenfolge), 0) FROM projekte')->fetchColumn();

            $abfrage = $db->prepare(
                'INSERT INTO projekte
                   (titel, kategorie, rolle, rahmen, beschreibung, bild, video, link, sichtbar, reihenfolge)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $abfrage->execute([
                $projekt['titel'], $projekt['kategorie'], $projekt['rolle'], $projekt['rahmen'],
                $projekt['beschreibung'], $projekt['bild'], $video, $link, $projekt['sichtbar'], $letzter + 1,
            ]);
            meldungSetzen("„{$projekt['titel']}“ wurde angelegt.");

        } else {
            $abfrage = $db->prepare(
                'UPDATE projekte SET
                   titel = ?, kategorie = ?, rolle = ?, rahmen = ?, beschreibung = ?,
                   bild = ?, video = ?, link = ?, sichtbar = ?
                 WHERE id = ?'
            );
            $abfrage->execute([
                $projekt['titel'], $projekt['kategorie'], $projekt['rolle'], $projekt['rahmen'],
                $projekt['beschreibung'], $projekt['bild'], $video, $link, $projekt['sichtbar'], $id,
            ]);

            // Wurde das Bild ersetzt, das alte hochgeladene Bild löschen
            if ($projekt['bild'] !== $altesBild) {
                loescheHochgeladenesBild($altesBild);
            }
            meldungSetzen("Änderungen an „{$projekt['titel']}“ gespeichert.");
        }

        header('Location: index.php');
        exit;
    }
}


// ---------- Vorschläge für das Feld "Kategorie" ----------
// Alle bisher verwendeten Kategorien, damit man nicht jedes Mal neu tippen muss
$kategorien = $db->query('SELECT DISTINCT kategorie FROM projekte ORDER BY kategorie')->fetchAll(PDO::FETCH_COLUMN);


// ---------- Weitere Bilder (BTS) dieses Projekts ----------
// Nur bei bestehenden Projekten: Ein neues Projekt hat noch keine id,
// an die man Bilder hängen könnte.
$bilder = [];
if (!$istNeu) {
    $abfrage = $db->prepare(
        'SELECT id, pfad, beschreibung FROM projekt_bilder
         WHERE projekt_id = ? ORDER BY reihenfolge, id'
    );
    $abfrage->execute([$id]);
    $bilder = $abfrage->fetchAll();
}


// Kleine Hilfe: zeigt die Fehlermeldung eines Feldes an (falls es eine gibt)
function feldFehler(array $fehler, string $feld): string
{
    return isset($fehler[$feld])
        ? '<p class="field-error" id="fehler-' . $feld . '">' . e($fehler[$feld]) . '</p>'
        : '';
}

$seitentitel = $istNeu ? 'Neues Projekt' : $projekt['titel'] . ' bearbeiten';
require __DIR__ . '/../includes/admin-kopf.php';
?>

    <a href="index.php" class="admin-back-link">&larr; Alle Projekte</a>

    <h1 class="admin-title">
      <?= $istNeu ? 'Neues Projekt' : e($projekt['titel']) ?><span class="dot-accent">.</span>
    </h1>

    <?php if ($fehler): ?>
      <p class="admin-error" role="alert">Bitte die markierten Felder korrigieren.</p>
    <?php endif; ?>

    <!-- enctype="multipart/form-data" braucht es, damit das Formular Dateien mitschicken kann -->
    <form method="post" enctype="multipart/form-data" class="admin-form project-form" novalidate>
      <?= csrfFeld() ?>

      <div class="form-columns">

        <!-- ===== Linke Spalte: Texte ===== -->
        <div class="form-column">

          <label for="titel">Titel *</label>
          <input type="text" id="titel" name="titel" value="<?= e($projekt['titel']) ?>" maxlength="100" required>
          <?= feldFehler($fehler, 'titel') ?>

          <label for="kategorie">Kategorie *</label>
          <!-- list="..." verbindet das Feld mit den Vorschlägen in der <datalist> -->
          <input type="text" id="kategorie" name="kategorie" value="<?= e($projekt['kategorie']) ?>" maxlength="50" list="kategorie-vorschlaege" required>
          <datalist id="kategorie-vorschlaege">
            <?php foreach ($kategorien as $kategorie): ?>
              <option value="<?= e($kategorie) ?>">
            <?php endforeach; ?>
          </datalist>
          <?= feldFehler($fehler, 'kategorie') ?>

          <label for="rolle">Meine Rolle *</label>
          <input type="text" id="rolle" name="rolle" value="<?= e($projekt['rolle']) ?>" maxlength="150" required>
          <p class="field-hint">Kurz, für die Karte. Z. B. „Konzept · Moderation“</p>
          <?= feldFehler($fehler, 'rolle') ?>

          <label for="rahmen">Rahmen *</label>
          <input type="text" id="rahmen" name="rahmen" value="<?= e($projekt['rahmen']) ?>" maxlength="150" required>
          <p class="field-hint">Modul, Auftraggeberin oder Ort. Z. B. „Modul Digezz · FHGR“</p>
          <?= feldFehler($fehler, 'rahmen') ?>

          <label for="beschreibung">Beschreibung *</label>
          <textarea id="beschreibung" name="beschreibung" rows="8" required><?= e($projekt['beschreibung']) ?></textarea>
          <p class="field-hint">Erscheint auf der Detailseite des Projekts.</p>
          <?= feldFehler($fehler, 'beschreibung') ?>

        </div>

        <!-- ===== Rechte Spalte: Bild, Links, Sichtbarkeit ===== -->
        <div class="form-column">

          <label for="bild">Vorschaubild<?= $istNeu ? ' *' : '' ?></label>
          <?php if ($projekt['bild'] && !isset($fehler['bild'])): ?>
            <img src="../<?= e($projekt['bild']) ?>" alt="Aktuelles Vorschaubild" class="form-preview">
            <p class="field-hint">Nur ein neues Bild auswählen, wenn du das aktuelle ersetzen willst.</p>
          <?php endif; ?>
          <input type="file" id="bild" name="bild" accept="image/jpeg,image/png,image/webp">
          <p class="field-hint">JPG, PNG oder WebP, höchstens 15 MB. Am besten im Querformat 3:2. Grosse Bilder werden automatisch auf 1600 px Breite verkleinert.</p>
          <?= feldFehler($fehler, 'bild') ?>

          <label for="video">Video-Link</label>
          <input type="url" id="video" name="video" value="<?= e($projekt['video']) ?>" placeholder="https://vimeo.com/123456789">
          <p class="field-hint">YouTube oder Vimeo. Wird auf der Detailseite statt des Bildes abgespielt.</p>
          <?= feldFehler($fehler, 'video') ?>

          <label for="link">Externer Link</label>
          <input type="url" id="link" name="link" value="<?= e($projekt['link']) ?>" placeholder="https://open.spotify.com/…">
          <p class="field-hint">Z. B. Spotify oder die Sendung auf tvsuedostschweiz.ch. Erscheint als Button „Projekt ansehen“.</p>
          <?= feldFehler($fehler, 'link') ?>

          <label class="form-checkbox">
            <input type="checkbox" name="sichtbar" value="1" <?= $projekt['sichtbar'] ? 'checked' : '' ?>>
            Auf der Webseite anzeigen
          </label>

        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="admin-button"><?= $istNeu ? 'Projekt anlegen' : 'Änderungen speichern' ?></button>
        <a href="index.php" class="admin-back-link">Abbrechen</a>
      </div>
    </form>

    <!-- ===================== WEITERE BILDER (BTS) =====================
         Eigener Abschnitt mit eigenen Formularen. Formulare dürfen nicht
         ineinander stecken, darum steht er nach dem Projekt-Formular. -->
    <section class="bts-section" id="bilder">
      <h2 class="admin-subtitle">Bilder &amp; Videos<span class="dot-accent">.</span></h2>

      <?php if ($istNeu): ?>
        <p class="admin-hint">Speichere zuerst das Projekt. Danach kannst du hier weitere Bilder hinzufügen.</p>
      <?php else: ?>
        <p class="admin-hint">Bilder und kurze Videos (MP4) erscheinen auf der Detailseite unter „Behind the scenes“. Die Bildunterschrift ist auch der Alt-Text für Screenreader.</p>

        <!-- Hochladen: multiple erlaubt mehrere Dateien auf einmal -->
        <form method="post" action="bilder-aktion.php" enctype="multipart/form-data" class="bts-upload">
          <?= csrfFeld() ?>
          <input type="hidden" name="projekt_id" value="<?= $id ?>">
          <input type="hidden" name="aktion" value="hochladen">
          <label for="bts-dateien" class="visually-hidden">Bilder oder Videos auswählen</label>
          <input type="file" id="bts-dateien" name="bilder[]" accept="image/jpeg,image/png,image/webp,video/mp4" multiple required>
          <button type="submit" class="admin-small-button">Hochladen</button>
        </form>
        <p class="field-hint">Mehrere Dateien auf einmal möglich (cmd gedrückt halten beim Auswählen). Bilder: JPG, PNG oder WebP, je höchstens 15 MB. Videos: MP4, je höchstens 40 MB.</p>

        <?php if (!$bilder): ?>
          <p class="bts-empty">Noch keine weiteren Bilder oder Videos.</p>
        <?php else: ?>
          <ol class="bts-grid">
            <?php foreach ($bilder as $nummer => $bild): ?>
              <?php $istVideo = str_ends_with($bild['pfad'], '.mp4'); ?>
              <li class="bts-tile">
                <div class="bts-media">
                  <?php if ($istVideo): ?>
                    <!-- preload="metadata": nur das erste Bild und die Länge laden, nicht das ganze Video -->
                    <video src="../<?= e($bild['pfad']) ?>" preload="metadata" muted controls playsinline></video>
                    <span class="admin-badge">Video</span>
                  <?php else: ?>
                    <img src="../<?= e($bild['pfad']) ?>" alt="">
                  <?php endif; ?>
                </div>

                <!-- Bildunterschrift bearbeiten -->
                <form method="post" action="bilder-aktion.php" class="bts-caption">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="projekt_id" value="<?= $id ?>">
                  <input type="hidden" name="bild_id" value="<?= $bild['id'] ?>">
                  <input type="hidden" name="aktion" value="beschreibung">
                  <label for="beschreibung-<?= $bild['id'] ?>" class="visually-hidden">Bildunterschrift</label>
                  <input type="text" id="beschreibung-<?= $bild['id'] ?>" name="beschreibung"
                         value="<?= e($bild['beschreibung']) ?>" maxlength="255"
                         placeholder="Bildunterschrift, z. B. „Dreh in Berlin“"
                         class="<?= $bild['beschreibung'] === '' ? 'is-missing' : '' ?>">
                  <button type="submit" class="admin-small-button">Speichern</button>
                </form>

                <div class="admin-tile-row">
                  <form method="post" action="bilder-aktion.php">
                    <?= csrfFeld() ?>
                    <input type="hidden" name="projekt_id" value="<?= $id ?>">
                    <input type="hidden" name="bild_id" value="<?= $bild['id'] ?>">
                    <input type="hidden" name="aktion" value="hoch">
                    <button type="submit" class="admin-icon-button" aria-label="Eintrag <?= $nummer + 1 ?> nach vorne"
                      <?= $nummer === 0 ? 'disabled' : '' ?>>&larr;</button>
                  </form>

                  <form method="post" action="bilder-aktion.php">
                    <?= csrfFeld() ?>
                    <input type="hidden" name="projekt_id" value="<?= $id ?>">
                    <input type="hidden" name="bild_id" value="<?= $bild['id'] ?>">
                    <input type="hidden" name="aktion" value="runter">
                    <button type="submit" class="admin-icon-button" aria-label="Eintrag <?= $nummer + 1 ?> nach hinten"
                      <?= $nummer === count($bilder) - 1 ? 'disabled' : '' ?>>&rarr;</button>
                  </form>

                  <form method="post" action="bilder-aktion.php" class="admin-push-right"
                        onsubmit="return confirm('Wirklich löschen?');">
                    <?= csrfFeld() ?>
                    <input type="hidden" name="projekt_id" value="<?= $id ?>">
                    <input type="hidden" name="bild_id" value="<?= $bild['id'] ?>">
                    <input type="hidden" name="aktion" value="loeschen">
                    <button type="submit" class="admin-small-button is-danger">Löschen</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      <?php endif; ?>
    </section>

<?php require __DIR__ . '/../includes/admin-fuss.php'; ?>
