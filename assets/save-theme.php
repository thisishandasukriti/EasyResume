<?php
/**
 * assets/save-theme.php
 * ----------------------------------------------------------------------
 * Tiny, presentation-layer-only endpoint. Its only job is to persist the
 * chosen theme onto the existing $_SESSION['resume'] array — the exact
 * same write template-preview.php already performed on every reload in
 * "live" mode. This file exists only because instant client-side theme
 * switching means no page reload (and therefore no normal PHP request)
 * happens on theme change anymore, so something still needs to reach
 * the server to persist the choice.
 *
 * Does NOT touch save_resume.php / resume_repository.php / normalization
 * / database logic. Writes to the same session key those files already
 * read from, so persistence keeps working exactly as before.
 *
 * Called via fetch() from template-preview.php; expects JSON body:
 *   { "theme": "ocean" }
 * Responds 204 on success, 400/422 on bad input.
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

session_start();

header('Content-Type: application/json');

$validThemes = require __DIR__ . '/resume_themes.php';

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

$theme = is_array($payload) ? ($payload['theme'] ?? null) : null;

if (!is_string($theme) || !isset($validThemes[$theme])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid theme.']);
    exit;
}

if (!isset($_SESSION['resume']) || !is_array($_SESSION['resume'])) {
    // Nothing to attach the theme to yet (e.g. user hasn't started a
    // resume). Not an error — just nothing to persist.
    http_response_code(204);
    exit;
}

$_SESSION['resume']['theme'] = $theme;

http_response_code(204);