<?php
/**
 * assets/autosave.php
 * ----------------------------------------------------------------------
 * Fixes the real problem behind "incomplete entries get lost on crash":
 * the form previously only reached the server on final submit, so any
 * browser crash / accidental navigation before that point lost
 * everything typed so far. This endpoint lets builder.js periodically
 * POST the current form state in the background; nothing is ever lost
 * for longer than the debounce window (a couple seconds).
 *
 * Reuses normalize_resume() from resume_data.php (read-only use of an
 * existing, untouched function) so the data written here is in EXACTLY
 * the same shape save_resume.php and everything else already expects —
 * this does not introduce a parallel data format. Writes to the same
 * $_SESSION['resume'] key already used elsewhere; no schema change.
 *
 * Does NOT touch save_resume.php, resume_repository.php, or any
 * database/persistence logic. This is a session-only autosave, exactly
 * like the existing theme persistence.
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../resume_data.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

// $_POST already arrives pre-nested by PHP from field names like
// personal[full_name] / experience[0][company], i.e. already in the
// same shape normalize_resume() expects — no manual parsing needed.
$incoming = $_POST;

// File inputs never reach $_POST; profile_picture is preserved via its
// existing hidden field (personal[profile_picture]) elsewhere in the
// form, so it round-trips through autosave untouched automatically.
unset($incoming['profile_picture_file']);

$_SESSION['resume'] = normalize_resume($incoming);

if (isset($incoming['template'])) {
    $_SESSION['selected_template'] = (string) $incoming['template'];
}

http_response_code(204);