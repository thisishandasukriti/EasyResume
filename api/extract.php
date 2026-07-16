<?php
// ─────────────────────────────────────────────
//  AJAX: Skill Extractor
//  POST: resume_text
//  Returns: skills, tools, technologies, certs
// ─────────────────────────────────────────────

header('Content-Type: application/json');

// Ensure we always return JSON, even on fatal errors
function handleError($msg) {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Method not allowed');
}

$resume = trim($_POST['resume_text'] ?? '');
$resume = strip_tags($resume);
$resume = html_entity_decode(
    $resume,
    ENT_QUOTES | ENT_HTML5,
    'UTF-8'
);

if (!$resume) {
    handleError('Resume text is required.');
}

// Limit input size to prevent memory issues
if (strlen($resume) > 50000) {
    handleError('Resume text is too long (max 50,000 characters).');
}

// Use strtolower instead of mb_strtolower (no mbstring dependency)
// Case-insensitive matching is handled by the 'i' flag in preg_match
$text_lower = strtolower($resume);

// ── Static keyword dictionaries ───────────────
$skills_config = @require __DIR__ . '/../config/skills.php';
if (!$skills_config || !isset($skills_config['categories'])) {
    handleError('Skills configuration not found.');
}
$TECH_SKILLS = $skills_config['categories'];

$CERT_PATTERNS = @require __DIR__ . '/../config/certifications.php';
if (!$CERT_PATTERNS) {
    handleError('Certifications configuration not found.');
}

$SOFT_SKILLS = @require __DIR__ . '/../config/soft_skills.php';
if (!$SOFT_SKILLS) {
    handleError('Soft skills configuration not found.');
}

// ── Extraction logic ──────────────────────────
$found = [];
foreach ($TECH_SKILLS as $category => $keywords) {
    foreach ($keywords as $kw) {
        if (
            preg_match(
                '/\b' . preg_quote($kw, '/') . '\b/i',
                $text_lower
            )
        ) {
            $found[$category][] = $kw;
        }
    }
}
foreach ($found as $cat => $items) {
    $found[$cat] = array_values(array_unique($items));
}

// Certifications — look for patterns
$certs_found = [];

foreach ($CERT_PATTERNS as $cert_entry) {
    // Support both old string format and new object format
    $cert_text = is_array($cert_entry) ? ($cert_entry['match'] ?? '') : $cert_entry;
    if (!$cert_text) continue;
    
    $pattern = '/\b' . preg_quote(strtolower($cert_text), '/') . '\b/i';
    if (preg_match($pattern, $text_lower)) {
        // Use display name if available, otherwise use match text
        $display = is_array($cert_entry) ? ($cert_entry['display'] ?? $cert_text) : $cert_text;
        $certs_found[] = $display;
    }
}

$certs_found = array_values(array_unique($certs_found));

// Soft skills
$soft_found = array_filter(
    $SOFT_SKILLS,
    fn($s) => preg_match('/\b' . preg_quote($s, '/') . '\b/i', $text_lower)
);

// ── Normalize capitalization ──────────────────
// Use strtolower/strtoupper (ASCII-only, works for our known acronyms)
$normalize = fn(array $items) => array_values(array_unique(
    array_map(fn($i) => match(strtolower($i)) {
        'css','html','sql','php','api','aws','gcp','nlp','rest api','soap','grpc',
        'ci/cd','vs code','ml','ai','cka','ckad','pmp','csm' => strtoupper($i),
        'javascript','typescript' => ucfirst($i),
        'nodejs' => 'Node.js',
        'nextjs' => 'Next.js',
        'fastapi' => 'FastAPI',
        'graphql' => 'GraphQL',
        'nestjs' => 'NestJS',
        'mongodb' => 'MongoDB',
        'postgresql' => 'PostgreSQL',
        'tensorflow' => 'TensorFlow',
        'pytorch' => 'PyTorch',
        default => ucfirst($i),
    }, $items)
));

$categories = [];
foreach ($found as $cat => $items) {
    if ($items) $categories[$cat] = $normalize($items);
}

$total_skills = array_sum(array_map('count', $categories))
              + count($certs_found) + count($soft_found);

echo json_encode([
    'success'     => true,
    'categories'  => $categories,
    'soft_skills' => array_values(array_map('ucfirst', $soft_found)),
    'certifications' => $certs_found,
    'total_count' => $total_skills,
    'summary'     => [
        'technical_categories' => count($categories),
        'total_technical'      => array_sum(array_map('count', $categories)),
        'certifications'       => count($certs_found),
        'soft_skills'          => count($soft_found),
    ],
]);
