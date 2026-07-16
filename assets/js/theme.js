// EasyResume — Shared Theme & Navigation Logic
// Include before </body> on every page

(function () {
    'use strict';

    const STORAGE_KEY = 'easyresume-theme';

    // Apply theme to document
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);

        const btn = document.querySelector('.theme-toggle');

        if (btn) {
            btn.textContent = theme === 'dark' ? '☀️' : '🌙';

            btn.setAttribute(
                'aria-label',
                theme === 'dark'
                    ? 'Switch to light mode'
                    : 'Switch to dark mode'
            );
        }
    }

    // Get saved theme or fallback to OS preference
    function getPreferredTheme() {
        const storedTheme = localStorage.getItem(STORAGE_KEY);

        if (storedTheme === 'light' || storedTheme === 'dark') {
            return storedTheme;
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    // Toggle theme
    function toggleTheme() {
        const currentTheme =
            document.documentElement.getAttribute('data-theme') || 'light';

        const nextTheme =
            currentTheme === 'dark'
                ? 'light'
                : 'dark';

        localStorage.setItem(STORAGE_KEY, nextTheme);

        applyTheme(nextTheme);
    }

    // Apply immediately to avoid theme flash
    applyTheme(getPreferredTheme());

    document.addEventListener('DOMContentLoaded', function () {

        applyTheme(getPreferredTheme());

        // Theme toggle button
        const toggleBtn = document.querySelector('.theme-toggle');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleTheme);
        }

        // Mobile navigation
        const navToggler = document.querySelector('.navbar-toggler-btn');
        const navButtons = document.querySelector('.nav-buttons');

        if (navToggler && navButtons) {

            navToggler.addEventListener('click', function () {
                navButtons.classList.toggle('open');
            });

            // Auto-close mobile menu when clicking a link
            navButtons.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    navButtons.classList.remove('open');
                });
            });

        }

    });

    // Follow OS theme changes only if user has not chosen manually
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    function handleSystemThemeChange(e) {

        if (!localStorage.getItem(STORAGE_KEY)) {
            applyTheme(e.matches ? 'dark' : 'light');
        }

    }

    if (typeof mediaQuery.addEventListener === 'function') {

        mediaQuery.addEventListener(
            'change',
            handleSystemThemeChange
        );

    } else if (typeof mediaQuery.addListener === 'function') {

        // Older browser support
        mediaQuery.addListener(
            handleSystemThemeChange
        );

    }

})();