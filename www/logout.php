<?php
require_once __DIR__ . '/includes/auth.php';

// Détruire complètement la session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

// Empêcher le navigateur de mettre cette page en cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Location: /login.php");
exit;
