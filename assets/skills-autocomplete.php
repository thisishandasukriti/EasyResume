<?php
/**
 * assets/skills-autocomplete.php
 * ----------------------------------------------------------------------
 * GET ?q=<partial text>
 * Returns JSON suggestions for the Skills tag input: direct matches
 * (prefix first, then substring) across technical skills, soft skills,
 * and certifications, plus a small set of "related" skills pulled from
 * role_profiles.php when a matched skill belongs to a known role
 * (e.g. typing "python" can surface "django"/"sql" as related, since
 * both appear in the Backend Developer profile alongside it).
 *
 * Presentation/data-lookup only — does not touch resume_data.php,
 * session, or any saved resume content.
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

header('Content-Type: application/json');

// Polyfill for mb_strlen / mb_strtolower
function mbs_len(string $s): int { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }
function mbs_lower(string $s): string { return function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); }
function mbs_starts_with(string $haystack, string $needle): bool { return function_exists('mb_strlen') ? mb_strpos($haystack, $needle) === 0 : strpos($haystack, $needle) === 0; }

$skillsFile      = __DIR__ . '/../config/skills.php';
$certsFile       = __DIR__ . '/../config/certifications.php';
$rolesFile       = __DIR__ . '/../config/role_profiles.php';
$softSkillsFile  = __DIR__ . '/../config/soft_skills.php';

$query = trim((string) ($_GET['q'] ?? ''));
if ($query === '' || mbs_len($query) < 1) {
    echo json_encode([]);
    exit;
}
$q = mbs_lower($query);

$skillsData = is_file($skillsFile) ? require $skillsFile : ['categories' => [], 'high_priority' => []];
$certsData  = is_file($certsFile) ? require $certsFile : [];
$rolesData  = is_file($rolesFile) ? require $rolesFile : [];
$softSkills = is_file($softSkillsFile) ? require $softSkillsFile : [];

$highPriority = array_flip(array_map('mbs_lower', $skillsData['high_priority'] ?? []));

/**
 * Build one flat lookup: label => category, so every entry has a
 * category badge in the dropdown (e.g. "Languages", "Cloud/DevOps",
 * "Soft Skill", "Certification").
 */
$flat = []; // lower(label) => ['label' => original-case, 'category' => ...]

foreach (($skillsData['categories'] ?? []) as $category => $items) {
    foreach ($items as $item) {
        $flat[mbs_lower($item)] = ['label' => $item, 'category' => $category];
    }
}
foreach ($softSkills as $item) {
    $key = mbs_lower($item);
    if (!isset($flat[$key])) {
        $flat[$key] = ['label' => $item, 'category' => 'Soft Skill'];
    }
}
foreach ($certsData as $cert) {
    $key = mbs_lower($cert['display'] ?? $cert['match'] ?? '');
    if ($key !== '' && !isset($flat[$key])) {
        $flat[$key] = ['label' => $cert['display'] ?? $cert['match'], 'category' => 'Certification'];
    }
}

/**
 * Rank: prefix matches first (high-priority ones first within that),
 * then substring matches. Cap direct matches at 8.
 */
$prefixHigh = [];
$prefixOther = [];
$substring = [];

foreach ($flat as $key => $entry) {
    if (mbs_starts_with($key, $q)) {
        if (isset($highPriority[$key])) {
            $prefixHigh[] = $entry;
        } else {
            $prefixOther[] = $entry;
        }
    } elseif (str_contains($key, $q)) {
        $substring[] = $entry;
    }
}

sort($prefixHigh);
sort($prefixOther);
sort($substring);

$matches = array_slice(array_merge($prefixHigh, $prefixOther, $substring), 0, 8);

/**
 * Related skills: for each matched item that belongs to a role profile,
 * pull a couple of sibling skills from that same role that the user
 * hasn't already matched directly. Capped at 4 total, de-duplicated.
 */
$matchedLabels = array_map(fn($m) => mbs_lower($m['label']), $matches);
$related = [];
$relatedSeen = [];

foreach ($rolesData as $roleName => $role) {
    $roleSkills = array_map('mbs_lower', $role['skills'] ?? []);
    $overlap = array_intersect($roleSkills, $matchedLabels);
    if (empty($overlap)) {
        continue;
    }
    foreach ($role['skills'] as $sibling) {
        $key = mbs_lower($sibling);
        if (in_array($key, $matchedLabels, true) || isset($relatedSeen[$key])) {
            continue;
        }
        $relatedSeen[$key] = true;
        $related[] = [
            'label'    => $flat[$key]['label'] ?? $sibling,
            'category' => $flat[$key]['category'] ?? 'Related',
            'related_to' => $roleName,
        ];
        if (count($related) >= 4) {
            break 2;
        }
    }
}

$response = array_merge(
    array_map(fn($m) => ['label' => $m['label'], 'category' => $m['category'], 'type' => 'match'], $matches),
    array_map(fn($r) => ['label' => $r['label'], 'category' => $r['category'], 'type' => 'related', 'related_to' => $r['related_to']], $related)
);

echo json_encode($response);