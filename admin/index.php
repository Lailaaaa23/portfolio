<?php
/*
 * CMS: ÜBERSICHT ALLER PROJEKTE (nur nach Login)
 */

require __DIR__ . '/../includes/admin.php';
loginErforderlich();

// Alle Projekte holen, auch die versteckten (anders als in der öffentlichen API)
$db = verbindeDatenbank();
$projekte = $db->query(
    'SELECT id, titel, kategorie, bild, sichtbar
     FROM projekte
     ORDER BY reihenfolge, id'
)->fetchAll();

$seitentitel = 'Projekte';
require __DIR__ . '/../includes/admin-kopf.php';
?>

    <!-- Kopf: links Überschrift und Hinweis, rechts der Button -->
    <div class="admin-title-row">
      <div>
        <h1 class="admin-title">Projekte<span class="dot-accent">.</span></h1>
        <p class="admin-hint">Die Reihenfolge hier ist dieselbe wie im Karussell auf der Webseite.</p>
      </div>
      <a href="projekt-bearbeiten.php" class="admin-button">+ Neues Projekt</a>
    </div>

    <?php if (!$projekte): ?>
      <p>Noch keine Projekte vorhanden.</p>
    <?php else: ?>

      <!-- Kacheln im Raster: Nummer 1 ist oben links, wie im Karussell zuerst -->
      <ol class="admin-tiles">
        <?php foreach ($projekte as $nummer => $projekt): ?>
          <!-- Versteckte Projekte bekommen eine eigene Klasse und werden blasser dargestellt -->
          <li class="admin-tile <?= $projekt['sichtbar'] ? '' : 'is-hidden' ?>">

            <div class="admin-tile-image">
              <img src="../<?= e($projekt['bild']) ?>" alt="">
              <?php if (!$projekt['sichtbar']): ?>
                <span class="admin-badge">Versteckt</span>
              <?php endif; ?>
            </div>

            <div class="admin-tile-text">
              <span class="admin-tile-nr"><?= str_pad($nummer + 1, 2, '0', STR_PAD_LEFT) ?></span>
              <strong><?= e($projekt['titel']) ?></strong>
              <span><?= e($projekt['kategorie']) ?></span>
            </div>

            <div class="admin-tile-actions">
              <!-- Jeder Knopf ist ein kleines Formular mit Token, Projekt-ID und Aktion -->

              <!-- Obere Reihe: Reihenfolge links, Löschen rechts -->
              <div class="admin-tile-row">
                <form method="post" action="projekt-aktion.php">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="id" value="<?= $projekt['id'] ?>">
                  <input type="hidden" name="aktion" value="hoch">
                  <!-- Das erste Projekt kann nicht weiter nach vorne -->
                  <button type="submit" class="admin-icon-button" aria-label="<?= e($projekt['titel']) ?> nach vorne"
                    <?= $nummer === 0 ? 'disabled' : '' ?>>&larr;</button>
                </form>

                <form method="post" action="projekt-aktion.php">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="id" value="<?= $projekt['id'] ?>">
                  <input type="hidden" name="aktion" value="runter">
                  <!-- Das letzte Projekt kann nicht weiter nach hinten -->
                  <button type="submit" class="admin-icon-button" aria-label="<?= e($projekt['titel']) ?> nach hinten"
                    <?= $nummer === count($projekte) - 1 ? 'disabled' : '' ?>>&rarr;</button>
                </form>

                <!-- onsubmit: Vor dem Löschen fragt der Browser nach.
                     "Abbrechen" gibt false zurück und verhindert das Absenden.
                     Der Titel kommt über data-titel und nicht direkt in den
                     JavaScript-Code, damit Zeichen wie ' im Titel nichts kaputt machen. -->
                <form method="post" action="projekt-aktion.php" class="admin-push-right"
                      data-titel="<?= e($projekt['titel']) ?>"
                      onsubmit="return confirm('„' + this.dataset.titel + '“ wirklich endgültig löschen?');">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="id" value="<?= $projekt['id'] ?>">
                  <input type="hidden" name="aktion" value="loeschen">
                  <button type="submit" class="admin-small-button is-danger">Löschen</button>
                </form>
              </div>

              <!-- Untere Reihe: Bearbeiten und Verstecken, gleich breit -->
              <div class="admin-tile-row is-main">
                <a href="projekt-bearbeiten.php?id=<?= $projekt['id'] ?>" class="admin-small-button">Bearbeiten</a>

                <form method="post" action="projekt-aktion.php">
                  <?= csrfFeld() ?>
                  <input type="hidden" name="id" value="<?= $projekt['id'] ?>">
                  <input type="hidden" name="aktion" value="sichtbar">
                  <button type="submit" class="admin-small-button">
                    <?= $projekt['sichtbar'] ? 'Verstecken' : 'Zeigen' ?>
                  </button>
                </form>
              </div>
            </div>

          </li>
        <?php endforeach; ?>
      </ol>

    <?php endif; ?>

<?php require __DIR__ . '/../includes/admin-fuss.php'; ?>
