/* ═══════════════════════════════════════════
   Role Optimizer JS — Phase 4
   Depends on: ai_client.js
   ═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  let selectedRole = null;
  let scoreTicker = null;

  // ── Resume pre-fill ──────────────────────
  const resumeTA = document.getElementById('resume_text');

  if (resumeTA && !resumeTA.value) {
    const saved = AIClient.getSessionResume();
    if (saved) {
      resumeTA.value = saved;
    }
  }

  // ── Role card selection ──────────────────
  document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {

      document.querySelectorAll('.role-card')
        .forEach(c => c.classList.remove('selected'));

      card.classList.add('selected');

      selectedRole = card.dataset.role;

      const roleKeyInput = document.getElementById('role_key');
      if (roleKeyInput) {
        roleKeyInput.value = selectedRole;
      }

      const analyzeBtn = document.getElementById('analyze-btn');

      if (analyzeBtn) {
        analyzeBtn.disabled = false;
      }
    });
  });

  // ── Analysis form ────────────────────────
  const roleForm = document.getElementById('role-form');

  if (roleForm) {

    roleForm.addEventListener('submit', async (e) => {

      e.preventDefault();

      if (!selectedRole) {
        AIClient.toast(
          'Please select a target role first',
          'error'
        );
        return;
      }

      const resumeText =
        document.getElementById('resume_text')?.value.trim() || '';

      if (!resumeText) {
        AIClient.toast(
          'Please paste your resume first',
          'error'
        );
        return;
      }

      if (resumeText.length > 8000) {
        AIClient.toast(
          'Resume text is too long. Please shorten it.',
          'error'
        );
        return;
      }

      const btn = document.getElementById('analyze-btn');

      /* Prevent accidental double submissions */
      if (btn?.disabled) {
        return;
      }

      if (btn) {
        btn.disabled = true;
      }

      AIClient.setLoading(btn, 'Analyzing fit…');

      if (resumeTA) {
        AIClient.saveSessionResume(resumeText);
      }

      try {

        const data = await AIClient.post('role_fit.php', {
          resume_text: resumeText,
          role_key: selectedRole,
        });

        renderRoleResults(data);

      } catch (err) {

        AIClient.toast(
          err.message || 'Unable to analyze role fit',
          'error'
        );

      } finally {

        AIClient.clearLoading(btn);

        if (btn) {
          btn.disabled = false;
        }
      }
    });
  }

  // ── Render role fit results ──────────────
  function renderRoleResults(data) {

    const wrap = document.getElementById('role-results');

    if (!wrap) {
      return;
    }

    wrap.classList.remove('ai-hidden');

    wrap.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });

    // Fit score animation
    const scoreEl =
      document.getElementById('fit-score-number');

    if (scoreEl) {

      clearInterval(scoreTicker);

      let current = 0;

      scoreTicker = setInterval(() => {

        current = Math.min(
          current + 2,
          data.fit_score
        );

        scoreEl.textContent = current + '%';

        scoreEl.style.color =
          AIClient.scoreColor(data.fit_score);

        if (current >= data.fit_score) {
          clearInterval(scoreTicker);
        }

      }, 25);
    }

    // Statistics
    setTextContent('fit-role-name', data.role);

    setTextContent(
      'fit-matched-count',
      data.stats?.matched ?? '—'
    );

    setTextContent(
      'fit-missing-count',
      data.stats?.missing ?? '—'
    );

    setTextContent(
      'fit-total-count',
      data.stats?.total_required ?? '—'
    );

    // Match bar
    const matchBar =
      document.querySelector('[data-bar="fit"]');

    if (matchBar) {

      matchBar.dataset.pct = data.fit_score;

      setTimeout(() => {
        matchBar.style.width =
          data.fit_score + '%';
      }, 100);
    }

    // Matched skills
    const matchedEl =
      document.getElementById('matched-skills');

    if (matchedEl) {

      matchedEl.innerHTML = '';

      (data.matched_skills || []).forEach(skill => {

        const badge =
          document.createElement('span');

        badge.className =
          'kw-badge match';

        badge.textContent =
          `✓ ${skill}`;

        matchedEl.appendChild(badge);
      });
    }

    // Missing skills
    const missingEl =
      document.getElementById('missing-skills');

    if (missingEl) {

      missingEl.innerHTML = '';

      (data.missing_skills || []).forEach((skill, index) => {

        const badge =
          document.createElement('span');

        badge.className =
          `kw-badge ${index < 8
            ? 'important'
            : 'miss'
          }`;

        badge.textContent = skill;

        missingEl.appendChild(badge);
      });
    }

    // AI Recommendations
    const recsEl =
      document.getElementById(
        'recommendations-output'
      );

    if (recsEl) {

      recsEl.classList.remove('empty');

      recsEl.textContent =
        data.recommendations ||
        'No recommendations available.';
    }
  }

  // ── Utility ──────────────────────────────
  function setTextContent(id, value) {

    const el =
      document.getElementById(id);

    if (el) {
      el.textContent = value;
    }
  }

});