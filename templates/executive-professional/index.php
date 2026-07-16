<?php
/**
 * templates/executive-professional/index.php
 * Full-width dark banner header (name/title/contact), then a two-column
 * body: wide main column (summary, experience, education) + narrow
 * side column (skills, certifications, languages, achievements,
 * interests, references). Aimed at senior/leadership resumes.
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
<div class="resume-page resume executive-professional theme-<?= htmlspecialchars($resume['theme'] ?? 'orange', ENT_QUOTES) ?>">

  <header class="ep-banner">
    <h1 class="ep-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="ep-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    <p class="ep-contact">
      <?= ph($p['email'], 'email') ?> &nbsp;&bull;&nbsp;
      <?= ph($p['phone'], 'phone') ?> &nbsp;&bull;&nbsp;
      <?= ph($p['address'], 'address') ?>
      <?php if (!empty($p['linkedin'])): ?> &nbsp;&bull;&nbsp; <?= ph($p['linkedin'], 'linkedin') ?><?php endif; ?>
    </p>
  </header>

  <div class="ep-body">
    <main class="ep-main">

      <section class="resume-section ep-section">
        <h2 class="ep-heading">Executive Summary</h2>
        <?= phLines($resume['summary'], 'summary') ?>
      </section>

      <?php if (!empty($resume['experience'])): ?>
      <section class="resume-section ep-section">
        <h2 class="ep-heading">Leadership Experience</h2>
        <?php foreach ($resume['experience'] as $job): ?>
          <div class="resume-entry ep-entry">
            <div class="ep-entry-top">
              <div>
                <h3 class="ep-entry-title"><?= ph($job['position'], 'position') ?></h3>
                <p class="ep-entry-sub"><?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?> &middot; <?= ph($job['location'], 'location') ?><?php endif; ?></p>
              </div>
              <div class="ep-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></div>
            </div>
            <div class="ep-entry-desc"><?= phLines($job['description'], 'description') ?></div>
          </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

      <?php if (!empty($resume['education'])): ?>
      <section class="resume-section ep-section">
        <h2 class="ep-heading">Education</h2>
        <?php foreach ($resume['education'] as $edu): ?>
          <div class="resume-entry ep-entry">
            <div class="ep-entry-top">
              <div>
                <h3 class="ep-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?></h3>
                <p class="ep-entry-sub"><?= ph($edu['institution'], 'institution') ?></p>
              </div>
              <div class="ep-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

      <?php if (!empty($resume['projects'])): ?>
      <section class="resume-section ep-section">
        <h2 class="ep-heading">Key Initiatives</h2>
        <?php foreach ($resume['projects'] as $proj): ?>
          <div class="resume-entry ep-entry">
            <h3 class="ep-entry-title"><?= ph($proj['name'], 'project_name') ?></h3>
            <p class="ep-entry-sub"><?= ph($proj['technologies'], 'technologies') ?></p>
            <div class="ep-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
          </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

    </main>

    <aside class="ep-side">
      <?php if (!empty($resume['skills'])): ?>
      <div class="ep-block">
        <h2 class="ep-side-heading">Core Strengths</h2>
        <ul class="ep-tags">
          <?php foreach ($resume['skills'] as $skill): ?><li><?= ph($skill, 'skill') ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['certifications'])): ?>
      <div class="ep-block">
        <h2 class="ep-side-heading">Certifications</h2>
        <ul class="ep-list">
          <?php foreach ($resume['certifications'] as $cert): ?>
            <li><?= ph($cert['name'], 'certification') ?><?php if (!empty($cert['year'])): ?> <span class="ep-dim">(<?= ph($cert['year'], 'year') ?>)</span><?php endif; ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['achievements'])): ?>
      <div class="ep-block">
        <h2 class="ep-side-heading">Achievements</h2>
        <ul class="ep-list">
          <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['languages'])): ?>
      <div class="ep-block">
        <h2 class="ep-side-heading">Languages</h2>
        <ul class="ep-list">
          <?php foreach ($resume['languages'] as $lang): ?><li><?= ph($lang['name'], 'language') ?> <span class="ep-dim"><?= ph($lang['level'], 'level') ?></span></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['interests'])): ?>
      <div class="ep-block">
        <h2 class="ep-side-heading">Interests</h2>
        <p class="ep-inline"><?= implode(', ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p>
      </div>
      <?php endif; ?>

      <?php if (!empty($resume['references'])): ?>
      <div class="ep-block">
        <h2 class="ep-side-heading">References</h2>
        <?php foreach ($resume['references'] as $ref): ?>
          <p class="ep-reference"><strong><?= ph($ref['name'], 'reference_name') ?></strong><br><?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?><br><?= ph($ref['email'], 'email') ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</div>