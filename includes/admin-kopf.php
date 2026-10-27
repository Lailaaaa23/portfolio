<?php
/*
 * KOPF FÜR ALLE CMS-SEITEN (nach dem Login)
 * <head>, Kopfzeile mit Logo, Name und Abmelden.
 *
 * Verwendung:
 *   $seitentitel = 'Projekte';
 *   require __DIR__ . '/../includes/admin-kopf.php';
 *   ... Inhalt der Seite ...
 *   require __DIR__ . '/../includes/admin-fuss.php';
 */
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title><?= e($seitentitel ?? 'CMS') ?> – CMS Laila Keller</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=DM+Sans:opsz,wght@9..40,400..700&display=swap" rel="stylesheet">
  <!-- ?v=… ist das Datum der letzten Änderung der Datei. Ändert sich die CSS,
       ändert sich die Adresse, und der Browser lädt die neue Version statt
       einer alten aus seinem Zwischenspeicher (Cache). -->
  <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
  <link rel="stylesheet" href="../css/admin.css?v=<?= filemtime(__DIR__ . '/../css/admin.css') ?>">
</head>
<body class="admin">

  <header class="admin-header">
    <a href="index.php" class="logo">LK<span>.</span> <small>CMS</small></a>

    <?php
    // Aktuelle Seite herausfinden, um ihren Menüpunkt hervorzuheben.
    // basename() liefert nur den Dateinamen, z. B. "ueber-mich.php"
    $aktuelleSeite = basename($_SERVER['SCRIPT_NAME']);
    $projektSeite = in_array($aktuelleSeite, ['index.php', 'projekt-bearbeiten.php'], true);
    ?>
    <!-- aria-current="page" sagt Screenreadern und der CSS, wo man gerade ist -->
    <nav class="admin-nav">
      <a href="index.php" <?= $projektSeite ? 'aria-current="page"' : '' ?>>Projekte</a>
      <a href="ueber-mich.php" <?= $aktuelleSeite === 'ueber-mich.php' ? 'aria-current="page"' : '' ?>>Über mich &amp; Status</a>
    </nav>

    <div class="admin-header-right">
      <span>Eingeloggt als <strong><?= e($_SESSION['benutzer_name']) ?></strong></span>
      <a href="../index.html" target="_blank">Webseite ansehen ↗&#xFE0E;</a>
      <form method="post" action="logout.php">
        <?= csrfFeld() ?>
        <button type="submit" class="admin-link-button">Abmelden</button>
      </form>
    </div>
  </header>

  <main class="admin-main">

    <?php
    // Meldung der letzten Aktion anzeigen (falls es eine gibt)
    $meldung = meldungHolen();
    if ($meldung): ?>
      <p class="admin-success" role="status"><?= e($meldung) ?></p>
    <?php endif; ?>
