<?php
/*
 * CMS: LOGIN
 */

require __DIR__ . '/../includes/admin.php';

// Wer schon eingeloggt ist, muss nicht nochmals
if (istEingeloggt()) {
    header('Location: index.php');
    exit;
}

$fehler = '';
$benutzername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pruefeCsrf();

    $benutzername = trim($_POST['benutzername'] ?? '');
    $passwort     = $_POST['passwort'] ?? '';

    // Benutzer in der Datenbank suchen (Prepared Statement, siehe api/projekte.php)
    $db = verbindeDatenbank();
    $abfrage = $db->prepare('SELECT id, benutzername, passwort_hash FROM benutzer WHERE benutzername = ?');
    $abfrage->execute([$benutzername]);
    $benutzer = $abfrage->fetch();

    // password_verify vergleicht das eingegebene Passwort mit dem gespeicherten Hash
    if ($benutzer && password_verify($passwort, $benutzer['passwort_hash'])) {
        // Neue Session-Nummer vergeben. Schützt davor, dass jemand eine
        // Session-Nummer, die er vor dem Login kannte, weiterverwenden kann.
        session_regenerate_id(true);

        $_SESSION['benutzer_id']   = $benutzer['id'];
        $_SESSION['benutzer_name'] = $benutzer['benutzername'];

        header('Location: index.php');
        exit;
    }

    // Absichtlich nicht verraten, ob der Name oder das Passwort falsch war.
    // Sonst könnte man ausprobieren, welche Benutzernamen es gibt.
    $fehler = 'Benutzername oder Passwort ist falsch.';

    // Eine Sekunde warten: macht automatisches Durchprobieren vieler Passwörter langsam
    sleep(1);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Login – CMS Laila Keller</title>
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
    <h1>Login</h1>

    <?php if (isset($_GET['eingerichtet'])): ?>
      <p class="admin-success">Zugang angelegt. Du kannst dich jetzt einloggen.</p>
    <?php endif; ?>

    <?php if (isset($_GET['abgemeldet'])): ?>
      <p class="admin-success">Du bist abgemeldet.</p>
    <?php endif; ?>

    <?php if ($fehler): ?>
      <p class="admin-error"><?= e($fehler) ?></p>
    <?php endif; ?>

    <form method="post" class="admin-form">
      <?= csrfFeld() ?>

      <label for="benutzername">Benutzername</label>
      <input type="text" id="benutzername" name="benutzername" value="<?= e($benutzername) ?>" required autocomplete="username">

      <label for="passwort">Passwort</label>
      <input type="password" id="passwort" name="passwort" required autocomplete="current-password">

      <button type="submit" class="admin-button">Einloggen</button>
    </form>

    <a href="../index.html" class="admin-back">&larr; Zur Webseite</a>
  </main>

</body>
</html>
