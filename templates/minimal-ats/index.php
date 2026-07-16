<?php
/**
 * templates/minimal-ats/index.php
 * Pure single-column, zero graphics, zero color blocks. No avatar is
 * ever rendered (even if provided) — this template optimizes solely
 * for reliable automated parsing. Skills/languages/interests render
 * as plain comma-separated text rather than tag/pill lists.
 * Receives: $resume (see /resume_data.php). Presentation only.
 */
require_once dirname(__DIR__, 2) . '/resume_data.php';
if (!isset($resume) || !is_array($resume)) {
    $resume = get_empty_resume();
}
if (resume_is_blank($resume)) {
    $resume = ensure_placeholder_rows($resume);
}
$p = $resume['personal'];
?>
<div class="resume-page resume minimal-ats">

  <header class="ma-header">
    <h1 class="ma-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="ma-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    <p class="ma-contact">
      <?= ph($p['phone'], 'phone') ?> | <?= ph($p['email'], 'email') ?> | <?= ph($p['address'], 'address') ?><?php if (!empty($p['linkedin'])): ?> | <?= ph($p['linkedin'], 'linkedin') ?><?php endif; ?><?php if (!empty($p['github'])): ?> | <?= ph($p['github'], 'github') ?><?php endif; ?><?php if (!empty($p['portfolio'])): ?> | <?= ph($p['portfolio'], 'portfolio') ?><?php endif; ?>
    </p>
  </header>

  <section class="resume-section ma-section">
    <h2 class="ma-heading">Summary</h2>
    <?= phLines($resume['summary'], 'summary') ?>
  </section>

  <?php if (!empty($resume['skills'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Skills</h2>
    <p><?= implode(', ', array_map(fn($s) => ph($s, 'skill'), $resume['skills'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['experience'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Experience</h2>
    <?php foreach ($resume['experience'] as $job): ?>
      <div class="resume-entry ma-entry">
        <p class="ma-entry-line"><strong><?= ph($job['position'], 'position') ?></strong> &mdash; <?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?>, <?= ph($job['location'], 'location') ?><?php endif; ?> (<?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?>)</p>
        <?= phLines($job['description'], 'description') ?>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['education'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Education</h2>
    <?php foreach ($resume['education'] as $edu): ?>
      <div class="resume-entry ma-entry">
        <p class="ma-entry-line"><strong><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?></strong> &mdash; <?= ph($edu['institution'], 'institution') ?> (<?= dateRange($edu['start_date'], $edu['end_date']) ?>)</p>
        <?php if (!empty($edu['grade'])): ?><p>Grade: <?= ph($edu['grade'], 'grade') ?></p><?php endif; ?>
        <?php if (!empty($edu['description'])): ?><?= phLines($edu['description'], 'description') ?><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['projects'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Projects</h2>
    <?php foreach ($resume['projects'] as $proj): ?>
      <div class="resume-entry ma-entry">
        <p class="ma-entry-line"><strong><?= ph($proj['name'], 'project_name') ?></strong> &mdash; <?= ph($proj['technologies'], 'technologies') ?></p>
        <?= phLines($proj['description'], 'description') ?>
        <?php if (!empty($proj['github']) || !empty($proj['live_link'])): ?>
          <p><?= ph($proj['github'], 'github') ?><?php if (!empty($proj['github']) && !empty($proj['live_link'])): ?> | <?php endif; ?><?= ph($proj['live_link'], 'live_link') ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['certifications'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Certifications</h2>
    <p><?= implode('; ', array_map(fn($c) => ph($c['name'], 'certification') . (!empty($c['year']) ? ' (' . ph($c['year'], 'year') . ')' : ''), $resume['certifications'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['achievements'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Achievements</h2>
    <?php foreach ($resume['achievements'] as $item): ?><p>&bull; <?= ph($item, 'achievement') ?></p><?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['languages'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Languages</h2>
    <p><?= implode(', ', array_map(fn($l) => ph($l['name'], 'language') . ' (' . ph($l['level'], 'level') . ')', $resume['languages'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['interests'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">Interests</h2>
    <p><?= implode(', ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['references'])): ?>
  <section class="resume-section ma-section">
    <h2 class="ma-heading">References</h2>
    <?php foreach ($resume['references'] as $ref): ?>
      <p><?= ph($ref['name'], 'reference_name') ?>, <?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?> &mdash; <?= ph($ref['email'], 'email') ?>, <?= ph($ref['phone'], 'phone') ?></p>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

</div>
