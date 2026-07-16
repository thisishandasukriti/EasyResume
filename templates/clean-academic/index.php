<?php
/**
 * templates/clean-academic/index.php
 * Education-first, serif CV layout for research/teaching/academic
 * roles. Centered header, generous line-height, small-caps headings,
 * References shown prominently rather than treated as an afterthought.
 * Receives: $resume (see /resume_data.php). Presentation only.
 */
require_once dirname(__DIR__, 2) . '/resume_data.php';
if (!isset($resume) || !is_array($resume)) {
    $resume = get_empty_resume();
}

/**
 * Theme fix: ensure_placeholder_rows() rebuilds $resume from the
 * canonical schema and knows nothing about 'theme' (a presentation-layer
 * addition, not part of resume_data.php's data model). We capture the
 * selected theme before that call and reapply it after, so the accent
 * color survives even on blank/placeholder renders. No change to
 * resume_data.php required.
 */
$resumeTheme = $resume['theme'] ?? 'orange';

if (resume_is_blank($resume)) {
    $resume = ensure_placeholder_rows($resume);
}

$resume['theme'] = $resumeTheme;

$p = $resume['personal'];
?>
<div class="resume-page resume clean-academic theme-<?= htmlspecialchars($resume['theme'] ?? 'orange', ENT_QUOTES) ?>">

  <header class="ca-header">
    <h1 class="ca-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="ca-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    <p class="ca-contact">
      <?= ph($p['address'], 'address') ?> &nbsp;&middot;&nbsp; <?= ph($p['email'], 'email') ?> &nbsp;&middot;&nbsp; <?= ph($p['phone'], 'phone') ?>
      <?php if (!empty($p['portfolio'])): ?> &nbsp;&middot;&nbsp; <?= ph($p['portfolio'], 'portfolio') ?><?php endif; ?>
    </p>
  </header>
  <hr class="ca-rule">

  <section class="resume-section ca-section">
    <h2 class="ca-heading">Profile</h2>
    <?= phLines($resume['summary'], 'summary') ?>
  </section>

  <?php if (!empty($resume['education'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">Education</h2>
    <?php foreach ($resume['education'] as $edu): ?>
      <div class="resume-entry ca-entry">
        <div class="ca-entry-row">
          <span class="ca-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?> in <?= ph($edu['field'], 'field') ?><?php endif; ?>, <?= ph($edu['institution'], 'institution') ?></span>
          <span class="ca-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></span>
        </div>
        <?php if (!empty($edu['grade'])): ?><p class="ca-entry-sub">Grade: <?= ph($edu['grade'], 'grade') ?></p><?php endif; ?>
        <?php if (!empty($edu['description'])): ?><div class="ca-entry-desc"><?= phLines($edu['description'], 'description') ?></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['experience'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">Experience</h2>
    <?php foreach ($resume['experience'] as $job): ?>
      <div class="resume-entry ca-entry">
        <div class="ca-entry-row">
          <span class="ca-entry-title"><?= ph($job['position'], 'position') ?>, <?= ph($job['company'], 'company') ?></span>
          <span class="ca-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></span>
        </div>
        <div class="ca-entry-desc"><?= phLines($job['description'], 'description') ?></div>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['projects'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">Projects</h2>
    <?php foreach ($resume['projects'] as $proj): ?>
      <div class="resume-entry ca-entry">
        <span class="ca-entry-title"><?= ph($proj['name'], 'project_name') ?></span> &mdash; <?= ph($proj['technologies'], 'technologies') ?>
        <div class="ca-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['certifications'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">Certifications</h2>
    <ul class="ca-list">
      <?php foreach ($resume['certifications'] as $cert): ?>
        <li><?= ph($cert['name'], 'certification') ?>, <?= ph($cert['issuer'], 'issuer') ?><?php if (!empty($cert['year'])): ?>, <?= ph($cert['year'], 'year') ?><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['achievements'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">Achievements</h2>
    <ul class="ca-list">
      <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['skills']) || !empty($resume['languages']) || !empty($resume['interests'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">Skills, Languages &amp; Interests</h2>
    <?php if (!empty($resume['skills'])): ?><p><strong>Skills:</strong> <?= implode(', ', array_map(fn($s) => ph($s, 'skill'), $resume['skills'])) ?></p><?php endif; ?>
    <?php if (!empty($resume['languages'])): ?><p><strong>Languages:</strong> <?= implode(', ', array_map(fn($l) => ph($l['name'], 'language') . ' (' . ph($l['level'], 'level') . ')', $resume['languages'])) ?></p><?php endif; ?>
    <?php if (!empty($resume['interests'])): ?><p><strong>Interests:</strong> <?= implode(', ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p><?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['references'])): ?>
  <section class="resume-section ca-section">
    <h2 class="ca-heading">References</h2>
    <?php foreach ($resume['references'] as $ref): ?>
      <p class="ca-reference"><?= ph($ref['name'], 'reference_name') ?>, <?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?> &mdash; <?= ph($ref['email'], 'email') ?>, <?= ph($ref['phone'], 'phone') ?></p>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

</div>