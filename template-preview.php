<?php
/**
 * template-preview.php
 * ----------------------------------------------------------------------
 * Renders ONE template as a complete, standalone HTML document.
 *
 * Sprint 2 / Phase 2: theme switching is now INSTANT and client-side.
 * Clicking a theme option no longer triggers a page reload or PHP
 * request — JS simply swaps the `theme-*` class on the root resume
 * element. The global stylesheet (assets/resume-themes.css) recolors
 * everything immediately via CSS variables. Persistence to the session
 * happens via a small async fetch() to assets/save-theme.php, fired
 * after the swap so the UI never waits on the network.
 *
 * Template switching still works the old way (full reload via <select>
 * onchange) since loading a different template's HTML/layout requires
 * a server round-trip regardless — only THEME switching needed to
 * become instant per the brief.
 *
 * Query params: same as before (template, mode, toolbar, theme).
 * `theme` is still read server-side on load so the initial render is
 * already correct (no flash of default colors before JS runs).
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/resume_data.php';
require_once __DIR__ . '/resume_renderer.php';

$slug = $_GET['template'] ?? '';
$mode = $_GET['mode'] ?? 'blank';
$showToolbar = !empty($_GET['toolbar']);

if (get_template_meta($slug) === null) {
    http_response_code(404);
    echo 'Unknown template.';
    exit;
}

switch ($mode) {
    case 'sample':
        $resume = get_sample_resume();
        break;
    case 'live':
        $resume = isset($_SESSION['resume']) ? normalize_resume($_SESSION['resume']) : get_empty_resume();
        break;
    default:
        $resume = get_empty_resume();
        break;
}

/**
 * Centralized theme metadata — id => [name, category, swatch]. The full
 * color palette for each id lives in assets/resume-themes.css, not here.
 * Adding a theme means editing exactly those two files; this file never
 * needs to change.
 */
$availableThemes = require __DIR__ . '/assets/resume_themes.php';

$currentTheme = $resume['theme'] ?? 'executive';
if (isset($_GET['theme']) && isset($availableThemes[$_GET['theme']])) {
    $currentTheme = $_GET['theme'];
}
if (!isset($availableThemes[$currentTheme])) {
    $currentTheme = array_key_first($availableThemes);
}
$resume['theme'] = $currentTheme;

// Persist on initial load too (covers direct links / template switches
// that still go through a full reload), same as before.
if ($mode === 'live' && isset($_SESSION['resume'])) {
    $_SESSION['resume']['theme'] = $currentTheme;
}

$meta = get_template_meta($slug);
$stylePath = resume_template_style_path($slug);
$allTemplates = require __DIR__ . '/templates_registry.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($meta['name'], ENT_QUOTES) ?> — Resume Preview</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Roboto:wght@400;500;700&family=Source+Sans+Pro:wght@400;600;700&family=Lato:wght@400;700&family=Lora:wght@500;600&family=Source+Serif+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/resume-base.css">
<link rel="stylesheet" href="assets/resume-themes.css">
<link rel="stylesheet" href="<?= htmlspecialchars($stylePath, ENT_QUOTES) ?>">
<?php if ($showToolbar): ?>
<link rel="stylesheet" href="assets/builder.css">
<style>
  .preview-toolbar {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 13px;
    line-height: 1.2;
    background: #ffffff;
    border-bottom: 1px solid #E5E7EB;
  }
  .preview-toolbar-inner {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 10px 20px;
    max-width: 1100px;
    margin: 0 auto;
  }
  .preview-toolbar .pt-btn {
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    border-radius: 6px;
    padding: 7px 14px;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
  }
  .preview-toolbar .pt-btn-ghost { color: #374151; border-color: #D1D5DB; background: #fff; }
  .preview-toolbar .pt-btn-ghost:hover { background: #F9FAFB; }
  .preview-toolbar .pt-btn-primary { color: #fff; background: #111827; margin-left: auto; }
  .preview-toolbar .pt-btn-primary:hover { background: #1F2937; }
  .preview-toolbar .pt-switcher {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #374151;
  }
  .preview-toolbar .pt-switcher select {
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    color: #111827;
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    appearance: none;
    -webkit-appearance: none;
    border: 1.5px solid #E5E7EB;
    border-radius: 8px;
    padding: 6px 32px 6px 10px;
    max-width: 200px;
    cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  }
  .preview-toolbar .pt-switcher select:hover {
    border-color: #D1D5DB;
    box-shadow: 0 2px 6px rgba(0,0,0,0.07);
  }
  .preview-toolbar .pt-switcher select:focus {
    outline: none;
    border-color: #6366F1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
  }
  .preview-toolbar .pt-switcher label {
    font-size: 12px;
    font-weight: 500;
    color: #6B7280;
    white-space: nowrap;
  }
  .preview-toolbar .pt-save-hint {
    font-size: 11px;
    color: #9CA3AF;
    margin-left: 4px;
    opacity: 0;
    transition: opacity .2s ease;
  }
  .preview-toolbar .pt-save-hint.is-visible { opacity: 1; }
</style>
<?php endif; ?>
</head>
<body class="resume-preview-screen<?= $mode === 'live' ? ' mode-live' : '' ?>">

<?php if ($showToolbar): ?>
  <div class="preview-toolbar no-print">
    <div class="preview-toolbar-inner">
      <a class="pt-btn pt-btn-ghost" href="resume-builder.php?template=<?= urlencode($slug) ?>">&larr; Edit details</a>

      <form class="pt-switcher" method="get" action="template-preview.php">
        <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES) ?>">
        <input type="hidden" name="toolbar" value="1">
        <input type="hidden" name="theme" id="pt-theme-carry" value="<?= htmlspecialchars($currentTheme, ENT_QUOTES) ?>">
        <label for="pt-template-select">Template</label>
        <select id="pt-template-select" name="template" onchange="this.form.submit()">
          <?php foreach ($allTemplates as $optSlug => $optMeta): ?>
            <option value="<?= htmlspecialchars($optSlug, ENT_QUOTES) ?>" <?= $optSlug === $slug ? 'selected' : '' ?>>
              <?= htmlspecialchars($optMeta['name'], ENT_QUOTES) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <div class="pt-switcher" id="pt-theme-group">
        <select id="pt-theme-select" aria-label="Resume color theme">
          <?php foreach ($availableThemes as $themeId => $themeMeta): ?>
            <option
              value="<?= htmlspecialchars($themeId, ENT_QUOTES) ?>"
              data-swatch="<?= htmlspecialchars($themeMeta['swatch'], ENT_QUOTES) ?>"
              <?= $themeId === $currentTheme ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($themeMeta['name'], ENT_QUOTES) ?> — <?= htmlspecialchars($themeMeta['category'], ENT_QUOTES) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <span class="pt-save-hint" id="pt-save-hint">Saved</span>
      </div>

      <button class="pt-btn pt-btn-primary" type="button" onclick="window.print()">Download / Print PDF</button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var select   = document.getElementById('pt-theme-select');
      var carry    = document.getElementById('pt-theme-carry'); // keeps template-switch <select> in sync
      var saveHint = document.getElementById('pt-save-hint');
      var resumeEl = document.querySelector('.resume-page');
      var mode     = <?= json_encode($mode) ?>;
      var saveHintTimer = null;

      if (!select || !resumeEl) {
        return;
      }

      function applyTheme(themeId) {
        // Strip any existing theme-* class, add the new one. No reload,
        // no rerender — pure class swap; CSS variables do the rest.
        resumeEl.className = resumeEl.className
          .split(/\s+/)
          .filter(function (c) { return c.indexOf('theme-') !== 0; })
          .concat('theme-' + themeId)
          .join(' ');
      }

      function persistTheme(themeId) {
        if (mode !== 'live') {
          return; // sample/blank previews have nothing to persist
        }
        fetch('assets/save-theme.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ theme: themeId })
        }).then(function () {
          if (saveHint) {
            saveHint.classList.add('is-visible');
            clearTimeout(saveHintTimer);
            saveHintTimer = setTimeout(function () {
              saveHint.classList.remove('is-visible');
            }, 1200);
          }
        }).catch(function () {
          // Non-fatal: theme still applied visually this session, just
          // won't survive a future visit if this request failed.
        });
      }

      select.addEventListener('change', function () {
        var themeId = select.value;

        applyTheme(themeId);

        if (carry) {
          carry.value = themeId; // so switching template right after keeps this theme
        }

        persistTheme(themeId);
      });
    });
  </script>
<?php endif; ?>

<?php render_resume_template($slug, $resume); ?>

<?php if ($mode === 'live'): ?>
<script>
/* Clean up plain-text {{placeholder}} tokens that CSS cannot reach.
   ph() output is already hidden by .mode-live .ph { display:none } in
   resume-base.css. This script handles dateRange() output, which emits
   {{start_date}} / {{end_date}} as raw text nodes (no .ph wrapper). */
document.addEventListener('DOMContentLoaded', function () {
  var resumeEl = document.querySelector('.resume-page');
  if (!resumeEl) return;

  /* 1. Walk every text node — replace {{token}} with empty string and
        clean up any orphaned separators left behind (e.g. " – 2024"
        becomes "2024" when start_date was blank in school mode). */
  var walker = document.createTreeWalker(resumeEl, NodeFilter.SHOW_TEXT, null, false);
  var textNodes = [];
  while (walker.nextNode()) textNodes.push(walker.currentNode);

  textNodes.forEach(function (node) {
    if (node.textContent.indexOf('{{') === -1) return;
    var cleaned = node.textContent
      .replace(/\{\{[^}]+\}\}/g, '')   /* strip all {{token}} */
      .replace(/^\s*[–\-·]\s*/,  '')   /* leading separator   */
      .replace(/\s*[–\-·]\s*$/,  '')   /* trailing separator  */
      .replace(/\s{2,}/g, ' ');        /* collapse whitespace */
    node.textContent = cleaned;
  });

  /* 2. After replacement, hide leaf elements whose visible text is now
        empty or only punctuation (catches date-range spans, contact
        separators, etc. that collapsed to nothing). */
  resumeEl.querySelectorAll('*').forEach(function (el) {
    if (el.children.length > 0) return;
    var text = el.textContent.trim();
    if (text === '' || /^[–\-·&;\s]+$/.test(text)) {
      el.style.display = 'none';
    }
  });

  /* 3. Hide entire resume sections where every content element is
        now invisible (handles optional sections left fully blank). */
  resumeEl.querySelectorAll('.resume-section').forEach(function (section) {
    var hasVisible = false;
    section.querySelectorAll('*').forEach(function (el) {
      if (hasVisible) return;
      var tag = el.tagName;
      if (tag === 'H1' || tag === 'H2' || tag === 'H3' || tag === 'HR') return;
      if (el.classList.contains('ph')) return;
      if (el.style.display === 'none') return;
      if (el.children.length > 0) return; /* check leaf nodes only */
      if (el.textContent.trim()) hasVisible = true;
    });
    if (!hasVisible) section.style.display = 'none';
  });
});
</script>
<?php endif; ?>

</body>
</html>