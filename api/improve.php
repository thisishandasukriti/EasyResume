<?php
// ─────────────────────────────────────────────
//  AJAX: AI Resume Improvement
//  POST: text (bullet or paragraph), mode
//  Modes: bullet | description | ats | wording
// ─────────────────────────────────────────────

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']); exit;
}

require_once __DIR__ . '/ai_handler.php';

$text = trim($_POST['text'] ?? '');
$text = strip_tags($text);

$text = html_entity_decode(
    $text,
    ENT_QUOTES | ENT_HTML5,
    'UTF-8'
);
$allowed_modes = [
    'bullet',
    'description',
    'ats',
    'wording'
];

$mode = strtolower(
    trim($_POST['mode'] ?? 'bullet')
);

if (!in_array($mode, $allowed_modes, true)) {
    $mode = 'bullet';
}

if (!$text) {
    echo json_encode(['success' => false, 'error' => 'No text provided.']); exit;
}

// Polyfill for mb_strlen
$text_len = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);

if ($text_len < 10) {
    echo json_encode([
        'success' => false,
        'error' => 'Please provide more text to improve.'
    ]);
    exit;
}

if ($text_len > 8000) {
    echo json_encode([
        'success' => false,
        'error' => 'Text exceeds maximum allowed length.'
    ]);
    exit;
}

$context = 'You are a professional resume writer and ATS optimization expert. '
         . 'Respond only with the improved text, no explanations or preamble.';

$prompts = [
    'bullet' => "Rewrite the following resume bullet points to be more impactful, ATS-friendly, and achievement-oriented. "
              . "Use strong action verbs. Add quantified metrics where logical. Keep each bullet under 2 lines:\n\n$text",

    'description' => "Rewrite the following resume description to be more professional, concise, and ATS-optimized. "
                   . "Focus on impact and results:\n\n$text",

    'ats'     => "Rewrite the following resume content to maximize ATS score. "
               . "Include relevant keywords naturally, use industry-standard terminology, "
               . "and ensure clear structure:\n\n$text",

    'wording' => "Suggest 3 alternative professional phrasings for the following resume content. "
               . "Number each suggestion. Focus on strong action verbs and professional language:\n\n$text",
];

$prompt = $prompts[$mode] ?? $prompts['bullet'];

try {

    $result = ai_complete($prompt, $context);

}
catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'error' => 'Unable to generate suggestions at the moment.'
    ]);

    exit;
}

echo json_encode([
    'success'  => true,
    'original' => $text,
    'improved' => trim($result),
    'mode'     => $mode,
]);
