<?php
/**
 * templates/elegant-two-column/index.php
 * Centered serif header spanning the full width, then two balanced
 * columns separated by a hairline vertical rule: a narrower left
 * column (skills/education/languages/interests/references) and a
 * wider right column (summary/experience/projects/certifications/
 * achievements).
 * Receives: $resume (see /resume_data.php). Presentation only.
 */
require_once dirname(__DIR__, 2) . '/resume_data.php';
if (!isset($resume) || !is_array($resume)) {
    $resume = get_empty_resume();
}
$resumeTheme = $resume['theme'] ?? 'orange';  
if (resume_is_blank($resume)) {
    $resume = ensure_placeholder_rows($resume);
}
$resume['theme'] = $resumeTheme;
$p = $resume['personal'];
?>
<div class="resume-page resume elegant-two-column theme-<?= htmlspecialchars($resume['theme'] ?? 'orange', ENT_QUOTES) ?>">

  <header class="e2c-header">
    <h1 class="e2c-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="e2c-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    <p class="e2c-contact">
      <?= ph($p['email'], 'email') ?> &nbsp;&middot;&nbsp; <?= ph($p['phone'], 'phone') ?> &nbsp;&middot;&nbsp; <?= ph($p['address'], 'address') ?>
      <?php if (!empty($p['linkedin'])): ?> &nbsp;&middot;&nbsp; <?= ph($p['linkedin'], 'linkedin') ?><?php endif; ?>
      <?php if (!empty($p['portfolio'])): ?> &nbsp;&middot;&nbsp; <?= ph($p['portfolio'], 'portfolio') ?><?php endif; ?>
    </p>
  </header>

  <div class="e2c-columns">
    <aside class="e2c-col e2c-col-left">

      <?php if (!empty($resume['skills'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Skills</h2>
        <ul class="e2c-list-plain">
          <?php foreach ($resume['skills'] as $skill): ?><li><?= ph($skill, 'skill') ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['education'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Education</h2>
        <?php foreach ($resume['education'] as $edu): ?>
          <div class="resume-entry e2c-entry">
            <p class="e2c-entry-title"><?= ph($edu['degree'], 'degree') ?></p>
            <p class="e2c-entry-sub"><?= ph($edu['institution'], 'institution') ?></p>
            <p class="e2c-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['languages'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Languages</h2>
        <ul class="e2c-list-plain">
          <?php foreach ($resume['languages'] as $lang): ?><li><?= ph($lang['name'], 'language') ?> <span class="e2c-dim"><?= ph($lang['level'], 'level') ?></span></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['interests'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Interests</h2>
        <p class="e2c-inline"><?= implode(', ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['references'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">References</h2>
        <?php foreach ($resume['references'] as $ref): ?>
          <p class="e2c-reference"><?= ph($ref['name'], 'reference_name') ?><br><span class="e2c-dim"><?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?></span></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </aside>

    <main class="e2c-col e2c-col-right">

      <div class="e2c-block">
        <h2 class="e2c-heading">Profile</h2>
        <?= phLines($resume['summary'], 'summary') ?>
      </div>

      <?php if (!empty($resume['experience'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Experience</h2>
        <?php foreach ($resume['experience'] as $job): ?>
          <div class="resume-entry e2c-entry">
            <div class="e2c-entry-top">
              <div>
                <p class="e2c-entry-title"><?= ph($job['position'], 'position') ?></p>
                <p class="e2c-entry-sub"><?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?> &middot; <?= ph($job['location'], 'location') ?><?php endif; ?></p>
              </div>
              <p class="e2c-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></p>
            </div>
            <div class="e2c-entry-desc"><?= phLines($job['description'], 'description') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['projects'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Projects</h2>
        <?php foreach ($resume['projects'] as $proj): ?>
          <div class="resume-entry e2c-entry">
            <p class="e2c-entry-title"><?= ph($proj['name'], 'project_name') ?></p>
            <p class="e2c-entry-sub"><?= ph($proj['technologies'], 'technologies') ?></p>
            <div class="e2c-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['certifications'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Certifications</h2>
        <ul class="e2c-list-plain">
          <?php foreach ($resume['certifications'] as $cert): ?>
            <li><?= ph($cert['name'], 'certification') ?> <span class="e2c-dim"><?= ph($cert['issuer'], 'issuer') ?><?php if (!empty($cert['year'])): ?>, <?= ph($cert['year'], 'year') ?><?php endif; ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['achievements'])): ?>
      <div class="e2c-block">
        <h2 class="e2c-heading">Achievements</h2>
        <ul class="e2c-list-plain">
          <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

    </main>
  </div>
</div>
