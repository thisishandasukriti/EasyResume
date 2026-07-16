<?php
// ─────────────────────────────────────────────
//  Auth Guard
//  require_once this at the top of any page
//  that requires a logged-in user.
// ─────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
