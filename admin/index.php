<?php
/*
 * CMS: STARTSEITE (nur nach Login)
 * Hier kommt im nächsten Schritt die Übersicht aller Projekte hin.
 */

require __DIR__ . '/../includes/admin.php';
loginErforderlich();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>CMS – Laila Keller</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=DM+Sans:opsz,wght@9..40,400..700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin">

  <header class="admin-header">
    <p class="logo">LK<span>.</span> <small>CMS</small></p>
    <div class="admin-header-right">
      <span>Eingeloggt als <strong><?= e($_SESSION['benutzer_name']) ?></strong></span>
      <a href="../index.html" target="_blank">Webseite ansehen ↗</a>
      <form method="post" action="logout.php">
        <?= csrfFeld() ?>
        <button type="submit" class="admin-link-button">Abmelden</button>
      </form>
    </div>
  </header>

  <main class="admin-main">
    <h1 class="headline">Hallo<span class="dot-accent">.</span></h1>
    <p class="admin-hint">Der Login funktioniert. Hier entsteht als Nächstes die Übersicht deiner Projekte.</p>
  </main>

</body>
</html>
