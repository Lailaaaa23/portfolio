<?php
/*
 * CMS: ÜBER MICH & STATUS
 *
 * - Status im Header der Webseite ("Available for projects")
 * - Skills im Teil "Über mich": bearbeiten, sortieren, löschen, hinzufügen
 *
 * Alle Formulare auf dieser Seite schicken ihre Daten an diese Seite selbst.
 * Das Feld "aktion" sagt, was zu tun ist. Danach wird auf diese Seite
 * zurückgeleitet, damit ein Neuladen das Formular nicht nochmals abschickt.
 */

require __DIR__ . '/../includes/admin.php';
loginErforderlich();

$db = verbindeDatenbank();


// ---------- Formular abgeschickt: Aktion ausführen ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pruefeCsrf();
    $aktion = $_POST['aktion'] ?? '';

    // ----- Status speichern -----
    if ($aktion === 'status') {
        $status = trim($_POST['status'] ?? '');
        if (mb_strlen($status) > 60) {
            meldungSetzen('Der Status ist zu lang (höchstens 60 Zeichen).');
        } else {
            // "INSERT … ON DUPLICATE KEY UPDATE": Gibt es die Zeile "status" noch nicht,
            // wird sie angelegt, sonst wird ihr Wert geändert. Ein Befehl für beide Fälle.
            $db->prepare(
                'INSERT INTO einstellungen (name, wert) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE wert = VALUES(wert)'
            )->execute(['status', $status]);
            meldungSetzen($status === '' ? 'Status entfernt. Er wird auf der Webseite nicht mehr angezeigt.' : 'Status gespeichert.');
        }
    }

    // ----- Neuen Skill hinzufügen -----
    elseif ($aktion === 'hinzufuegen') {
        $name     = trim($_POST['name'] ?? '');
        $werkzeug = trim($_POST['werkzeug'] ?? '');

        if ($name === '' || $werkzeug === '') {
            meldungSetzen('Bitte Skill und Werkzeug ausfüllen.');
        } elseif (mb_strlen($name) > 100 || mb_strlen($werkzeug) > 100) {
            meldungSetzen('Skill und Werkzeug dürfen je höchstens 100 Zeichen lang sein.');
        } else {
            // Neuer Skill kommt ans Ende der Liste
            $letzter = (int) $db->query('SELECT COALESCE(MAX(reihenfolge), 0) FROM skills')->fetchColumn();
            $db->prepare('INSERT INTO skills (name, werkzeug, reihenfolge) VALUES (?, ?, ?)')
               ->execute([$name, $werkzeug, $letzter + 1]);
            meldungSetzen("„{$name}“ hinzugefügt.");
        }
    }

    // ----- Aktionen für einen bestehenden Skill -----
    else {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $abfrage = $db->prepare('SELECT id, name, reihenfolge FROM skills WHERE id = ?');
        $abfrage->execute([$id]);
        $skill = $abfrage->fetch();

        if (!$skill) {
            meldungSetzen('Diesen Skill gibt es nicht.');
        }

        // Ändern
        elseif ($aktion === 'speichern') {
            $name     = trim($_POST['name'] ?? '');
            $werkzeug = trim($_POST['werkzeug'] ?? '');
            if ($name === '' || $werkzeug === '') {
                meldungSetzen('Skill und Werkzeug dürfen nicht leer sein. Zum Entfernen „Löschen“ verwenden.');
            } elseif (mb_strlen($name) > 100 || mb_strlen($werkzeug) > 100) {
                meldungSetzen('Skill und Werkzeug dürfen je höchstens 100 Zeichen lang sein.');
            } else {
                $db->prepare('UPDATE skills SET name = ?, werkzeug = ? WHERE id = ?')->execute([$name, $werkzeug, $id]);
                meldungSetzen("„{$name}“ gespeichert.");
            }
        }

        // Reihenfolge: gleiches Prinzip wie bei den Projekten (Platz mit dem Nachbarn tauschen)
        elseif ($aktion === 'hoch' || $aktion === 'runter') {
            $nachbarAbfrage = $db->prepare($aktion === 'hoch'
                ? 'SELECT id, reihenfolge FROM skills WHERE reihenfolge < ? ORDER BY reihenfolge DESC LIMIT 1'
                : 'SELECT id, reihenfolge FROM skills WHERE reihenfolge > ? ORDER BY reihenfolge ASC LIMIT 1'
            );
            $nachbarAbfrage->execute([$skill['reihenfolge']]);
            $nachbar = $nachbarAbfrage->fetch();

            if ($nachbar) {
                $db->beginTransaction();
                $tauschen = $db->prepare('UPDATE skills SET reihenfolge = ? WHERE id = ?');
                $tauschen->execute([$nachbar['reihenfolge'], $skill['id']]);
                $tauschen->execute([$skill['reihenfolge'], $nachbar['id']]);
                $db->commit();
            }
        }

        // Löschen
        elseif ($aktion === 'loeschen') {
            $db->prepare('DELETE FROM skills WHERE id = ?')->execute([$id]);
            meldungSetzen("„{$skill['name']}“ gelöscht.");
        }
    }

    header('Location: ueber-mich.php');
    exit;
}


// ---------- Daten für die Anzeige holen ----------
$abfrage = $db->prepare('SELECT wert FROM einstellungen WHERE name = ?');
$abfrage->execute(['status']);
$status = $abfrage->fetchColumn();
$status = $status === false ? '' : $status;   // fetchColumn gibt false, wenn es die Zeile nicht gibt

$skills = $db->query('SELECT id, name, werkzeug FROM skills ORDER BY reihenfolge, id')->fetchAll();

$seitentitel = 'Über mich & Status';
require __DIR__ . '/../includes/admin-kopf.php';
?>

    <h1 class="admin-title">Über mich &amp; Status<span class="dot-accent">.</span></h1>

    <!-- ===================== STATUS ===================== -->
    <section class="admin-section">
      <h2 class="admin-subtitle">Status</h2>
      <p class="admin-hint">Erscheint oben rechts im Header neben dem pulsierenden Punkt. Leer lassen, um ihn auszublenden.</p>

      <form method="post" class="admin-inline-form">
        <?= csrfFeld() ?>
        <input type="hidden" name="aktion" value="status">
        <label for="status" class="visually-hidden">Status</label>
        <input type="text" id="status" name="status" value="<?= e($status) ?>" maxlength="60"
               placeholder="z. B. Available for projects">
        <button type="submit" class="admin-small-button">Speichern</button>
      </form>
    </section>

    <!-- ===================== SKILLS ===================== -->
    <section class="admin-section">
      <h2 class="admin-subtitle">Skills</h2>
      <p class="admin-hint">Die Liste im Teil „Über mich“, in dieser Reihenfolge.</p>

      <?php if (!$skills): ?>
        <p class="bts-empty">Noch keine Skills.</p>
      <?php else: ?>
        <ol class="skill-admin-list">
          <?php foreach ($skills as $nummer => $skill): ?>
            <li class="skill-admin-row">
              <span class="skill-admin-nr"><?= str_pad($nummer + 1, 2, '0', STR_PAD_LEFT) ?></span>

              <!-- Bearbeiten: beide Felder und Speichern in einem Formular -->
              <form method="post" class="skill-admin-edit">
                <?= csrfFeld() ?>
                <input type="hidden" name="aktion" value="speichern">
                <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                <label for="name-<?= $skill['id'] ?>" class="visually-hidden">Skill</label>
                <input type="text" id="name-<?= $skill['id'] ?>" name="name" value="<?= e($skill['name']) ?>" maxlength="100" required>
                <label for="werkzeug-<?= $skill['id'] ?>" class="visually-hidden">Werkzeug</label>
                <input type="text" id="werkzeug-<?= $skill['id'] ?>" name="werkzeug" value="<?= e($skill['werkzeug']) ?>" maxlength="100" required>
                <button type="submit" class="admin-small-button">Speichern</button>
              </form>

              <div class="skill-admin-actions">
                <form method="post">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="aktion" value="hoch">
                  <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                  <button type="submit" class="admin-icon-button" aria-label="<?= e($skill['name']) ?> nach oben"
                    <?= $nummer === 0 ? 'disabled' : '' ?>>&uarr;</button>
                </form>
                <form method="post">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="aktion" value="runter">
                  <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                  <button type="submit" class="admin-icon-button" aria-label="<?= e($skill['name']) ?> nach unten"
                    <?= $nummer === count($skills) - 1 ? 'disabled' : '' ?>>&darr;</button>
                </form>
                <!-- Titel über data-titel, damit Zeichen wie ' nichts kaputt machen (siehe index.php) -->
                <form method="post" data-titel="<?= e($skill['name']) ?>"
                      onsubmit="return confirm('„' + this.dataset.titel + '“ wirklich löschen?');">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="aktion" value="loeschen">
                  <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                  <button type="submit" class="admin-small-button is-danger">Löschen</button>
                </form>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>

      <!-- Neuer Skill -->
      <form method="post" class="skill-admin-new">
        <?= csrfFeld() ?>
        <input type="hidden" name="aktion" value="hinzufuegen">
        <label for="neu-name" class="visually-hidden">Neuer Skill</label>
        <input type="text" id="neu-name" name="name" maxlength="100" placeholder="Neuer Skill, z. B. Moderation" required>
        <label for="neu-werkzeug" class="visually-hidden">Werkzeug</label>
        <input type="text" id="neu-werkzeug" name="werkzeug" maxlength="100" placeholder="Werkzeug, z. B. Live & Studio" required>
        <button type="submit" class="admin-small-button">+ Hinzufügen</button>
      </form>
    </section>

<?php require __DIR__ . '/../includes/admin-fuss.php'; ?>
