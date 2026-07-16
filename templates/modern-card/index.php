<?php
/**
 * templates/modern-card/index.php
 * Single column layout where every section is its own bordered "card"
 * with a colored left accent edge. Plain header row (no color block).
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
<div class="resume-page resume modern-card">

  <header class="mc-header">
    <div>
      <h1 class="mc-name"><?= ph($p['full_name'], 'full_name') ?></h1>
      <p class="mc-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    </div>
    <ul class="mc-contact">
      <li><?= ph($p['email'], 'email') ?></li>
      <li><?= ph($p['phone'], 'phone') ?></li>
      <li><?= ph($p['address'], 'address') ?></li>
      <?php if (!empty($p['linkedin'])): ?><li><?= ph($p['linkedin'], 'linkedin') ?></li><?php endif; ?>
      <?php if (!empty($p['portfolio'])): ?><li><?= ph($p['portfolio'], 'portfolio') ?></li><?php endif; ?>
    </ul>
  </header>

  <div class="mc-stack">

    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Summary</h2>
      <?= phLines($resume['summary'], 'summary') ?>
    </section>

    <?php if (!empty($resume['skills'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Skills</h2>
      <ul class="mc-tags">
        <?php foreach ($resume['skills'] as $skill): ?><li><?= ph($skill, 'skill') ?></li><?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['experience'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Experience</h2>
      <?php foreach ($resume['experience'] as $job): ?>
        <div class="resume-entry mc-entry">
          <div class="mc-entry-top">
            <div>
              <h3 class="mc-entry-title"><?= ph($job['position'], 'position') ?></h3>
              <p class="mc-entry-sub"><?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?> &middot; <?= ph($job['location'], 'location') ?><?php endif; ?></p>
            </div>
            <span class="mc-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></span>
          </div>
          <div class="mc-entry-desc"><?= phLines($job['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['education'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Education</h2>
      <?php foreach ($resume['education'] as $edu): ?>
        <div class="resume-entry mc-entry">
          <div class="mc-entry-top">
            <div>
              <h3 class="mc-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?></h3>
              <p class="mc-entry-sub"><?= ph($edu['institution'], 'institution') ?></p>
            </div>
            <span class="mc-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['projects'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Projects</h2>
      <?php foreach ($resume['projects'] as $proj): ?>
        <div class="resume-entry mc-entry">
          <h3 class="mc-entry-title"><?= ph($proj['name'], 'project_name') ?></h3>
          <p class="mc-entry-sub"><?= ph($proj['technologies'], 'technologies') ?></p>
          <div class="mc-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['certifications']) || !empty($resume['achievements'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Certifications &amp; Achievements</h2>
      <?php if (!empty($resume['certifications'])): ?>
      <ul class="mc-simple-list">
        <?php foreach ($resume['certifications'] as $cert): ?>
          <li><?= ph($cert['name'], 'certification') ?> <span class="mc-dim"><?= ph($cert['issuer'], 'issuer') ?><?php if (!empty($cert['year'])): ?>, <?= ph($cert['year'], 'year') ?><?php endif; ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php if (!empty($resume['achievements'])): ?>
      <ul class="mc-simple-list">
        <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['languages']) || !empty($resume['interests'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>Languages &amp; Interests</h2>
      <?php if (!empty($resume['languages'])): ?>
        <p class="mc-inline"><?= implode(' &middot; ', array_map(fn($l) => ph($l['name'], 'language') . ' (' . ph($l['level'], 'level') . ')', $resume['languages'])) ?></p>
      <?php endif; ?>
      <?php if (!empty($resume['interests'])): ?>
        <p class="mc-inline"><?= implode(' &middot; ', array_map(fn($i) => ph($i, 'interest'), $resume['interests'])) ?></p>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['references'])): ?>
    <section class="resume-section mc-card">
      <h2 class="mc-heading"><span class="mc-dot"></span>References</h2>
      <?php foreach ($resume['references'] as $ref): ?>
        <p class="mc-inline"><strong><?= ph($ref['name'], 'reference_name') ?></strong> &middot; <?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?> &middot; <?= ph($ref['email'], 'email') ?></p>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

  </div>
</div>
