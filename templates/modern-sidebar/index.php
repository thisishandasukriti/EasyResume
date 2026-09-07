<?php
/**
 * templates/modern-sidebar/index.php
 * Two-column layout: colored left sidebar (identity, contact, skills,
 * languages, interests) + main column (summary, experience, education,
 * projects, certifications, achievements, references).
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
<div class="resume-page resume modern-sidebar">

  <aside class="ms-sidebar">
    <?php if (!empty($p['profile_picture'])): ?>
      <img class="ms-avatar" src="<?= htmlspecialchars($p['profile_picture'], ENT_QUOTES) ?>" alt="">
    <?php endif; ?>

    <h1 class="ms-name"><?= ph($p['full_name'], 'full_name') ?></h1>
    <p class="ms-title"><?= ph($p['professional_title'], 'professional_title') ?></p>

    <div class="ms-block">
      <h2 class="ms-block-title">Contact</h2>
      <ul class="ms-contact">
        <li><span class="ms-label">Email</span><?= ph($p['email'], 'email') ?></li>
        <li><span class="ms-label">Phone</span><?= ph($p['phone'], 'phone') ?></li>
        <li><span class="ms-label">Location</span><?= ph($p['address'], 'address') ?></li>
        <li><span class="ms-label">LinkedIn</span><?= ph($p['linkedin'], 'linkedin') ?></li>
        <li><span class="ms-label">GitHub</span><?= ph($p['github'], 'github') ?></li>
        <li><span class="ms-label">Portfolio</span><?= ph($p['portfolio'], 'portfolio') ?></li>
      </ul>
    </div>

    <div class="ms-block">
      <h2 class="ms-block-title">Skills</h2>
      <ul class="ms-tags">
        <?php foreach ($resume['skills'] as $skill): ?>
          <li><?= ph($skill, 'skill') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <?php if (!empty($resume['languages'])): ?>
    <div class="ms-block">
      <h2 class="ms-block-title">Languages</h2>
      <ul class="ms-list-plain">
        <?php foreach ($resume['languages'] as $lang): ?>
          <li><?= ph($lang['name'], 'language') ?> <span class="ms-dim"><?= ph($lang['level'], 'level') ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($resume['interests'])): ?>
    <div class="ms-block">
      <h2 class="ms-block-title">Interests</h2>
      <ul class="ms-tags ms-tags-outline">
        <?php foreach ($resume['interests'] as $interest): ?>
          <li><?= ph($interest, 'interest') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </aside>

  <main class="ms-main">

    <section class="resume-section ms-section">
      <h2 class="ms-heading">Professional Summary</h2>
      <?= phLines($resume['summary'], 'summary') ?>
    </section>

    <?php if (!empty($resume['experience'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-heading">Experience</h2>
      <?php foreach ($resume['experience'] as $job): ?>
        <div class="resume-entry ms-entry">
          <div class="ms-entry-top">
            <div>
              <h3 class="ms-entry-title"><?= ph($job['position'], 'position') ?></h3>
              <p class="ms-entry-sub"><?= ph($job['company'], 'company') ?><?php if (!empty($job['location'])): ?> &middot; <?= ph($job['location'], 'location') ?><?php endif; ?></p>
            </div>
            <div class="ms-entry-date"><?= dateRange($job['start_date'], $job['end_date'], !empty($job['current'])) ?></div>
          </div>
          <div class="ms-entry-desc"><?= phLines($job['description'], 'description') ?></div>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['education'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-heading">Education</h2>
      <?php foreach ($resume['education'] as $edu): ?>
        <div class="resume-entry ms-entry">
          <div class="ms-entry-top">
            <div>
              <h3 class="ms-entry-title"><?= ph($edu['degree'], 'degree') ?><?php if (!empty($edu['field'])): ?>, <?= ph($edu['field'], 'field') ?><?php endif; ?></h3>
              <p class="ms-entry-sub"><?= ph($edu['institution'], 'institution') ?><?php if (!empty($edu['grade'])): ?> &middot; <?= ph($edu['grade'], 'grade') ?><?php endif; ?></p>
            </div>
            <div class="ms-entry-date"><?= dateRange($edu['start_date'], $edu['end_date']) ?></div>
          </div>
          <?php if (!empty($edu['description'])): ?><div class="ms-entry-desc"><?= phLines($edu['description'], 'description') ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['projects'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-heading">Projects</h2>
      <?php foreach ($resume['projects'] as $proj): ?>
        <div class="resume-entry ms-entry">
          <div class="ms-entry-top">
            <div>
              <h3 class="ms-entry-title"><?= ph($proj['name'], 'project_name') ?></h3>
              <p class="ms-entry-sub"><?= ph($proj['technologies'], 'technologies') ?></p>
            </div>
          </div>
          <div class="ms-entry-desc"><?= phLines($proj['description'], 'description') ?></div>
          <p class="ms-links">
            <?php if (!empty($proj['github'])): ?><span><?= ph($proj['github'], 'github') ?></span><?php endif; ?>
            <?php if (!empty($proj['live_link'])): ?><span><?= ph($proj['live_link'], 'live_link') ?></span><?php endif; ?>
          </p>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['certifications'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-heading">Certifications</h2>
      <ul class="ms-simple-list">
        <?php foreach ($resume['certifications'] as $cert): ?>
          <li><strong><?= ph($cert['name'], 'certification') ?></strong><?php if (!empty($cert['issuer'])): ?> &mdash; <?= ph($cert['issuer'], 'issuer') ?><?php endif; ?><?php if (!empty($cert['year'])): ?> (<?= ph($cert['year'], 'year') ?>)<?php endif; ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['achievements'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-heading">Achievements</h2>
      <ul class="ms-simple-list">
        <?php foreach ($resume['achievements'] as $item): ?>
          <li><?= ph($item, 'achievement') ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($resume['references'])): ?>
    <section class="resume-section ms-section">
      <h2 class="ms-heading">References</h2>
      <?php foreach ($resume['references'] as $ref): ?>
        <div class="ms-reference">
          <strong><?= ph($ref['name'], 'reference_name') ?></strong>
          <?= ph($ref['position'], 'position') ?> &middot; <?= ph($ref['company'], 'company') ?><br>
          <?= ph($ref['email'], 'email') ?> &middot; <?= ph($ref['phone'], 'phone') ?>
        </div>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

  </main>
</div>
