<?php
/**
 * save_resume.php
 * ----------------------------------------------------------------------
 * Receives the POST from resume-builder.php, maps it onto the EXACT
 * same $resume shape every template expects (see resume_data.php), and
 * persists it.
 *
 * Templates never see $_POST. They never see the database. All of that
 * stays here — this is the one seam where business logic is allowed to
 * live, by design (see "Code Architecture" in the project brief).
 *
 * CURRENT PERSISTENCE: PHP session ($_SESSION['resume']). This is
 * intentional for this deliverable, since no database schema/credentials
 * were supplied — but it already proves the core idea end-to-end: one
 * submission, any template, switchable instantly on template-preview.php.
 *
 * WIRING UP REAL PERSISTENCE LATER: see the clearly marked block near
 * the bottom of this file. Because every template already reads from
 * the same $resume array regardless of where it came from, swapping
 * "session" for "MySQL row" requires changing ONLY this file.
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/resume_data.php';
require_once __DIR__ . '/resume_renderer.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: resume-builder.php');
    exit;
}

/** Strips empty rows so we don't save a section full of blank entries. */
function strip_empty_rows(array $rows, array $significantKeys): array
{
    return array_values(array_filter($rows, function ($row) use ($significantKeys) {
        foreach ($significantKeys as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return true;
            }
        }
        return false;
    }));
}

$template = $_POST['template'] ?? 'modern-sidebar';
if (get_template_meta($template) === null) {
    $template = 'modern-sidebar';
}

$personalInput = (array) ($_POST['personal'] ?? []);

// Optional profile picture upload — stored as a plain filename reference.
// In production this would validate type/size and move the file into a
// public uploads directory; left as a clear extension point here.
$profilePicture = trim((string) ($personalInput['profile_picture'] ?? ''));
if (!empty($_FILES['profile_picture_file']['name']) && $_FILES['profile_picture_file']['error'] === UPLOAD_ERR_OK) {
    // Example (not enabled by default — no uploads/ directory is assumed to exist):
    // $destDir = __DIR__ . '/uploads/';
    // if (!is_dir($destDir)) { mkdir($destDir, 0755, true); }
    // $safeName = uniqid('avatar_', true) . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['profile_picture_file']['name']);
    // if (move_uploaded_file($_FILES['profile_picture_file']['tmp_name'], $destDir . $safeName)) {
    //     $profilePicture = 'uploads/' . $safeName;
    // }
}

$resume = [
    'personal' => [
        'full_name'          => trim((string) ($personalInput['full_name'] ?? '')),
        'professional_title' => trim((string) ($personalInput['professional_title'] ?? '')),
        'email'              => trim((string) ($personalInput['email'] ?? '')),
        'phone'              => trim((string) ($personalInput['phone'] ?? '')),
        'address'            => trim((string) ($personalInput['address'] ?? '')),
        'linkedin'           => trim((string) ($personalInput['linkedin'] ?? '')),
        'github'             => trim((string) ($personalInput['github'] ?? '')),
        'portfolio'          => trim((string) ($personalInput['portfolio'] ?? '')),
        'profile_picture'    => $profilePicture,
    ],

    'summary' => trim((string) ($_POST['summary'] ?? '')),

    'skills'       => array_values(array_filter(array_map('trim', (array) ($_POST['skills'] ?? [])))),
    'achievements' => array_values(array_filter(array_map('trim', (array) ($_POST['achievements'] ?? [])))),
    'interests'    => array_values(array_filter(array_map('trim', (array) ($_POST['interests'] ?? [])))),

    'education' => strip_empty_rows(array_map(function ($row) {
        return [
            'institution' => trim((string) ($row['institution'] ?? '')),
            'degree'      => trim((string) ($row['degree'] ?? '')),
            'field'       => trim((string) ($row['field'] ?? '')),
            'start_date'  => trim((string) ($row['start_date'] ?? '')),
            'end_date'    => trim((string) ($row['end_date'] ?? '')),
            'grade'       => trim((string) ($row['grade'] ?? '')),
            'description' => trim((string) ($row['description'] ?? '')),
        ];
    }, (array) ($_POST['education'] ?? [])), ['institution', 'degree']),

    'experience' => strip_empty_rows(array_map(function ($row) {
        return [
            'company'     => trim((string) ($row['company'] ?? '')),
            'position'    => trim((string) ($row['position'] ?? '')),
            'location'    => trim((string) ($row['location'] ?? '')),
            'start_date'  => trim((string) ($row['start_date'] ?? '')),
            'end_date'    => trim((string) ($row['end_date'] ?? '')),
            'current'     => !empty($row['current']),
            'description' => trim((string) ($row['description'] ?? '')),
        ];
    }, (array) ($_POST['experience'] ?? [])), ['company', 'position']),

    'projects' => strip_empty_rows(array_map(function ($row) {
        return [
            'name'         => trim((string) ($row['name'] ?? '')),
            'technologies' => trim((string) ($row['technologies'] ?? '')),
            'description'  => trim((string) ($row['description'] ?? '')),
            'github'       => trim((string) ($row['github'] ?? '')),
            'live_link'    => trim((string) ($row['live_link'] ?? '')),
        ];
    }, (array) ($_POST['projects'] ?? [])), ['name']),

    'certifications' => strip_empty_rows(array_map(function ($row) {
        return [
            'name'   => trim((string) ($row['name'] ?? '')),
            'issuer' => trim((string) ($row['issuer'] ?? '')),
            'year'   => trim((string) ($row['year'] ?? '')),
        ];
    }, (array) ($_POST['certifications'] ?? [])), ['name']),

    'languages' => strip_empty_rows(array_map(function ($row) {
        return [
            'name'  => trim((string) ($row['name'] ?? '')),
            'level' => trim((string) ($row['level'] ?? '')),
        ];
    }, (array) ($_POST['languages'] ?? [])), ['name']),

    'references' => strip_empty_rows(array_map(function ($row) {
        return [
            'name'     => trim((string) ($row['name'] ?? '')),
            'position' => trim((string) ($row['position'] ?? '')),
            'company'  => trim((string) ($row['company'] ?? '')),
            'email'    => trim((string) ($row['email'] ?? '')),
            'phone'    => trim((string) ($row['phone'] ?? '')),
        ];
    }, (array) ($_POST['references'] ?? [])), ['name']),
];

$resume = normalize_resume($resume);

/* ----------------------------------------------------------------------
 * CURRENT PERSISTENCE: session only.
 * -------------------------------------------------------------------- */
$_SESSION['resume'] = $resume;
$_SESSION['selected_template'] = $template;

/* ----------------------------------------------------------------------
 * DATABASE PERSISTENCE
 * -------------------------------------------------------------------- */

$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    try {

        $mysqli = db();

        $json = json_encode(
            $resume,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json !== false) {

            $stmt = $mysqli->prepare("
                INSERT INTO resumes (user_id, template_slug, data)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    template_slug = VALUES(template_slug),
                    data = VALUES(data)
            ");

            $stmt->bind_param(
                "iss",
                $userId,
                $template,
                $json
            );

            $stmt->execute();
            $stmt->close();

        } else {

            error_log(
                "Resume JSON encode failed: "
                . json_last_error_msg()
            );

        }

    } catch (Throwable $e) {

        error_log(
            "Resume save failed: "
            . $e->getMessage()
        );

    }
}
header('Location: template-preview.php?template=' . urlencode($template) . '&mode=live&toolbar=1');
exit;
