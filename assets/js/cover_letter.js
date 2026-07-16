/* ═══════════════════════════════════════════
   Cover Letter JS — Phase 5
   Depends on: ai_client.js
   ═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  // Resume pre-fill
  const resumeTA = document.getElementById('resume_text');
  if (resumeTA && !resumeTA.value) {
    const saved = AIClient.getSessionResume();
    if (saved) resumeTA.value = saved;
  }

  // Filename sanitizer
  const sanitizeFilename = (str = '') =>
    str
      .replace(/[<>:"/\\|?*]+/g, '')
      .replace(/\s+/g, '-')
      .toLowerCase();

  // ── Cover letter form ────────────────────
  const clForm = document.getElementById('cover-form');

  if (clForm) {
    clForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const btn = clForm.querySelector('[type=submit]');

      // Prevent double submissions
      if (btn?.disabled) {
        return;
      }

      // Collect + validate inputs
      const jobDescription =
        document.getElementById('job_description')?.value.trim() || '';

      const company =
        document.getElementById('company')?.value.trim() || '';

      const role =
        document.getElementById('role')?.value.trim() || '';

      const resume =
        document.getElementById('resume_text')?.value.trim() || '';

      if (!jobDescription || !company || !role) {
        AIClient.toast(
          'Job description, company, and role are required.',
          'error'
        );
        return;
      }

      if (jobDescription.length > 8000) {
        AIClient.toast(
          'Job description is too long.',
          'error'
        );
        return;
      }

      if (resume.length > 8000) {
        AIClient.toast(
          'Resume text is too long.',
          'error'
        );
        return;
      }

      if (btn) {
        btn.disabled = true;
      }

      AIClient.setLoading(btn, 'Generating cover letter…');

      try {

        // Save latest resume to session
        if (resume) {
          AIClient.saveSessionResume(resume);
        }

        const data = await AIClient.post('coverletter.php', {
          job_description: jobDescription,
          company,
          role,
          resume_text: resume,
        });

        if (!data?.letter) {
          throw new Error('No cover letter returned from server.');
        }

        const output = document.getElementById('letter-output');

        if (output) {
          output.classList.remove('empty');
          output.value = data.letter;
        }

        const resultWrap = document.getElementById('letter-result');

        if (resultWrap) {
          resultWrap.classList.remove('ai-hidden');
        }

        // Update meta
        setTextContent('letter-company', data.company);
        setTextContent('letter-role', data.role);

        resultWrap?.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });

        AIClient.toast(
          'Cover letter generated!',
          'success'
        );

      } catch (err) {

        AIClient.toast(
          err?.message || 'Failed to generate cover letter.',
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

  // ── Copy letter ──────────────────────────
  const copyBtn = document.getElementById('copy-letter');

  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      const output = document.getElementById('letter-output');

      if (!output?.value) {
        AIClient.toast(
          'Generate a cover letter first',
          'error'
        );
        return;
      }

      AIClient.copyText(output.value);
    });
  }

  // ── Download as .txt ─────────────────────
  const downloadBtn = document.getElementById('download-letter');

  if (downloadBtn) {
    downloadBtn.addEventListener('click', () => {

      const output = document.getElementById('letter-output');

      if (!output || !output.value) {
        AIClient.toast(
          'Generate a cover letter first',
          'error'
        );
        return;
      }

      const company =
        document.getElementById('company')?.value || 'company';

      const role =
        document.getElementById('role')?.value || 'role';

      const blob = new Blob(
        [output.value],
        { type: 'text/plain' }
      );

      const url = URL.createObjectURL(blob);

      const a = document.createElement('a');

      a.href = url;

      a.download =
        `cover-letter-${sanitizeFilename(role)
        }-${sanitizeFilename(company)
        }.txt`;

      document.body.appendChild(a);
      a.click();
      a.remove();

      URL.revokeObjectURL(url);

      AIClient.toast(
        'Downloaded!',
        'success'
      );
    });
  }

  // ── Regenerate ───────────────────────────
  const regenBtn = document.getElementById('regen-letter');

  if (regenBtn) {
    regenBtn.addEventListener('click', () => {
      clForm?.requestSubmit();
    });
  }

  function setTextContent(id, val) {
    const el = document.getElementById(id);

    if (el) {
      el.textContent = val ?? '';
    }
  }

});