<?php
/*
 * GEMEINSAME FUNKTIONEN FÜR DAS CMS (admin/)
 *
 * Jede Seite im CMS bindet diese Datei als Erstes ein:
 *   require __DIR__ . '/../includes/admin.php';
 *
 * Enthält:
 * 1. Sitzung (Session) starten
 * 2. Login prüfen
 * 3. Schutz vor gefälschten Formularen (CSRF)
 * 4. Text sicher ausgeben
 * 5. Meldungen nach einer Aktion ("Projekt gespeichert.")
 */

require __DIR__ . '/db.php';


/* ---------- 1. SITZUNG STARTEN ----------
   Eine Session ist das "Gedächtnis" des Servers für einen Besucher.
   Der Browser bekommt dafür ein Cookie mit einer zufälligen Nummer,
   der Server merkt sich dazu z. B., ob diese Person eingeloggt ist. */
session_set_cookie_params([
    'httponly' => true,                     // JavaScript kann das Cookie nicht lesen
    'samesite' => 'Lax',                    // Cookie wird nicht bei Anfragen von fremden Seiten mitgeschickt
    'secure'   => !empty($_SERVER['HTTPS']), // online (https) nur über verschlüsselte Verbindung
]);
session_name('portfolio_admin');
session_start();


/* ---------- 2. LOGIN PRÜFEN ---------- */

// Gibt true zurück, wenn jemand eingeloggt ist
function istEingeloggt(): bool
{
    return isset($_SESSION['benutzer_id']);
}

// Am Anfang jeder geschützten Seite aufrufen:
// Wer nicht eingeloggt ist, wird zur Login-Seite geschickt.
function loginErforderlich(): void
{
    if (!istEingeloggt()) {
        header('Location: login.php');
        exit;
    }
}


/* ---------- 3. SCHUTZ VOR GEFÄLSCHTEN FORMULAREN (CSRF) ----------
   Problem: Eine fremde Webseite könnte heimlich ein Formular an mein CMS
   schicken, während ich eingeloggt bin (z. B. "Projekt löschen").
   Lösung: Jedes Formular im CMS bekommt ein geheimes, zufälliges "Token".
   Der Server nimmt ein Formular nur an, wenn das Token stimmt.
   Eine fremde Seite kennt das Token nicht. */

// Liefert das Token dieser Sitzung (wird beim ersten Aufruf erzeugt)
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Unsichtbares Formularfeld mit dem Token, in jedes <form> einfügen
function csrfFeld(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// Prüft beim Absenden, ob das mitgeschickte Token stimmt
function pruefeCsrf(): void
{
    $gesendet = $_POST['csrf_token'] ?? '';
    // hash_equals vergleicht so, dass man aus der Antwortzeit nichts ablesen kann
    if (!hash_equals(csrfToken(), $gesendet)) {
        http_response_code(400);
        exit('Ungültige Anfrage. Bitte die Seite neu laden und nochmals versuchen.');
    }
}


/* ---------- 4. TEXT SICHER AUSGEBEN ----------
   Alles, was aus der Datenbank oder einem Formular kommt, wird vor der
   Ausgabe "entschärft": Aus < wird &lt; usw. So wird eingegebener
   HTML-Code nur angezeigt und nicht ausgeführt (Schutz vor XSS).
   Kurzer Name e() für "escape", weil man es sehr oft braucht. */
function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}


/* ---------- 5. MELDUNGEN NACH EINER AKTION ----------
   Nach dem Speichern leitet das CMS auf eine andere Seite weiter.
   Damit die Meldung ("Projekt gespeichert.") dort noch ankommt,
   wird sie kurz in der Session zwischengespeichert und nach dem
   Anzeigen sofort wieder gelöscht. */

function meldungSetzen(string $text): void
{
    $_SESSION['meldung'] = $text;
}

// Gibt die Meldung zurück (oder null) und löscht sie,
// damit sie beim nächsten Neuladen nicht nochmals erscheint
function meldungHolen(): ?string
{
    $text = $_SESSION['meldung'] ?? null;
    unset($_SESSION['meldung']);
    return $text;
}
