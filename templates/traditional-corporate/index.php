<?php
/**
 * templates/traditional-corporate/index.php
 * Single-column, conservative layout: centered header block, then
 * full-width stacked sections with simple rule dividers. No color
 * blocks, no sidebar — built for banking/finance/legal ATS screening.
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
<div class="resume-page resume traditional-corporate">

  <header class="tc-header">
    <h1 class="tc-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="tc-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    <p class="tc-contact">
      <?= ph($p['address'], 'address') ?> &nbsp;|&nbsp;
      <?= ph($p['phone'], 'phone') ?> &nbsp;|&nbsp;
      <?= ph($p['email'], 'email') ?>
      <?php if (!empty($p['linkedin'])): ?> &nbsp;|&nbsp; <?= ph($p['linkedin'], 'linkedin') ?><?php endif; ?>
      <?php if (!empty($p['portfolio'])): ?> &nbsp;|&nbsp; <?= ph($p['portfolio'], 'portfolio') ?><?php endif; ?>
    </p>
  </header>

  <section class="resume-section tc-section">
    <h2 class="tc-heading">Professional Summary</h2>
    <?= phLines($resume['summary'], 'summary') ?>
  </section>

  <?php if (!empty($resume['experience'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Professional Experience</h2>
    <?php foreach ($resume['experience'] as $job): ?>
      <div class="resume-entry tc-entry">
        <div class="tc-entry-row">
          <strong class="tc-entry-title"><?= ph($job['position'], 'position') ?>, <?= ph($job['company'], 'company') ?></strong>
          <span class="tc-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></span>
        </div>
        <?php if (!empty($job['location'])): ?><p class="tc-entry-loc"><?= ph($job['location'], 'location') ?></p><?php endif; ?>
        <div class="tc-entry-desc"><?= phLines($job['description'], 'description') ?></div>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['education'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Education</h2>
    <?php foreach ($resume['education'] as $edu): ?>
      <div class="resume-entry tc-entry">
        <div class="tc-entry-row">
          <strong class="tc-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?> &mdash; <?= ph($edu['institution'], 'institution') ?></strong>
          <span class="tc-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></span>
        </div>
        <?php if (!empty($edu['grade'])): ?><p class="tc-entry-loc">Grade: <?= ph($edu['grade'], 'grade') ?></p><?php endif; ?>
        <?php if (!empty($edu['description'])): ?><div class="tc-entry-desc"><?= phLines($edu['description'], 'description') ?></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['skills'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Core Skills</h2>
    <p class="tc-inline-list"><?= implode(' &nbsp;&middot;&nbsp; ', array_map(fn($s) => ph($s, 'skill'), $resume['skills'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['projects'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Projects</h2>
    <?php foreach ($resume['projects'] as $proj): ?>
      <div class="resume-entry tc-entry">
        <strong class="tc-entry-title"><?= ph($proj['name'], 'project_name') ?></strong> &mdash; <span class="tc-entry-loc"><?= ph($proj['technologies'], 'technologies') ?></span>
        <div class="tc-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
      </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['certifications'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Certifications</h2>
    <ul class="tc-list">
      <?php foreach ($resume['certifications'] as $cert): ?>
        <li><?= ph($cert['name'], 'certification') ?><?php if (!empty($cert['issuer'])): ?>, <?= ph($cert['issuer'], 'issuer') ?><?php endif; ?><?php if (!empty($cert['year'])): ?> (<?= ph($cert['year'], 'year') ?>)<?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['achievements'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Achievements</h2>
    <ul class="tc-list">
      <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['languages'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Languages</h2>
    <p class="tc-inline-list"><?= implode(' &nbsp;&middot;&nbsp; ', array_map(fn($l) => ph($l['name'], 'language') . ' (' . ph($l['level'], 'level') . ')', $resume['languages'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['interests'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">Interests</h2>
    <p class="tc-inline-list"><?= implode(' &nbsp;&middot;&nbsp; ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p>
  </section>
  <?php endif; ?>

  <?php if (!empty($resume['references'])): ?>
  <section class="resume-section tc-section">
    <h2 class="tc-heading">References</h2>
    <?php foreach ($resume['references'] as $ref): ?>
      <p class="tc-reference"><strong><?= ph($ref['name'], 'reference_name') ?></strong>, <?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?> &mdash; <?= ph($ref['email'], 'email') ?>, <?= ph($ref['phone'], 'phone') ?></p>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

</div>
