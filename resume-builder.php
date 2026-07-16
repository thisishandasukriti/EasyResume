<?php
/**
 * resume-builder.php
 * ----------------------------------------------------------------------
 * THE ONE FORM. Every template in /templates is fed by this exact same
 * data shape (see resume_data.php). Switching ?template=<slug> never
 * changes a single field on this page — it only changes which layout
 * the data gets poured into afterwards.
 *
 * On submit, this posts to save_resume.php, which is the ONLY place
 * business logic / persistence belongs (currently: session storage;
 * see the comment block in save_resume.php for where DB writes hook in).
 *
 * ---------------------------------------------------------------------
 * PRESENTATION NOTE (Sprint 2, Phase 1):
 * This markup was restructured for the UI/UX overhaul — section cards,
 * icons, a richer sidebar nav, progress feedback hooks, and collapsible
 * repeatable entries. No field name, id, POST shape, or data-* contract
 * relied on by save_resume.php / builder.js changed. New data-* hooks
 * (data-section-icon, data-repeat-summary, data-repeat-collapsed, etc.)
 * are additive and safe to ignore until builder.css / builder.js are
 * updated to match in the next step.
 *
 * PRESENTATION NOTE (Sprint 2, Phase 1b):
 * Education entries now carry a client-side-only "Education type"
 * toggle (College / School). It does NOT add or rename any backend
 * field — it relabels/hides the existing education[i][...] inputs so
 * a 10th/12th-grade entry doesn't have to be force-fit into "Degree" /
 * "Field of study" language. See eduTypeFor() below and the
 * data-edu-field / data-label-* / data-placeholder-* / data-hide-on-school
 * attributes, handled by assets/builder.js.
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/resume_data.php';
require_once __DIR__ . '/resume_renderer.php';

$templates = require __DIR__ . '/templates_registry.php';

$selectedTemplate = $_GET['template'] ?? ($_SESSION['selected_template'] ?? 'modern-sidebar');
if (get_template_meta($selectedTemplate) === null) {
    $selectedTemplate = 'modern-sidebar';
}
$_SESSION['selected_template'] = $selectedTemplate;

// Pre-fill from whatever the user already saved this session (edit flow).
$resumeOld = isset($_SESSION['resume']) ? normalize_resume($_SESSION['resume']) : get_empty_resume();
$resumeOld = ensure_placeholder_rows($resumeOld); // guarantees one (empty) row per repeatable section to render

function v(?string $val): string
{
    return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES);
}

/**
 * Presentation-only helper: builds a short human label for a repeatable
 * entry's collapsed-card header. Falls back gracefully on empty fields.
 * Does not touch $resumeOld or any backend data shape.
 */
function entrySummary(array $parts): string
{
    $clean = array_filter(array_map('trim', $parts), fn($p) => $p !== '');
    return $clean ? implode(' &middot; ', array_map('htmlspecialchars', $clean)) : 'Untitled entry';
}

/**
 * Presentation-only helper: guesses whether a saved education entry was
 * filled in "school" style or "college" style, to decide which label set
 * the toggle should default to on page load.
 *
 * Rules:
 *  - Both degree and field empty  → 'college' (blank new entry default)
 *  - Field of study populated     → 'college'
 *  - Degree set but field empty   → 'school'
 *
 * Does not change what's stored — the user can always flip the toggle.
 */
function eduTypeFor(array $edu): string
{
    if ($edu['field'] === '' && $edu['degree'] !== '') {
        return 'school';
    }
    return 'college';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resume Builder — EasyResume</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/builder.css">
</head>
<body class="br-shell">

<header class="br-topbar">
  <a href="home.php" class="br-logo">Easy<span>Resume</span></a>
  <nav>
    <a href="template-selection.php">Templates</a>
    <a href="resume-builder.php" class="is-active">Resume Builder</a>
  </nav>
  <span class="br-save-status" data-save-status>All changes saved</span>
</header>

<form class="bf-layout" action="save_resume.php" method="post" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="template" value="<?= v($selectedTemplate) ?>">

  <!-- ===================== SIDEBAR NAV ===================== -->
  <aside class="bf-nav" data-builder-nav>
    <div class="bf-nav-progress" data-progress-wrap>
      <div class="bf-nav-progress-bar"><span data-progress-fill></span></div>
      <span class="bf-nav-progress-label"><span data-progress-percent>0</span>% complete</span>
    </div>

    <a href="#sec-personal"       data-nav-link data-section-icon="user">Personal Info</a>
    <a href="#sec-summary"        data-nav-link data-section-icon="doc">Summary</a>
    <a href="#sec-skills"         data-nav-link data-section-icon="star">Skills</a>
    <a href="#sec-experience"     data-nav-link data-section-icon="briefcase">Experience</a>
    <a href="#sec-education"      data-nav-link data-section-icon="cap">Education</a>
    <a href="#sec-projects"       data-nav-link data-section-icon="rocket">Projects</a>
    <a href="#sec-certifications" data-nav-link data-section-icon="award">Certifications</a>
    <a href="#sec-achievements"   data-nav-link data-section-icon="trophy">Achievements</a>
    <a href="#sec-languages"      data-nav-link data-section-icon="globe">Languages</a>
    <a href="#sec-interests"      data-nav-link data-section-icon="heart">Interests</a>
    <a href="#sec-references"     data-nav-link data-section-icon="contact">References</a>
  </aside>

  <main class="bf-main">
    <div class="bf-intro">
      <h1>Build your resume</h1>
      <p>Fill this in once. Template selected: <strong><?= v(get_template_meta($selectedTemplate)['name']) ?></strong> &middot; <a href="template-selection.php">change template</a>.</p>
    </div>

    <!-- ===================== PERSONAL INFORMATION ===================== -->
    <section class="bf-section bf-card" id="sec-personal" data-section-icon="user">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">👤</span>
        <div>
          <h2>Personal Information</h2>
          <p class="bf-section-hint">Shown at the top of every template.</p>
        </div>
      </div>
      <div class="bf-grid">
        <div class="bf-field"><label for="full_name">Full name</label><input type="text" id="full_name" name="personal[full_name]" value="<?= v($resumeOld['personal']['full_name']) ?>"></div>
        <div class="bf-field"><label for="professional_title">Professional title</label><input type="text" id="professional_title" name="personal[professional_title]" value="<?= v($resumeOld['personal']['professional_title']) ?>"></div>
        <div class="bf-field"><label for="email">Email</label><input type="email" id="email" name="personal[email]" value="<?= v($resumeOld['personal']['email']) ?>"></div>

        <!-- Phone: visible split UI (country-code select + number input) writes
             into a single hidden field `personal[phone]` via builder.js syncPhone().
             The split is purely presentational — save_resume.php only ever sees
             the combined hidden value. -->
        <div class="bf-field">
          <label for="phone_number">Phone</label>
          <div style="display:flex; gap:6px;">
            <select id="phone_country_code" style="flex:0 0 92px;" aria-label="Country code">
              <?php
                $dialCodes = [
                  '+1'   => 'US/CA', '+44'  => 'UK',  '+91'  => 'IN',  '+61'  => 'AU',
                  '+49'  => 'DE',    '+33'  => 'FR',   '+81'  => 'JP',  '+86'  => 'CN',
                  '+971' => 'UAE',   '+65'  => 'SG',   '+27'  => 'ZA',  '+34'  => 'ES',
                  '+39'  => 'IT',    '+7'   => 'RU',   '+82'  => 'KR',  '+55'  => 'BR',
                ];
                $rawPhone     = $resumeOld['personal']['phone'];
                $detectedCode = '+1';
                $numberPart   = $rawPhone;
                foreach (array_keys($dialCodes) as $code) {
                    if (str_starts_with($rawPhone, $code)) {
                        $detectedCode = $code;
                        $numberPart   = trim(substr($rawPhone, strlen($code)));
                        break;
                    }
                }
              ?>
              <?php foreach ($dialCodes as $code => $label): ?>
                <option value="<?= v($code) ?>"<?= $code === $detectedCode ? ' selected' : '' ?>><?= v($code) ?> <?= v($label) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="tel" id="phone_number" style="flex:1;" value="<?= v($numberPart) ?>" placeholder="555 123 4567" autocomplete="tel-national">
          </div>
          <!-- Combined value submitted to save_resume.php. builder.js keeps this
               in sync via syncPhone() whenever the select or number input changes. -->
          <input type="hidden" id="phone" name="personal[phone]" value="<?= v($rawPhone) ?>">
        </div>

        <div class="bf-field"><label for="address">Address / Location</label><input type="text" id="address" name="personal[address]" value="<?= v($resumeOld['personal']['address']) ?>"></div>
        <div class="bf-field"><label for="linkedin">LinkedIn</label><input type="text" id="linkedin" name="personal[linkedin]" value="<?= v($resumeOld['personal']['linkedin']) ?>"></div>
        <div class="bf-field"><label for="github">GitHub</label><input type="text" id="github" name="personal[github]" value="<?= v($resumeOld['personal']['github']) ?>"></div>
        <div class="bf-field"><label for="portfolio">Portfolio</label><input type="text" id="portfolio" name="personal[portfolio]" value="<?= v($resumeOld['personal']['portfolio']) ?>"></div>
        <div class="bf-field">
          <label for="profile_picture_file">Profile picture (optional)</label>
          <input type="file" id="profile_picture_file" name="profile_picture_file" accept="image/*">
          <input type="hidden" name="personal[profile_picture]" value="<?= v($resumeOld['personal']['profile_picture']) ?>">
        </div>
      </div>
    </section>

    <!-- ===================== SUMMARY ===================== -->
    <section class="bf-section bf-card" id="sec-summary" data-section-icon="doc">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">📝</span>
        <div>
          <h2>Professional Summary</h2>
          <p class="bf-section-hint">2-4 sentences. One line per paragraph is fine — each line break becomes its own line on the resume.</p>
        </div>
      </div>
      <div class="bf-field bf-field-wide">
        <textarea name="summary" rows="4"><?= v($resumeOld['summary']) ?></textarea>
      </div>
    </section>

    <!-- ===================== SKILLS ===================== -->
    <section class="bf-section bf-card" id="sec-skills" data-section-icon="star">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">⭐</span>
        <div>
          <h2>Skills</h2>
          <p class="bf-section-hint">Type a skill and press Enter or comma to add it.</p>
        </div>
      </div>
      <div class="bf-field bf-field-wide" data-tag-input="skills">
        <input type="text" data-tag-text placeholder="e.g. Project Management">
        <div class="bf-tags-input" data-tag-list>
          <?php foreach ($resumeOld['skills'] as $skill): if ($skill === '') continue; ?>
            <span class="bf-tag"><input type="hidden" name="skills[]" value="<?= v($skill) ?>"><?= v($skill) ?><button type="button" aria-label="Remove">&times;</button></span>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ===================== EXPERIENCE ===================== -->
    <section class="bf-section bf-card" id="sec-experience" data-section-icon="briefcase" data-repeat-group="experience">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">💼</span>
        <div>
          <h2>Experience</h2>
          <p class="bf-section-hint">Add one entry per role, most recent first.</p>
        </div>
      </div>
      <div data-repeat-list>
        <?php foreach ($resumeOld['experience'] as $i => $exp): ?>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="<?= ($exp['position'] !== '' || $exp['company'] !== '') ? 'true' : 'false' ?>">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong><?= entrySummary([$exp['position'] ?: 'New role']) ?></strong>
              <span><?= entrySummary([$exp['company'], $exp['location']]) ?><?php if ($exp['start_date'] || $exp['end_date'] || !empty($exp['current'])): ?> &middot; <?= v($exp['start_date']) ?> – <?= !empty($exp['current']) ? 'Present' : v($exp['end_date']) ?><?php endif; ?></span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Company</label><input type="text" name="experience[<?= $i ?>][company]" value="<?= v($exp['company']) ?>"></div>
              <div class="bf-field"><label>Position</label><input type="text" name="experience[<?= $i ?>][position]" value="<?= v($exp['position']) ?>"></div>
              <div class="bf-field"><label>Location</label><input type="text" name="experience[<?= $i ?>][location]" value="<?= v($exp['location']) ?>"></div>
              <div class="bf-field"><label>Start date</label><input type="month" name="experience[<?= $i ?>][start_date]" value="<?= v($exp['start_date']) ?>"></div>
              <div class="bf-field">
                <label>End date</label>
                <input type="month" name="experience[<?= $i ?>][end_date]" value="<?= v($exp['end_date']) ?>" data-end-date <?= !empty($exp['current']) ? 'disabled' : '' ?>>
              </div>
              <div class="bf-field">
                <label>&nbsp;</label>
                <label class="bf-checkbox">
                  <input type="checkbox" name="experience[<?= $i ?>][current]" value="1" data-current-toggle <?= !empty($exp['current']) ? 'checked' : '' ?>>
                  Currently working here
                </label>
              </div>
              <div class="bf-field bf-field-wide"><label>Description</label><textarea name="experience[<?= $i ?>][description]" rows="3" placeholder="One bullet per line"><?= v($exp['description']) ?></textarea></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeat-template>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="false">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong>New role</strong>
              <span>Untitled entry</span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Company</label><input type="text" name="experience[0][company]" value=""></div>
              <div class="bf-field"><label>Position</label><input type="text" name="experience[0][position]" value=""></div>
              <div class="bf-field"><label>Location</label><input type="text" name="experience[0][location]" value=""></div>
              <div class="bf-field"><label>Start date</label><input type="month" name="experience[0][start_date]" value=""></div>
              <div class="bf-field"><label>End date</label><input type="month" name="experience[0][end_date]" value="" data-end-date></div>
              <div class="bf-field">
                <label>&nbsp;</label>
                <label class="bf-checkbox"><input type="checkbox" name="experience[0][current]" value="1" data-current-toggle> Currently working here</label>
              </div>
              <div class="bf-field bf-field-wide"><label>Description</label><textarea name="experience[0][description]" rows="3" placeholder="One bullet per line"></textarea></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="bf-add-btn" data-repeat-add>+ Add experience</button>
    </section>

    <!-- ===================== EDUCATION ===================== -->
    <section class="bf-section bf-card" id="sec-education" data-section-icon="cap" data-repeat-group="education">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">🎓</span>
        <div>
          <h2>Education</h2>
          <p class="bf-section-hint">Use "School" for 10th / 12th grade entries — it relabels the fields below to match (Standard, Year of passing, Percentage / CGPA).</p>
        </div>
      </div>
      <div data-repeat-list>
        <?php foreach ($resumeOld['education'] as $i => $edu): $eduType = eduTypeFor($edu); ?>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="<?= ($edu['institution'] !== '' || $edu['degree'] !== '') ? 'true' : 'false' ?>">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong><?= entrySummary([$edu['degree'] ?: 'New education entry']) ?></strong>
              <span><?= entrySummary([$edu['institution'], $edu['field']]) ?></span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field bf-field-wide">
                <label>Education type</label>
                <select data-edu-type>
                  <option value="college"<?= $eduType === 'college' ? ' selected' : '' ?>>College / University</option>
                  <option value="school"<?=  $eduType === 'school'  ? ' selected' : '' ?>>School (e.g. 10th / 12th grade)</option>
                </select>
              </div>
              <div class="bf-field" data-edu-field="institution" data-label-college="Institution" data-label-school="School name" data-placeholder-school="e.g. Delhi Public School">
                <label>Institution</label><input type="text" name="education[<?= $i ?>][institution]" value="<?= v($edu['institution']) ?>">
              </div>
              <div class="bf-field" data-edu-field="degree" data-label-college="Degree" data-label-school="Standard / Grade" data-placeholder-college="e.g. Bachelor of Science" data-placeholder-school="e.g. 12th Grade / Higher Secondary">
                <label>Degree</label><input type="text" name="education[<?= $i ?>][degree]" value="<?= v($edu['degree']) ?>">
              </div>
              <div class="bf-field" data-edu-field="field" data-hide-on-school="true">
                <label>Field of study</label><input type="text" name="education[<?= $i ?>][field]" value="<?= v($edu['field']) ?>">
              </div>
              <div class="bf-field" data-edu-field="start_date" data-hide-on-school="true">
                <label>Start date</label><input type="month" name="education[<?= $i ?>][start_date]" value="<?= v($edu['start_date']) ?>">
              </div>
              <div class="bf-field" data-edu-field="end_date" data-label-college="End date" data-label-school="Year of passing">
                <label>End date</label><input type="month" name="education[<?= $i ?>][end_date]" value="<?= v($edu['end_date']) ?>">
              </div>
              <div class="bf-field" data-edu-field="grade" data-label-college="Grade (optional)" data-label-school="Percentage / CGPA (optional)" data-placeholder-college="e.g. 3.8 GPA" data-placeholder-school="e.g. 92% or 9.4 CGPA">
                <label>Grade (optional)</label><input type="text" name="education[<?= $i ?>][grade]" value="<?= v($edu['grade']) ?>">
              </div>
              <div class="bf-field bf-field-wide"><label>Description (optional)</label><textarea name="education[<?= $i ?>][description]" rows="2"><?= v($edu['description']) ?></textarea></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeat-template>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="false">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong>New education entry</strong>
              <span>Untitled entry</span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field bf-field-wide">
                <label>Education type</label>
                <select data-edu-type>
                  <option value="college" selected>College / University</option>
                  <option value="school">School (e.g. 10th / 12th grade)</option>
                </select>
              </div>
              <div class="bf-field" data-edu-field="institution" data-label-college="Institution" data-label-school="School name" data-placeholder-school="e.g. Delhi Public School">
                <label>Institution</label><input type="text" name="education[0][institution]" value="">
              </div>
              <div class="bf-field" data-edu-field="degree" data-label-college="Degree" data-label-school="Standard / Grade" data-placeholder-college="e.g. Bachelor of Science" data-placeholder-school="e.g. 12th Grade / Higher Secondary">
                <label>Degree</label><input type="text" name="education[0][degree]" value="">
              </div>
              <div class="bf-field" data-edu-field="field" data-hide-on-school="true">
                <label>Field of study</label><input type="text" name="education[0][field]" value="">
              </div>
              <div class="bf-field" data-edu-field="start_date" data-hide-on-school="true">
                <label>Start date</label><input type="month" name="education[0][start_date]" value="">
              </div>
              <div class="bf-field" data-edu-field="end_date" data-label-college="End date" data-label-school="Year of passing">
                <label>End date</label><input type="month" name="education[0][end_date]" value="">
              </div>
              <div class="bf-field" data-edu-field="grade" data-label-college="Grade (optional)" data-label-school="Percentage / CGPA (optional)" data-placeholder-college="e.g. 3.8 GPA" data-placeholder-school="e.g. 92% or 9.4 CGPA">
                <label>Grade (optional)</label><input type="text" name="education[0][grade]" value="">
              </div>
              <div class="bf-field bf-field-wide"><label>Description (optional)</label><textarea name="education[0][description]" rows="2"></textarea></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="bf-add-btn" data-repeat-add>+ Add education</button>
    </section>

    <!-- ===================== PROJECTS ===================== -->
    <section class="bf-section bf-card" id="sec-projects" data-section-icon="rocket" data-repeat-group="projects">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">🚀</span>
        <div><h2>Projects</h2></div>
      </div>
      <div data-repeat-list>
        <?php foreach ($resumeOld['projects'] as $i => $proj): ?>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="<?= ($proj['name'] !== '') ? 'true' : 'false' ?>">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong><?= entrySummary([$proj['name'] ?: 'New project']) ?></strong>
              <span><?= entrySummary([$proj['technologies']]) ?></span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Project name</label><input type="text" name="projects[<?= $i ?>][name]" value="<?= v($proj['name']) ?>"></div>
              <div class="bf-field"><label>Technologies</label><input type="text" name="projects[<?= $i ?>][technologies]" value="<?= v($proj['technologies']) ?>"></div>
              <div class="bf-field"><label>GitHub</label><input type="text" name="projects[<?= $i ?>][github]" value="<?= v($proj['github']) ?>"></div>
              <div class="bf-field"><label>Live link</label><input type="text" name="projects[<?= $i ?>][live_link]" value="<?= v($proj['live_link']) ?>"></div>
              <div class="bf-field bf-field-wide"><label>Description</label><textarea name="projects[<?= $i ?>][description]" rows="2"><?= v($proj['description']) ?></textarea></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeat-template>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="false">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong>New project</strong>
              <span>Untitled entry</span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Project name</label><input type="text" name="projects[0][name]" value=""></div>
              <div class="bf-field"><label>Technologies</label><input type="text" name="projects[0][technologies]" value=""></div>
              <div class="bf-field"><label>GitHub</label><input type="text" name="projects[0][github]" value=""></div>
              <div class="bf-field"><label>Live link</label><input type="text" name="projects[0][live_link]" value=""></div>
              <div class="bf-field bf-field-wide"><label>Description</label><textarea name="projects[0][description]" rows="2"></textarea></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="bf-add-btn" data-repeat-add>+ Add project</button>
    </section>

    <!-- ===================== CERTIFICATIONS ===================== -->
    <section class="bf-section bf-card" id="sec-certifications" data-section-icon="award" data-repeat-group="certifications">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">🏆</span>
        <div><h2>Certifications</h2></div>
      </div>
      <div data-repeat-list>
        <?php foreach ($resumeOld['certifications'] as $i => $cert): ?>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="<?= ($cert['name'] !== '') ? 'true' : 'false' ?>">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong><?= entrySummary([$cert['name'] ?: 'New certification']) ?></strong>
              <span><?= entrySummary([$cert['issuer'], $cert['year']]) ?></span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Name</label><input type="text" name="certifications[<?= $i ?>][name]" value="<?= v($cert['name']) ?>"></div>
              <div class="bf-field"><label>Issuer</label><input type="text" name="certifications[<?= $i ?>][issuer]" value="<?= v($cert['issuer']) ?>"></div>
              <div class="bf-field"><label>Year</label><input type="text" name="certifications[<?= $i ?>][year]" value="<?= v($cert['year']) ?>"></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeat-template>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="false">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong>New certification</strong>
              <span>Untitled entry</span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Name</label><input type="text" name="certifications[0][name]" value=""></div>
              <div class="bf-field"><label>Issuer</label><input type="text" name="certifications[0][issuer]" value=""></div>
              <div class="bf-field"><label>Year</label><input type="text" name="certifications[0][year]" value=""></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="bf-add-btn" data-repeat-add>+ Add certification</button>
    </section>

    <!-- ===================== ACHIEVEMENTS ===================== -->
    <section class="bf-section bf-card" id="sec-achievements" data-section-icon="trophy">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">🏆</span>
        <div>
          <h2>Achievements</h2>
          <p class="bf-section-hint">Type one and press Enter to add it.</p>
        </div>
      </div>
      <div class="bf-field bf-field-wide" data-tag-input="achievements">
        <input type="text" data-tag-text placeholder="e.g. Employee of the Year, 2023">
        <div class="bf-tags-input" data-tag-list>
          <?php foreach ($resumeOld['achievements'] as $item): if ($item === '') continue; ?>
            <span class="bf-tag"><input type="hidden" name="achievements[]" value="<?= v($item) ?>"><?= v($item) ?><button type="button" aria-label="Remove">&times;</button></span>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ===================== LANGUAGES ===================== -->
    <section class="bf-section bf-card" id="sec-languages" data-section-icon="globe" data-repeat-group="languages">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">🌍</span>
        <div><h2>Languages</h2></div>
      </div>
      <div data-repeat-list>
        <?php foreach ($resumeOld['languages'] as $i => $lang): ?>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="<?= ($lang['name'] !== '') ? 'true' : 'false' ?>">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong><?= entrySummary([$lang['name'] ?: 'New language']) ?></strong>
              <span><?= entrySummary([$lang['level']]) ?></span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Language</label><input type="text" name="languages[<?= $i ?>][name]" value="<?= v($lang['name']) ?>"></div>
              <div class="bf-field">
                <label>Proficiency</label>
                <select name="languages[<?= $i ?>][level]">
                  <?php foreach (['Native', 'Fluent', 'Professional', 'Conversational', 'Basic'] as $lvl): ?>
                    <option value="<?= v($lvl) ?>"<?= $lang['level'] === $lvl ? ' selected' : '' ?>><?= v($lvl) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeat-template>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="false">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong>New language</strong>
              <span>Untitled entry</span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Language</label><input type="text" name="languages[0][name]" value=""></div>
              <div class="bf-field">
                <label>Proficiency</label>
                <select name="languages[0][level]">
                  <?php foreach (['Native', 'Fluent', 'Professional', 'Conversational', 'Basic'] as $lvl): ?>
                    <option value="<?= v($lvl) ?>"><?= v($lvl) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="bf-add-btn" data-repeat-add>+ Add language</button>
    </section>

    <!-- ===================== INTERESTS ===================== -->
    <section class="bf-section bf-card" id="sec-interests" data-section-icon="heart">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">❤️</span>
        <div>
          <h2>Interests</h2>
          <p class="bf-section-hint">Type one and press Enter to add it.</p>
        </div>
      </div>
      <div class="bf-field bf-field-wide" data-tag-input="interests">
        <input type="text" data-tag-text placeholder="e.g. Photography">
        <div class="bf-tags-input" data-tag-list>
          <?php foreach ($resumeOld['interests'] as $item): if ($item === '') continue; ?>
            <span class="bf-tag"><input type="hidden" name="interests[]" value="<?= v($item) ?>"><?= v($item) ?><button type="button" aria-label="Remove">&times;</button></span>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ===================== REFERENCES ===================== -->
    <section class="bf-section bf-card" id="sec-references" data-section-icon="contact" data-repeat-group="references">
      <div class="bf-card-head">
        <span class="bf-card-icon" aria-hidden="true">📇</span>
        <div><h2>References <span style="font-weight:400;color:var(--br-muted);">(optional)</span></h2></div>
      </div>
      <div data-repeat-list>
        <?php foreach ($resumeOld['references'] as $i => $ref): ?>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="<?= ($ref['name'] !== '') ? 'true' : 'false' ?>">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong><?= entrySummary([$ref['name'] ?: 'New reference']) ?></strong>
              <span><?= entrySummary([$ref['position'], $ref['company']]) ?></span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Name</label><input type="text" name="references[<?= $i ?>][name]" value="<?= v($ref['name']) ?>"></div>
              <div class="bf-field"><label>Position</label><input type="text" name="references[<?= $i ?>][position]" value="<?= v($ref['position']) ?>"></div>
              <div class="bf-field"><label>Company</label><input type="text" name="references[<?= $i ?>][company]" value="<?= v($ref['company']) ?>"></div>
              <div class="bf-field"><label>Email</label><input type="email" name="references[<?= $i ?>][email]" value="<?= v($ref['email']) ?>"></div>
              <div class="bf-field"><label>Phone</label><input type="tel" name="references[<?= $i ?>][phone]" value="<?= v($ref['phone']) ?>"></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <template data-repeat-template>
        <div class="bf-repeat-item" data-repeat-item data-repeat-collapsed="false">
          <button type="button" class="bf-repeat-item-head" data-repeat-toggle>
            <div class="bf-repeat-summary" data-repeat-summary>
              <strong>New reference</strong>
              <span>Untitled entry</span>
            </div>
            <span class="bf-repeat-actions">
              <span class="bf-repeat-chevron" data-repeat-chevron aria-hidden="true">▼</span>
            </span>
          </button>
          <div class="bf-repeat-body" data-repeat-body>
            <div class="bf-grid">
              <div class="bf-field"><label>Name</label><input type="text" name="references[0][name]" value=""></div>
              <div class="bf-field"><label>Position</label><input type="text" name="references[0][position]" value=""></div>
              <div class="bf-field"><label>Company</label><input type="text" name="references[0][company]" value=""></div>
              <div class="bf-field"><label>Email</label><input type="email" name="references[0][email]" value=""></div>
              <div class="bf-field"><label>Phone</label><input type="tel" name="references[0][phone]" value=""></div>
            </div>
            <div class="bf-repeat-item-foot">
              <button type="button" class="bf-remove-btn" data-repeat-remove>Remove</button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="bf-add-btn" data-repeat-add>+ Add reference</button>
    </section>

    <!-- ===================== STICKY FOOTER ===================== -->
    <div class="bf-footer-actions">
      <span class="bf-footer-progress" data-footer-progress><span data-footer-progress-percent>0</span>% of your resume is filled in</span>
      <div class="bf-footer-buttons">
        <a class="bf-btn bf-btn-ghost" href="template-selection.php">Cancel</a>
        <button class="bf-btn bf-btn-primary" type="submit">Save &amp; preview resume</button>
      </div>
    </div>
  </main>
</form>

<div class="br-loading-overlay" id="br-loading-overlay" aria-live="polite">
      <span class="br-loading-logo">Easy<span>Resume</span></span>
      <div class="br-loader">
        <div class="br-loader-square"></div>
        <div class="br-loader-square"></div>
        <div class="br-loader-square"></div>
        <div class="br-loader-square"></div>
        <div class="br-loader-square"></div>
        <div class="br-loader-square"></div>
        <div class="br-loader-square"></div>
      </div>
      <span class="br-loading-label">Preparing your preview…</span>
    </div>

<script>
  (function () {
    var form      = document.querySelector('.bf-layout');
    var submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    var overlay   = document.getElementById('br-loading-overlay');
    if (!submitBtn || !overlay) return;

    submitBtn.addEventListener('click', function (e) {
      if (form.checkValidity && !form.checkValidity()) return;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Preparing Preview…';
      overlay.classList.add('is-visible');
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          setTimeout(function () { form.submit(); }, 1200);
        });
      });
      e.preventDefault();
    });
  })();
</script>
<script src="assets/builder.js"></script>
</body>
</html>