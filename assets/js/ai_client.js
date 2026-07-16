/* ═══════════════════════════════════════════
   AI Client — Shared AJAX wrapper
   Used by all AI phase JS files.
   ═══════════════════════════════════════════ */
const AIClient = (() => {
  const API_BASE = 'api/';
  // Core fetch wrapper for all api/ endpoints
  async function post(endpoint, data = {}) {
    const form = new FormData();
    Object.entries(data).forEach(([k, v]) => form.append(k, v));
    const controller = new AbortController();
    const timeout = setTimeout(() => {
      controller.abort();
    }, 15000);
    let res;

    try {
      res = await fetch(
        API_BASE + endpoint,
        {
          method: 'POST',
          body: form,
          signal: controller.signal
        }
      );
    } catch (err) {
      clearTimeout(timeout);
      if (err.name === 'AbortError') {
        throw new Error('Request timed out. Please try again.');
      }
      throw err;
    } finally {
      clearTimeout(timeout);
    }
    let json;
    try {
      json = await res.json();
    }
    catch {
      throw new Error('Invalid server response');
    }
    if (!json.success) throw new Error(json.error || 'Unknown error');
    return json;
  }
  // ── Loading state helpers ────────────────
  function setLoading(btn, label = 'Analyzing…') {
    btn._originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="ai-spinner"></span> ${label}`;
  }
  function clearLoading(btn) {
    btn.disabled = false;
    btn.innerHTML = btn._originalHTML || btn.textContent;
  }
  // ── Toast notifications ──────────────────
  function toast(msg, type = 'info', duration = 3500) {
    let container = document.getElementById('ai-toast');
    if (!container) {
      container = document.createElement('div');
      container.id = 'ai-toast';
      document.body.appendChild(container);
    }
    const el = document.createElement('div');
    el.className = `toast-msg ${type}`;
    el.textContent = msg;
    container.appendChild(el);
    setTimeout(() => el.remove(), duration);
  }
  // ── Score ring animator ──────────────────
  function animateScoreRing(svgEl, score, color) {
    const fg = svgEl.querySelector('.score-ring-fg');
    const label = svgEl.parentElement.querySelector('.score-ring-label');
    const circumference = 283;
    if (!fg) return;
    fg.style.stroke = color || scoreColor(score);
    requestAnimationFrame(() => {
      fg.style.strokeDashoffset = circumference - (circumference * score / 100);
    });
    if (label) {
      let current = 0;
      const target = score;
      const step = target / 40;
      const ticker = setInterval(() => {
        current = Math.min(current + step, target);
        label.textContent = Math.round(current);
        if (current >= target) clearInterval(ticker);
      }, 30);
    }
  }
  function scoreColor(score) {
    if (score >= 75) return '#10b981';
    if (score >= 50) return '#f59e0b';
    return '#ef4444';
  }
  // ── Score bars animator ──────────────────
  function animateScoreBars() {
    document.querySelectorAll('.score-bar-fill').forEach(bar => {
      const target = bar.dataset.pct || '0';
      setTimeout(() => { bar.style.width = target + '%'; }, 100);
    });
  }
  // ── Copy to clipboard ────────────────────
  function copyText(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text)
        .then(() => toast('Copied to clipboard', 'success'))
        .catch(fallbackCopy);
    } else {
      fallbackCopy();
    }
    function fallbackCopy() {
      const area = document.createElement('textarea');
      area.value = text;
      document.body.appendChild(area);
      area.select();
      document.execCommand('copy');
      area.remove();
      toast('Copied to clipboard', 'success');
    }
  }
  // Get and parse the resume text from localStorage
  function getSessionResume() {
    const saved = localStorage.getItem('resumeData');
    if (!saved) return '';
    try {
      const data = JSON.parse(saved);
      if (typeof data === 'string') return data;
      if (data && data.resumeText) return data.resumeText;
      return '';
    } catch {
      return saved;
    }
  }
  // Save the resume text to localStorage
  function saveSessionResume(resume) {
    if (typeof resume === 'string') {
      try {
        const data = JSON.parse(resume);
        localStorage.setItem('resumeData', JSON.stringify(data));
      } catch {
        const saved = localStorage.getItem('resumeData');
        let resumeData = { resumeText: resume };
        if (saved) {
          try {
            const parsed = JSON.parse(saved);
            if (parsed && typeof parsed === 'object') {
              resumeData = { ...parsed, resumeText: resume };
            }
          } catch { }
        }
        localStorage.setItem('resumeData', JSON.stringify(resumeData));
      }
    } else {
      localStorage.setItem('resumeData', JSON.stringify(resume));
    }
  }
  return {
    post,
    setLoading,
    clearLoading,
    toast,
    animateScoreRing,
    animateScoreBars,
    copyText,
    scoreColor,
    getSessionResume,
    saveSessionResume
  };
})();