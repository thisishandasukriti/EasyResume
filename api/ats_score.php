<?php
// ─────────────────────────────────────────────
//  AJAX: ATS Score Calculator
//  POST: resume_text, job_description
//  Returns JSON with score, keywords, gaps
// ─────────────────────────────────────────────

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Centralized error handler to guarantee JSON output
function handle_error(string $message): void {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handle_error('Method not allowed');
}

$resume = trim($_POST['resume_text'] ?? '');
$jd     = trim($_POST['job_description'] ?? '');

if (!$resume || !$jd) {
    handle_error('Both resume text and job description are required.');
}

// Input size limits
if (strlen($resume) > 50000 || strlen($jd) > 50000) {
    handle_error('Input text exceeds maximum allowed length.');
}

// Clean HTML before analysis
$resume = strip_tags($resume);
$jd     = strip_tags($jd);


// ── Keyword extraction ────────────────────────
function extract_keywords(string $text): array {
    // Polyfill for mb_strtolower
    $text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    // Remove common stopwords
    $stops = ['the','a','an','and','or','but','in','on','at','to','for','of','with',
              'by','from','as','is','was','are','were','be','been','being','have',
              'has','had','do','does','did','will','would','could','should','may',
              'might','shall','can','need','must','that','this','these','those',
              'we','you','they','he','she','it','our','your','their','its','my'];

    preg_match_all('/\b[a-z][a-z0-9\+\#\.\-]{1,30}\b/u', $text, $matches);
    $words = array_filter($matches[0], fn($w) => !in_array($w, $stops) && strlen($w) > 2);
    $freq  = array_count_values($words);
    arsort($freq);
    return array_unique( array_keys( array_slice($freq, 0, 60, true) ) );
}

// ── Tech / skill keyword list ─────────────────
function get_skill_keywords(): array {
    return [
        'python','javascript','typescript','java','c++','c#','php','ruby','swift','kotlin','go','rust',
        'react','angular','vue','nextjs','nodejs','express','django','flask','spring','laravel',
        'sql','mysql','postgresql','mongodb','redis','elasticsearch','firebase',
        'aws','gcp','azure','docker','kubernetes','terraform','ansible','jenkins','git',
        'machine learning','deep learning','tensorflow','pytorch','scikit-learn','nlp',
        'html','css','sass','webpack','rest','graphql','api','microservices','ci/cd',
        'agile','scrum','jira','figma','tableau','power bi','excel','pandas','numpy',
        'linux','bash','devops','cloud','security','testing','jest','pytest',
        'communication','leadership','teamwork','problem solving','analytical',
    ];
}

$jd_keywords     = extract_keywords($jd);
$resume_keywords = extract_keywords($resume);
$skill_list      = get_skill_keywords();

$skills_config = @require __DIR__ . '/../config/skills.php';
if (!is_array($skills_config)) {
    handle_error('Failed to load skills configuration.');
}
$high_priority_skills = $skills_config['high_priority'] ?? [];

// Matched keywords (appear in both)
$matched = array_values(array_intersect($jd_keywords, $resume_keywords));
// Keywords in JD but not in resume
$missing = array_values(array_diff($jd_keywords, $resume_keywords));
// Important missing keywords (cross-reference skill list)
$important_missing = [];

foreach ($skill_list as $skill) {
    if (
        stripos($jd, $skill) !== false &&
        stripos($resume, $skill) === false
    ) {
        $important_missing[] = $skill;
    }
}
// ── Score calculation ─────────────────────────
$weighted_matches = 0;
$weighted_total = 0;

foreach ($jd_keywords as $keyword) {

    $weight = in_array(
        $keyword,
        $high_priority_skills
    ) ? 2 : 1;

    $weighted_total += $weight;

    if (in_array($keyword, $resume_keywords)) {
        $weighted_matches += $weight;
    }
}

$match_pct = round(
    ($weighted_matches / max($weighted_total, 1)) * 100
);

$total_jd = count($jd_keywords);

// Bonus points for structure signals
$has_bullet    = (substr_count($resume, '•') + substr_count($resume, '-') + substr_count($resume, '*')) > 3;
$has_metrics   = preg_match('/\d+[\%\+x]|\$\d+|\d+ (year|month|team|user|project)/i', $resume);
$has_education = preg_match('/bachelor|master|degree|university|college|b\.?sc|m\.?sc/i', $resume);
$has_sections  = preg_match('/experience|education|skills|summary|objective/i', $resume);

$structure_bonus = ($has_bullet ? 5 : 0) + ($has_metrics ? 8 : 0)
                 + ($has_education ? 4 : 0) + ($has_sections ? 3 : 0);

$score = min(100, $match_pct + $structure_bonus);

// ── Strengths & weaknesses ────────────────────
$strengths  = [];
$weaknesses = [];

if ($match_pct >= 60) $strengths[]  = 'Strong keyword alignment with job description';
else                  $weaknesses[] = 'Low keyword overlap with job description';

if ($has_metrics)     $strengths[]  = 'Includes quantified achievements and metrics';
else                  $weaknesses[] = 'Lacks quantified achievements (numbers, percentages, impact)';

if ($has_bullet)      $strengths[]  = 'Uses structured bullet points for readability';
else                  $weaknesses[] = 'Use bullet points to improve ATS readability';

if ($has_sections)    $strengths[]  = 'Clear section headings detected';
else                  $weaknesses[] = 'Add clear section headings (Experience, Skills, Education)';

if ($has_education)   $strengths[]  = 'Education credentials present';
else                  $weaknesses[] = 'Education section not detected';

if (strlen($resume) > 1500) $strengths[]  = 'Sufficient resume length with detailed content';
else                        $weaknesses[] = 'Resume may be too brief — add more detail';

echo json_encode([
    'success'           => true,
    'score'             => $score,
    'match_percent'     => $match_pct,
    'matched_keywords'  => array_slice($matched, 0, 25),
    'missing_keywords'  => array_slice($missing, 0, 20),
    'important_missing' => array_slice($important_missing, 0, 10),
    'strengths'         => $strengths,
    'weaknesses'        => $weaknesses,
    'stats'             => [
        'jd_keywords'     => count($jd_keywords),
        'resume_keywords' => count($resume_keywords),
        'matched'         => count($matched),
    ],
]);