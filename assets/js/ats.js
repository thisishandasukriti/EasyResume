/* ═══════════════════════════════════════════
   ATS Checker JS — Phase 1 & 2
   Depends on: ai_client.js
   ═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  // ── Pre-fill from session ────────────────
  const resumeTA = document.getElementById('resume_text');

  if (resumeTA && !resumeTA.value) {
    const saved = localStorage.getItem('resumeData');

    if (saved) {
      resumeTA.value = saved;
    }
  }

  // ── ATS Score form ───────────────────────
  const atsForm = document.getElementById('ats-form');
  if (atsForm) {
    atsForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const resume =
        document.getElementById('resume_text')?.value.trim();

      const jd =
        document.getElementById('job_description')?.value.trim();

      if (!resume) {
        AIClient.toast('Please enter your resume', 'error');
        return;
      }

      if (!jd) {
        AIClient.toast('Please enter a job description', 'error');
        return;
      }

      const btn = atsForm.querySelector('[type=submit]');
      AIClient.setLoading(btn, 'Checking ATS score…');

      // Persist resume
      localStorage.setItem('resumeData', resume);

      try {
        const data = await AIClient.post('ats_score.php', {
          resume_text: resume,
          job_description: jd,
        });
        renderATSResults(data);
      } catch (err) {
        AIClient.toast(err.message, 'error');
      } finally {
        AIClient.clearLoading(btn);
      }
    });
  }

  // ── Render ATS results ───────────────────
  function renderATSResults(data) {
    const wrap = document.getElementById('ats-results');
    if (!wrap) return;
    wrap.classList.remove('ai-hidden');
    wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Score ring
    const ring = document.getElementById('score-ring-svg');
    if (ring) AIClient.animateScoreRing(ring, data.score);

    // Score label
    const scoreLabel = document.getElementById('score-label');
    if (scoreLabel) scoreLabel.textContent = data.score;

    // Score description
    const scoreDesc = document.getElementById('score-desc');
    if (scoreDesc) {
      const descriptions = {
        high: { text: 'Excellent match — strong ATS performance', color: '#10b981' },
        mid: { text: 'Moderate match — room to improve', color: '#f59e0b' },
        low: { text: 'Low match — significant optimization needed', color: '#ef4444' },
      };
      const tier = data.score >= 75 ? 'high' : data.score >= 50 ? 'mid' : 'low';
      scoreDesc.textContent = descriptions[tier].text;
      scoreDesc.style.color = descriptions[tier].color;
    }

    // Stats
    setTextContent('stat-jd-keywords', data.stats.jd_keywords);
    setTextContent('stat-resume-keywords', data.stats.resume_keywords);
    setTextContent('stat-matched', data.stats.matched);
    setTextContent('stat-match-pct', data.match_percent + '%');

    // Keyword clouds
    renderKeywords('matched-keywords', data.matched_keywords, 'match');
    renderKeywords('missing-keywords', data.missing_keywords, 'miss');
    renderKeywords('important-missing', data.important_missing, 'important');

    // Strengths & weaknesses
    renderSWList('strengths-list', data.strengths, 'strength', '✓');
    renderSWList('weaknesses-list', data.weaknesses, 'weakness', '✗');

    // Score bar for match
    const matchBar = document.querySelector('[data-bar="match"]');
    if (matchBar) {
      matchBar.dataset.pct = data.match_percent;
      AIClient.animateScoreBars();
    }
  }

  function renderKeywords(containerId, keywords, type) {
    const el = document.getElementById(containerId);

    if (!el || !keywords) return;

    el.innerHTML = '';

    if (!keywords.length) {
      const empty = document.createElement('span');
      empty.className = 'ai-text-muted ai-text-sm';
      empty.textContent = 'None detected';
      el.appendChild(empty);
      return;
    }

    keywords.forEach((k) => {
      const badge = document.createElement('span');
      badge.className = `kw-badge ${type}`;
      badge.textContent = k;
      el.appendChild(badge);
    });
  }

  function renderSWList(containerId, items, cls, icon) {
    const el = document.getElementById(containerId);

    if (!el || !items) return;

    el.innerHTML = '';

    items.forEach((item) => {
      const row = document.createElement('div');
      row.className = `sw-item ${cls}`;

      const iconEl = document.createElement('span');
      iconEl.className = 'sw-icon';
      iconEl.textContent = icon;

      const textEl = document.createElement('span');
      textEl.textContent = item;

      row.appendChild(iconEl);
      row.appendChild(textEl);

      el.appendChild(row);
    });
  }

  function setTextContent(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  // ── AI Improve form ──────────────────────
  const improveForm = document.getElementById('improve-form');
  if (improveForm) {
    improveForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = improveForm.querySelector('[type=submit]');
      AIClient.setLoading(btn, 'Improving…');

      try {
        const data = await AIClient.post('improve.php', {
          text: document.getElementById('improve_text').value,
          mode: document.getElementById('improve_mode')?.value || 'bullet',
        });

        const output = document.getElementById('improved-output');
        if (output) {
          output.classList.remove('empty');
          output.textContent = data.improved;
        }

        const original = document.getElementById('original-output');
        if (original) original.textContent = data.original;

        const diffWrap = document.getElementById('diff-wrap');
        if (diffWrap) diffWrap.classList.remove('ai-hidden');

        const copyBtn = document.getElementById('copy-improved');
        if (copyBtn) copyBtn.dataset.text = data.improved;

        AIClient.toast('Content improved!', 'success');
      } catch (err) {
        AIClient.toast(err.message, 'error');
      } finally {
        AIClient.clearLoading(btn);
      }
    });
  }

  // ── Copy button handler ──────────────────
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-copy]');
    if (btn) {
      const target = document.getElementById(btn.dataset.copy);
      if (target) AIClient.copyText(target.textContent || target.value);
    }
    const btn2 = e.target.closest('[data-text]');
    if (btn2 && btn2.id === 'copy-improved') {
      AIClient.copyText(btn2.dataset.text);
    }
  });

});
