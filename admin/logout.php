<?php
/*
 * CMS: LOGOUT
 * Löscht alles, was sich der Server zu dieser Sitzung gemerkt hat.
 */

require __DIR__ . '/../includes/admin.php';

// Nur per Formular (POST) mit gültigem Token abmelden,
// damit kein fremder Link einen ungewollt abmelden kann
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
pruefeCsrf();

// 1. Alle gespeicherten Werte leeren
$_SESSION = [];

// 2. Das Session-Cookie im Browser löschen (Ablaufdatum in der Vergangenheit)
$cookie = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

// 3. Die Sitzung auf dem Server beenden
session_destroy();

header('Location: login.php?abgemeldet=1');
exit;
