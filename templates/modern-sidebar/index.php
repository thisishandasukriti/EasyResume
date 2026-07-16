<?php
/**
 * templates/modern-sidebar/index.php
 * Two-column layout: a colored sidebar carries contact info, skills,
 * and languages; the main column carries summary, experience,
 * education, projects, certifications/achievements, and references.
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
<div class="resume-page resume modern-sidebar theme-<?= htmlspecialchars($resume['theme'] ?? 'orange', ENT_QUOTES) ?>">

  <aside class="ms-sidebar">

    <div class="ms-identity">
      <h1 class="ms-name"><?= ph($p['full_name'], 'full_name') ?></h1>
      <p class="ms-title"><?= ph($p['professional_title'], 'professional_title') ?></p>
    </div>

    <section class="ms-block">
      <h2 class="ms-heading">Contact</h2>
      <ul class="ms-contact">
        <li><?= ph($p['email'], 'email') ?></li>
        <li><?= ph($p['phone'], 'phone') ?></li>
        <li><?= ph($p['address'], 'address') ?></li>
        <?php if (!empty($p['linkedin'])): ?><li><?= ph($p['linkedin'], 'linkedin') ?></li><?php endif; ?>
        <?php if (!empty($p['portfolio'])): ?><li><?= ph($p['portfolio'], 'portfolio') ?></li><?php endif; ?>
      </ul>
    </section>

    <?php if (!empty($resume['skills'])): ?>
    <section class="ms-block">
      <h2 class="ms-heading">Skills</h2>
      <ul class="ms-tags">
        <?php foreach ($resume['skills'] as $skill): ?><li><?= ph($skill, 'skill') ?></li><?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['languages'])): ?>
    <section class="ms-block">
      <h2 class="ms-heading">Languages</h2>
      <ul class="ms-simple-list">
        <?php foreach ($resume['languages'] as $lang): ?>
          <li><?= ph($lang['name'], 'language') ?> <span class="ms-dim">&middot; <?= ph($lang['level'], 'level') ?></span></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['interests'])): ?>
    <section class="ms-block">
      <h2 class="ms-heading">Interests</h2>
      <ul class="ms-tags">
        <?php foreach ($resume['interests'] as $interest): ?><li><?= ph($interest, 'interest') ?></li><?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['certifications'])): ?>
    <section class="ms-block">
      <h2 class="ms-heading">Certifications</h2>
      <ul class="ms-simple-list">
        <?php foreach ($resume['certifications'] as $cert): ?>
          <li><?= ph($cert['name'], 'certification') ?><br><span class="ms-dim"><?= ph($cert['issuer'], 'issuer') ?><?php if (!empty($cert['year'])): ?>, <?= ph($cert['year'], 'year') ?><?php endif; ?></span></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

  </aside>

  <main class="ms-main">

    <section class="resume-section ms-section">
      <h2 class="ms-main-heading">Summary</h2>
      <?= phLines($resume['summary'], 'summary') ?>
    </section>

    <?php if (!empty($resume['experience'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-main-heading">Experience</h2>
      <?php foreach ($resume['experience'] as $job): ?>
        <div class="resume-entry ms-entry">
          <div class="ms-entry-top">
            <div>
              <h3 class="ms-entry-title"><?= ph($job['position'], 'position') ?></h3>
              <p class="ms-entry-sub"><?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?> &middot; <?= ph($job['location'], 'location') ?><?php endif; ?></p>
            </div>
            <span class="ms-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></span>
          </div>
          <div class="ms-entry-desc"><?= phLines($job['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['education'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-main-heading">Education</h2>
      <?php foreach ($resume['education'] as $edu): ?>
        <div class="resume-entry ms-entry">
          <div class="ms-entry-top">
            <div>
              <h3 class="ms-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?></h3>
              <p class="ms-entry-sub"><?= ph($edu['institution'], 'institution') ?></p>
            </div>
            <span class="ms-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['projects'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-main-heading">Projects</h2>
      <?php foreach ($resume['projects'] as $proj): ?>
        <div class="resume-entry ms-entry">
          <h3 class="ms-entry-title"><?= ph($proj['name'], 'project_name') ?></h3>
          <p class="ms-entry-sub"><?= ph($proj['technologies'], 'technologies') ?></p>
          <div class="ms-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['achievements'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-main-heading">Achievements</h2>
      <ul class="ms-simple-list-main">
        <?php foreach ($resume['achievements'] as $item): ?><li><?= ph($item, 'achievement') ?></li><?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['references'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-main-heading">References</h2>
      <?php foreach ($resume['references'] as $ref): ?>
        <p class="ms-inline"><strong><?= ph($ref['name'], 'reference_name') ?></strong> &middot; <?= ph($ref['position'], 'position') ?>, <?= ph($ref['company'], 'company') ?> &middot; <?= ph($ref['email'], 'email') ?></p>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

  </main>
</div>