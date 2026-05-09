/* ==========================================================
   MAIN.JS — Init global + theme toggle
   ========================================================== */

(function () {
  // ---- Theme toggle ----
  const html  = document.documentElement;
  const pref  = window.matchMedia('(prefers-color-scheme: dark)');
  let theme   = pref.matches ? 'dark' : 'light';
  html.setAttribute('data-theme', theme);

  function setTheme(t) {
    theme = t;
    html.setAttribute('data-theme', t);
    const btn = document.querySelector('[data-theme-toggle]');
    if (btn) {
      btn.setAttribute('aria-label', 'Cambiar a modo ' + (t === 'dark' ? 'claro' : 'oscuro'));
      btn.innerHTML = t === 'dark'
        ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>'
        : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    setTheme(theme);
    const btn = document.querySelector('[data-theme-toggle]');
    btn?.addEventListener('click', () => setTheme(theme === 'dark' ? 'light' : 'dark'));
  });

  pref.addEventListener('change', (e) => {
    if (!html.dataset.themeManual) setTheme(e.matches ? 'dark' : 'light');
  });
})();
