<?php
/**
 * templates/contemporary-creative/index.php
 * A confident solid-color header band (name/title/contact in white),
 * then a single-column body below with pill-style skill tags and a
 * small colored marker before each section heading. Modern but kept
 * ATS-friendly: no icon fonts, no background images, no text-in-image.
 * Receives: $resume (see /resume_data.php). Presentation only.
 */
require_once dirname(__DIR__, 2) . '/resume_data.php';
if (!isset($resume) || !is_array($resume)) {
    $resume = get_empty_resume();
}

/**
 * Theme fix: ensure_placeholder_rows() (in resume_data.php) rebuilds
 * $resume from the canonical schema and does not know about the
 * 'theme' key, since theme is a presentation-layer addition rather
 * than part of resume_data.php's data model. Without this, the
 * selected accent color would silently revert to the default any
 * time blank/placeholder rows are inserted. We capture it beforehand
 * and reapply it after, so the protected helper never needs to know
 * 'theme' exists. No change to resume_data.php required.
 */
$resumeTheme = $resume['theme'] ?? 'orange';

if (resume_is_blank($resume)) {
    $resume = ensure_placeholder_rows($resume);
}

$resume['theme'] = $resumeTheme;

$p = $resume['personal'];
?>
<div class="resume-page resume contemporary-creative theme-<?= htmlspecialchars($resume['theme'] ?? 'orange', ENT_QUOTES) ?>">

  <header class="cc-band">
    <h1 class="cc-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="cc-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    <p class="cc-contact">
      <?= ph($p['email'], 'email') ?> &nbsp;&middot;&nbsp; <?= ph($p['phone'], 'phone') ?> &nbsp;&middot;&nbsp; <?= ph($p['address'], 'address') ?>
      <?php if (!empty($p['linkedin'])): ?> &nbsp;&middot;&nbsp; <?= ph($p['linkedin'], 'linkedin') ?><?php endif; ?>
      <?php if (!empty($p['github'])): ?> &nbsp;&middot;&nbsp; <?= ph($p['github'], 'github') ?><?php endif; ?>
      <?php if (!empty($p['portfolio'])): ?> &nbsp;&middot;&nbsp; <?= ph($p['portfolio'], 'portfolio') ?><?php endif; ?>
    </p>
  </header>

  <div class="cc-body">

    <section class="resume-section cc-section">
      <h2 class="cc-heading">Summary</h2>
      <?= phLines($resume['summary'], 'summary') ?>
    </section>

    <?php if (!empty($resume['skills'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Skills</h2>
      <ul class="cc-pills">
        <?php foreach ($resume['skills'] as $skill): ?><li><?= ph($skill, 'skill') ?></li><?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['experience'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Experience</h2>
      <?php foreach ($resume['experience'] as $job): ?>
        <div class="resume-entry cc-entry">
          <div class="cc-entry-top">
            <div>
              <h3 class="cc-entry-title"><?= ph($job['position'], 'position') ?></h3>
              <p class="cc-entry-sub"><?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?> &middot; <?= ph($job['location'], 'location') ?><?php endif; ?></p>
            </div>
            <span class="cc-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></span>
          </div>
          <div class="cc-entry-desc"><?= phLines($job['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['projects'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Projects</h2>
      <?php foreach ($resume['projects'] as $proj): ?>
        <div class="resume-entry cc-entry">
          <h3 class="cc-entry-title"><?= ph($proj['name'], 'project_name') ?></h3>
          <p class="cc-entry-sub"><?= ph($proj['technologies'], 'technologies') ?></p>
          <div class="cc-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['education'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Education</h2>
      <?php foreach ($resume['education'] as $edu): ?>
        <div class="resume-entry cc-entry">
          <div class="cc-entry-top">
            <div>
              <h3 class="cc-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?></h3>
              <p class="cc-entry-sub"><?= ph($edu['institution'], 'institution') ?></p>
            </div>
            <span class="cc-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['certifications'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Certifications</h2>
      <ul class="cc-simple-list">
        <?php foreach ($resume['certifications'] as $cert): ?>
          <li><?= ph($cert['name'], 'certification') ?> <span class="cc-dim"><?= ph($cert['issuer'], 'issuer') ?><?php if (!empty($cert['year'])): ?>, <?= ph($cert['year'], 'year') ?><?php endif; ?></span></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['achievements'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Achievements</h2>
      <ul class="cc-simple-list">
        <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['languages']) || !empty($resume['interests'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">Languages &amp; Interests</h2>
      <?php if (!empty($resume['languages'])): ?><p><?= implode(' &middot; ', array_map(fn($l) => ph($l['name'], 'language') . ' (' . ph($l['level'], 'level') . ')', $resume['languages'])) ?></p><?php endif; ?>
      <?php if (!empty($resume['interests'])): ?><p><?= implode(' &middot; ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p><?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['references'])): ?>
    <section class="resume-section cc-section">
      <h2 class="cc-heading">References</h2>
      <?php foreach ($resume['references'] as $ref): ?>
        <p><strong><?= ph($ref['name'], 'reference_name') ?></strong> &middot; <?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?> &middot; <?= ph($ref['email'], 'email') ?></p>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

  </div>
</div>