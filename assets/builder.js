/* ----------------------------------------------------------------------
 * assets/builder.js
 * 1. Repeatable groups — clone/remove/reindex + collapse toggle
 * 2. Tag inputs (skills / achievements / interests)
 * 3. End-date disable when "currently working" checked
 * 4. Skills autosuggest (assets/skills-autocomplete.php)
 * 5. Background autosave (assets/autosave.php)
 * 6. File upload filename preview; date-order hint
 * 7. Education type toggle (College / School label swap)
 * 8. Phone hidden-field sync
 * 9. Progress meter (sidebar bar + footer %)
 * ---------------------------------------------------------------------- */

document.addEventListener('DOMContentLoaded', function () {

  var form = document.querySelector('.bf-layout');

  /* ═══════════════════════════════════════════════════════════════
   * 9. Progress meter
   * Counts 11 sections; updates sidebar bar + both % labels.
   * Called on load and inside scheduleAutosave so it stays live.
   * ═══════════════════════════════════════════════════════════════ */
  function calculateProgress() {
    if (!form) return 0;
    var filled = 0;

    var nameEl = form.querySelector('[name="personal[full_name]"]');
    if (nameEl && nameEl.value.trim()) filled++;

    var summaryEl = form.querySelector('[name="summary"]');
    if (summaryEl && summaryEl.value.trim()) filled++;

    if (form.querySelectorAll('[name="skills[]"]').length > 0) filled++;

    var hasExp = false;
    form.querySelectorAll('[data-repeat-group="experience"] [data-repeat-item]').forEach(function (item) {
      var pos = item.querySelector('[name*="[position]"]');
      var comp = item.querySelector('[name*="[company]"]');
      if ((pos && pos.value.trim()) || (comp && comp.value.trim())) hasExp = true;
    });
    if (hasExp) filled++;

    var hasEdu = false;
    form.querySelectorAll('[data-repeat-group="education"] [data-repeat-item]').forEach(function (item) {
      var inst = item.querySelector('[name*="[institution]"]');
      var deg = item.querySelector('[name*="[degree]"]');
      if ((inst && inst.value.trim()) || (deg && deg.value.trim())) hasEdu = true;
    });
    if (hasEdu) filled++;

    var hasProj = false;
    form.querySelectorAll('[data-repeat-group="projects"] [data-repeat-item]').forEach(function (item) {
      var n = item.querySelector('[name*="[name]"]');
      if (n && n.value.trim()) hasProj = true;
    });
    if (hasProj) filled++;

    var hasCert = false;
    form.querySelectorAll('[data-repeat-group="certifications"] [data-repeat-item]').forEach(function (item) {
      var n = item.querySelector('[name*="[name]"]');
      if (n && n.value.trim()) hasCert = true;
    });
    if (hasCert) filled++;

    if (form.querySelectorAll('[name="achievements[]"]').length > 0) filled++;

    var hasLang = false;
    form.querySelectorAll('[data-repeat-group="languages"] [data-repeat-item]').forEach(function (item) {
      var n = item.querySelector('[name*="[name]"]');
      if (n && n.value.trim()) hasLang = true;
    });
    if (hasLang) filled++;

    if (form.querySelectorAll('[name="interests[]"]').length > 0) filled++;

    var hasRef = false;
    form.querySelectorAll('[data-repeat-group="references"] [data-repeat-item]').forEach(function (item) {
      var n = item.querySelector('[name*="[name]"]');
      if (n && n.value.trim()) hasRef = true;
    });
    if (hasRef) filled++;

    return Math.round((filled / 11) * 100);
  }

  function updateProgress() {
    var pct = calculateProgress();
    var fill = document.querySelector('[data-progress-fill]');
    var pctEl = document.querySelector('[data-progress-percent]');
    var footerPct = document.querySelector('[data-footer-progress-percent]');
    if (fill) fill.style.width = pct + '%';
    if (pctEl) pctEl.textContent = pct;
    if (footerPct) footerPct.textContent = pct;
  }

  /* ═══════════════════════════════════════════════════════════════
   * 7. Education type toggle
   * ═══════════════════════════════════════════════════════════════ */
  function applyEduType(item, type) {
    var isSchool = type === 'school';
    item.querySelectorAll('[data-edu-field]').forEach(function (field) {
      if (field.dataset.hideOnSchool === 'true') {
        field.style.display = isSchool ? 'none' : '';
        field.querySelectorAll('input, textarea, select').forEach(function (el) {
          el.disabled = isSchool;
        });
      }
      var labelKey = isSchool ? 'labelSchool' : 'labelCollege';
      var placeholderKey = isSchool ? 'placeholderSchool' : 'placeholderCollege';
      if (field.dataset[labelKey]) {
        var labelEl = field.querySelector('label');
        if (labelEl) labelEl.textContent = field.dataset[labelKey];
      }
      if (field.dataset[placeholderKey]) {
        var inputEl = field.querySelector('input, textarea');
        if (inputEl) inputEl.placeholder = field.dataset[placeholderKey];
      }
    });
  }

  function bindEduType(item) {
    var sel = item.querySelector('[data-edu-type]');
    if (!sel) return;
    sel.addEventListener('change', function () { applyEduType(item, sel.value); });
    applyEduType(item, sel.value);
  }

  document.querySelectorAll('[data-repeat-item]').forEach(function (item) {
    bindEduType(item);
  });

  /* ═══════════════════════════════════════════════════════════════
   * 10. Auto-grow Textareas
   * ═══════════════════════════════════════════════════════════════ */
  function autoGrow(el) {
    el.style.height = 'auto';
    el.style.height = (el.scrollHeight) + 'px';
  }
  document.querySelectorAll('textarea').forEach(function(ta) {
    ta.addEventListener('input', function() { autoGrow(ta); });
    /* Initial grow on load */
    setTimeout(function() { autoGrow(ta); }, 10);
  });

  /* ═══════════════════════════════════════════════════════════════
   * 11. Smooth Scroll Nav
   * ═══════════════════════════════════════════════════════════════ */
  document.querySelectorAll('.bf-nav a[href^="#"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      var targetId = this.getAttribute('href').substring(1);
      var targetEl = document.getElementById(targetId);
      if (targetEl) {
        var offset = 80; /* account for sticky topbar */
        var bodyRect = document.body.getBoundingClientRect().top;
        var elementRect = targetEl.getBoundingClientRect().top;
        var elementPosition = elementRect - bodyRect;
        var offsetPosition = elementPosition - offset;
        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  /* Active state sync on scroll */
  var sections = document.querySelectorAll('.bf-section');
  var navLinks = document.querySelectorAll('.bf-nav a');
  window.addEventListener('scroll', function() {
    var current = '';
    sections.forEach(function(section) {
      var sectionTop = section.offsetTop;
      if (scrollY >= (sectionTop - 150)) {
        current = section.getAttribute('id');
      }
    });
    navLinks.forEach(function(a) {
      a.classList.remove('is-active');
      if (a.getAttribute('href') === '#' + current) {
        a.classList.add('is-active');
      }
    });
  });

  /* ═══════════════════════════════════════════════════════════════
   * 8. Phone hidden-field sync
   * ═══════════════════════════════════════════════════════════════ */
  (function () {
    var codeSelect = document.getElementById('phone_country_code');
    var numberInput = document.getElementById('phone_number');
    var hidden = document.getElementById('phone');
    if (!codeSelect || !numberInput || !hidden) return;
    function syncPhone() {
      hidden.value = (codeSelect.value + ' ' + numberInput.value.trim()).trim();
    }
    codeSelect.addEventListener('change', function () { syncPhone(); scheduleAutosave(); });
    numberInput.addEventListener('input', function () { syncPhone(); scheduleAutosave(); });
    syncPhone();
  })();

  /* ═══════════════════════════════════════════════════════════════
   * 1. Repeatable section groups
   * ═══════════════════════════════════════════════════════════════ */
  document.querySelectorAll('[data-repeat-group]').forEach(function (group) {
    var section = group.dataset.repeatGroup;
    var list = group.querySelector('[data-repeat-list]');
    var addBtn = group.querySelector('[data-repeat-add]');
    var templateEl = group.querySelector('[data-repeat-template]');

    function reindex() {
      list.querySelectorAll('[data-repeat-item]').forEach(function (item, index) {
        item.querySelectorAll('[name]').forEach(function (field) {
          field.name = field.name.replace(/\[\d+\]/, '[' + index + ']');
        });
        var label = item.querySelector('[data-repeat-index]');
        if (label) label.textContent = section + ' #' + (index + 1);
      });
    }

    function bindToggle(item) {
      var headBtn = item.querySelector('[data-repeat-toggle]');
      if (!headBtn) return;
      headBtn.addEventListener('click', function () {
        var collapsed = item.getAttribute('data-repeat-collapsed') === 'true';
        item.setAttribute('data-repeat-collapsed', collapsed ? 'false' : 'true');
      });
    }

    function bindRemove(item) {
      var btn = item.querySelector('[data-repeat-remove]');
      if (!btn) return;
      btn.addEventListener('click', function () {
        if (list.querySelectorAll('[data-repeat-item]').length <= 1) {
          item.querySelectorAll('input, textarea, select').forEach(function (f) {
            if (f.type === 'checkbox') { f.checked = false; } else { f.value = ''; }
          });
          scheduleAutosave();
          return;
        }
        item.remove();
        reindex();
        scheduleAutosave();
      });
    }

    function bindCurrentToggle(item) {
      var checkbox = item.querySelector('[data-current-toggle]');
      var endDate = item.querySelector('[data-end-date]');
      if (!checkbox || !endDate) return;
      checkbox.addEventListener('change', function () {
        endDate.disabled = checkbox.checked;
        if (checkbox.checked) endDate.value = '';
        scheduleAutosave();
      });
    }

    function bindDateOrderCheck(item) {
      var start = item.querySelector('input[name*="[start_date]"]');
      var end = item.querySelector('[data-end-date], input[name*="[end_date]"]');
      if (!start || !end) return;
      function check() {
        var hint = item.querySelector('[data-date-order-hint]');
        if (start.value && end.value && end.value < start.value) {
          if (!hint) {
            hint = document.createElement('p');
            hint.dataset.dateOrderHint = '1';
            hint.style.cssText = 'color:#B23B3B;font-size:0.8rem;margin:4px 0 0;';
            hint.textContent = 'End date is before start date — double check this entry.';
            end.closest('.bf-field').appendChild(hint);
          }
        } else if (hint) {
          hint.remove();
        }
      }
      start.addEventListener('change', check);
      end.addEventListener('change', check);
    }

    list.querySelectorAll('[data-repeat-item]').forEach(function (item) {
      bindToggle(item);
      bindRemove(item);
      bindCurrentToggle(item);
      bindDateOrderCheck(item);
    });

    if (addBtn && templateEl) {
      addBtn.addEventListener('click', function () {
        var clone = templateEl.content.cloneNode(true);
        var item = clone.querySelector('[data-repeat-item]');
        list.appendChild(item);
        bindToggle(item);
        bindRemove(item);
        bindCurrentToggle(item);
        bindDateOrderCheck(item);
        bindEduType(item);
        reindex();
      });
    }
  });

  /* ═══════════════════════════════════════════════════════════════
   * 2. Tag inputs (skills / achievements / interests)
   *
   * FIX (achievements / interests Enter key):
   * `let suggestBox` was previously declared AFTER the early-return
   * guard (`if (fieldName !== 'skills') return`), putting it in the
   * temporal dead zone for non-skills fields. The keydown handler
   * (shared by all tag inputs) references suggestBox before it could
   * ever be initialized for achievements/interests, throwing a silent
   * ReferenceError that aborted the handler after e.preventDefault()
   * but before addTag() — so Enter appeared to do nothing.
   *
   * Fix: declare suggestBox = null and closeSuggestions (null-safe)
   * BEFORE the keydown handler. For skills, suggestBox is then
   * assigned the real DOM element after the guard. For all other tag
   * inputs, `if (!suggestBox) return` inside closeSuggestions makes
   * every reference safe to call.
   * ═══════════════════════════════════════════════════════════════ */
  document.querySelectorAll('[data-tag-input]').forEach(function (wrap) {
    var fieldName = wrap.dataset.tagInput;
    var textInput = wrap.querySelector('[data-tag-text]');
    var tagList = wrap.querySelector('[data-tag-list]');

    function existingValues() {
      return Array.from(tagList.querySelectorAll('input[type="hidden"]')).map(function (i) {
        return i.value.toLowerCase();
      });
    }

    /* Bind remove on tags server-rendered from PHP session data */
    tagList.querySelectorAll('.bf-tag button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        btn.closest('.bf-tag').remove();
        scheduleAutosave();
      });
    });

    function addTag(value) {
      value = value.trim();
      if (!value) return;
      if (existingValues().indexOf(value.toLowerCase()) !== -1) {
        textInput.value = '';
        return;
      }
      var pill = document.createElement('span');
      pill.className = 'bf-tag';
      var hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = fieldName + '[]';
      hidden.value = value;
      pill.appendChild(hidden);
      pill.appendChild(document.createTextNode(value));
      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.setAttribute('aria-label', 'Remove ' + value);
      removeBtn.textContent = '\u00D7';
      removeBtn.addEventListener('click', function () { pill.remove(); scheduleAutosave(); });
      pill.appendChild(removeBtn);
      tagList.appendChild(pill);
      scheduleAutosave();
    }

    /* ── Declare suggestBox + closeSuggestions BEFORE the keydown handler
       so they are NEVER in the temporal dead zone for any tag input. ── */
    var suggestBox = null;
    var currentItems = [];

    function closeSuggestions() {
      if (!suggestBox) return;   /* safe no-op for achievements/interests */
      suggestBox.style.display = 'none';
      suggestBox.classList.remove('is-open');
      suggestBox.dataset.activeIndex = '-1';
      currentItems = [];
    }

    /* Keydown: Enter always prevented from bubbling to form submit */
    textInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        /* If a suggestion is active, select it */
        if (suggestBox && suggestBox.classList.contains('is-open')) {
          var active = parseInt(suggestBox.dataset.activeIndex, 10);
          if (active >= 0 && currentItems[active]) {
            addTag(currentItems[active].label);
            textInput.value = '';
            closeSuggestions();
            return;
          }
        }
        addTag(textInput.value);
        textInput.value = '';
        closeSuggestions();
        return;
      }
      if (e.key === ',') {
        e.preventDefault();
        addTag(textInput.value);
        textInput.value = '';
        closeSuggestions();
        return;
      }
    });

    textInput.addEventListener('blur', function () {
      setTimeout(function () {
        if (textInput.value.trim()) { addTag(textInput.value); textInput.value = ''; }
        closeSuggestions();
      }, 150);
    });

    /* ── Autosuggest: skills only ── */
    if (fieldName !== 'skills') return;

    /* Now safe to reassign — no longer a TDZ let declaration */
    suggestBox = document.createElement('div');
    suggestBox.className = 'bf-suggest';
    suggestBox.dataset.activeIndex = '-1';
    suggestBox.style.cssText = [
      'position:absolute', 'z-index:50', 'background:#fff',
      'border:1px solid var(--br-border,#E1E3EA)', 'border-radius:9px',
      'box-shadow:0 8px 22px rgba(20,20,40,0.12)', 'margin-top:4px',
      'max-height:260px', 'overflow-y:auto', 'min-width:240px', 'display:none'
    ].join(';');
    wrap.style.position = 'relative';
    wrap.appendChild(suggestBox);

    var debounceTimer = null;

    function renderSuggestions(items) {
      currentItems = items;
      suggestBox.innerHTML = '';
      if (!items.length) { closeSuggestions(); return; }
      items.forEach(function (item, index) {
        var row = document.createElement('div');
        row.dataset.suggestIndex = String(index);
        row.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:0.88rem;display:flex;justify-content:space-between;gap:10px;';
        var label = document.createElement('span');
        label.textContent = item.label;
        var meta = document.createElement('span');
        meta.style.cssText = 'color:var(--br-muted,#6B6E7A);font-size:0.76rem;white-space:nowrap;';
        meta.textContent = item.type === 'related' ? ('Related \u00B7 ' + item.category) : item.category;
        row.appendChild(label);
        row.appendChild(meta);
        row.addEventListener('mousedown', function (e) {
          e.preventDefault();
          addTag(item.label);
          textInput.value = '';
          closeSuggestions();
          textInput.focus();
        });
        row.addEventListener('mouseenter', function () { setActive(index); });
        suggestBox.appendChild(row);
      });
      suggestBox.style.display = 'block';
      suggestBox.classList.add('is-open');
      suggestBox.dataset.activeIndex = '-1';
    }

    function setActive(index) {
      var rows = suggestBox.querySelectorAll('[data-suggest-index]');
      rows.forEach(function (r) { r.style.background = ''; });
      if (index >= 0 && index < rows.length) {
        rows[index].style.background = 'var(--br-primary-soft,#EEF1FF)';
        suggestBox.dataset.activeIndex = String(index);
      } else {
        suggestBox.dataset.activeIndex = '-1';
      }
    }

    textInput.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      var q = textInput.value.trim();
      if (q.length < 1) { closeSuggestions(); return; }
      debounceTimer = setTimeout(function () {
        fetch('assets/skills-autocomplete.php?q=' + encodeURIComponent(q))
          .then(function (r) { return r.ok ? r.json() : []; })
          .then(renderSuggestions)
          .catch(function () { closeSuggestions(); });
      }, 180);
    });

    /* Arrow / Escape for open suggestion box */
    textInput.addEventListener('keydown', function (e) {
      if (!suggestBox || !suggestBox.classList.contains('is-open')) return;
      var rows = suggestBox.querySelectorAll('[data-suggest-index]');
      var active = parseInt(suggestBox.dataset.activeIndex, 10);
      if (e.key === 'ArrowDown') {
        e.preventDefault(); setActive(Math.min(active + 1, rows.length - 1));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault(); setActive(Math.max(active - 1, -1));
      } else if (e.key === 'Escape') {
        closeSuggestions();
      }
    });
  });

  /* ═══════════════════════════════════════════════════════════════
   * 6. File upload filename preview
   * ═══════════════════════════════════════════════════════════════ */
  var fileInput = document.getElementById('profile_picture_file');
  if (fileInput) {
    var hint = document.createElement('span');
    hint.style.cssText = 'display:block;margin-top:6px;font-size:0.8rem;color:var(--br-muted,#6B6E7A);';
    fileInput.insertAdjacentElement('afterend', hint);
    fileInput.addEventListener('change', function () {
      hint.textContent = fileInput.files && fileInput.files[0]
        ? 'Selected: ' + fileInput.files[0].name : '';
    });
  }

  /* ═══════════════════════════════════════════════════════════════
   * 5. Background autosave
   * ═══════════════════════════════════════════════════════════════ */
  var saveStatusEl = document.querySelector('[data-save-status]');
  var autosaveTimer = null;
  var autosaveInFlight = false;

  /* Toast Notification System */
  var toastEl = document.createElement('div');
  toastEl.className = 'bf-toast';
  toastEl.innerHTML = '<div class="bf-toast-icon">✓</div><div class="bf-toast-text">Changes saved</div>';
  document.body.appendChild(toastEl);
  var toastTimeout = null;

  function showToast() {
    toastEl.classList.add('is-visible');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(function() {
      toastEl.classList.remove('is-visible');
    }, 2500);
  }

  function setStatus(text, success) {
    if (saveStatusEl) {
      if (success && text === 'All changes saved') {
        saveStatusEl.textContent = 'Saved';
        showToast();
      } else {
        saveStatusEl.textContent = text;
      }
    }
  }

  function runAutosave() {
    if (!form || autosaveInFlight) return;
    autosaveInFlight = true;
    setStatus('Saving\u2026', false);
    var data = new FormData(form);
    data.delete('profile_picture_file');
    fetch('assets/autosave.php', { method: 'POST', body: data })
      .then(function (r) { setStatus(r.ok ? 'All changes saved' : 'Could not save \u2014 check connection', r.ok); })
      .catch(function () { setStatus('Could not save \u2014 check connection', false); })
      .finally(function () { autosaveInFlight = false; });
  }

  function scheduleAutosave() {
    setStatus('Unsaved changes\u2026');
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(runAutosave, 1500);
    updateProgress(); /* keep meter live on every interaction */
  }

  if (form) {
    form.addEventListener('input', scheduleAutosave);
    form.addEventListener('change', scheduleAutosave);
    window.addEventListener('beforeunload', function () {
      if (autosaveTimer) {
        clearTimeout(autosaveTimer);
        var data = new FormData(form);
        data.delete('profile_picture_file');
        navigator.sendBeacon && navigator.sendBeacon('assets/autosave.php', data);
      }
    });
  }

  /* Initial progress render on page load */
  updateProgress();

});