<?php
/*
 * CMS EINRICHTEN: ERSTEN BENUTZER ANLEGEN
 *
 * Funktioniert nur, solange es noch KEINEN Benutzer gibt.
 * Sobald ein Benutzer existiert, sperrt sich diese Seite selbst.
 * So kann niemand später über diese Seite einen eigenen Zugang anlegen.
 */

require __DIR__ . '/../includes/admin.php';

$db = verbindeDatenbank();

// Gibt es schon einen Benutzer?
$anzahl = (int) $db->query('SELECT COUNT(*) FROM benutzer')->fetchColumn();
if ($anzahl > 0) {
    http_response_code(403);   // 403 = "Zugriff verboten"
    exit('Das CMS ist bereits eingerichtet. Zum Login: admin/login.php');
}

$fehler = '';
$benutzername = '';

// Wurde das Formular abgeschickt?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pruefeCsrf();

    $benutzername = trim($_POST['benutzername'] ?? '');
    $passwort     = $_POST['passwort'] ?? '';
    $wiederholung = $_POST['passwort_wiederholen'] ?? '';

    // Eingaben prüfen
    if ($benutzername === '' || mb_strlen($benutzername) > 50) {
        $fehler = 'Bitte einen Benutzernamen mit höchstens 50 Zeichen eingeben.';
    } elseif (mb_strlen($passwort) < 12) {
        $fehler = 'Das Passwort muss mindestens 12 Zeichen lang sein.';
    } elseif ($passwort !== $wiederholung) {
        $fehler = 'Die beiden Passwörter stimmen nicht überein.';
    } else {
        // password_hash macht aus dem Passwort einen "Fingerabdruck".
        // Nur dieser wird gespeichert. Aus ihm lässt sich das Passwort
        // nicht zurückrechnen, prüfen lässt es sich aber mit password_verify.
        $hash = password_hash($passwort, PASSWORD_DEFAULT);

        $abfrage = $db->prepare('INSERT INTO benutzer (benutzername, passwort_hash) VALUES (?, ?)');
        $abfrage->execute([$benutzername, $hash]);

        // Fertig: weiter zum Login
        header('Location: login.php?eingerichtet=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>CMS einrichten – Laila Keller</title>
  <!-- Favicon: lila Stern im Browser-Tab (SVG für moderne Browser, PNG als Ersatz).
       Auf dem iPhone-Home-Bildschirm erscheint stattdessen das LK.-Logo. -->
  <link rel="icon" href="../images/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="../images/favicon-32.png" sizes="32x32" type="image/png">
  <link rel="apple-touch-icon" href="../images/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-title" content="Laila Keller">
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=DM+Sans:opsz,wght@9..40,400..700&display=swap" rel="stylesheet">
  <!-- ?v=… ist das Datum der letzten Änderung der Datei. Ändert sich die CSS,
       ändert sich die Adresse, und der Browser lädt die neue Version statt
       einer alten aus seinem Zwischenspeicher (Cache). -->
  <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
  <link rel="stylesheet" href="../css/admin.css?v=<?= filemtime(__DIR__ . '/../css/admin.css') ?>">
</head>
<body class="admin admin-login">

  <main class="login-box">
    <p class="logo">LK<span>.</span></p>
    <h1>CMS einrichten</h1>
    <p class="admin-hint">Lege hier deinen Zugang an. Das geht nur ein einziges Mal.</p>

    <?php if ($fehler): ?>
      <p class="admin-error"><?= e($fehler) ?></p>
    <?php endif; ?>

    <form method="post" class="admin-form">
      <?= csrfFeld() ?>

      <label for="benutzername">Benutzername</label>
      <input type="text" id="benutzername" name="benutzername" value="<?= e($benutzername) ?>" required maxlength="50" autocomplete="username">

      <label for="passwort">Passwort (mindestens 12 Zeichen)</label>
      <input type="password" id="passwort" name="passwort" required minlength="12" autocomplete="new-password">

      <label for="passwort_wiederholen">Passwort wiederholen</label>
      <input type="password" id="passwort_wiederholen" name="passwort_wiederholen" required minlength="12" autocomplete="new-password">

      <button type="submit" class="admin-button">Zugang anlegen</button>
    </form>
  </main>

</body>
</html>
