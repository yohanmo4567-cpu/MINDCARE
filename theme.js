/**
 * MindCare+ · theme.js
 * Gestión global de Modo Claro / Modo Oscuro.
 * Este script debe cargarse en el <head>, ANTES de cualquier hoja de estilos,
 * para aplicar el tema guardado sin parpadeos (FOUC).
 *
 * Uso:
 *   - Cualquier <input type="checkbox" data-theme-toggle> se sincroniza automáticamente.
 *   - Cualquier <button data-theme-toggle> alterna el tema al hacer clic.
 *   - window.MindCareTheme.get() / .set('dark'|'light') / .toggle() disponibles globalmente.
 */
(function () {
  var STORAGE_KEY = 'mindcare-theme';

  function getTheme() {
    try {
      return localStorage.getItem(STORAGE_KEY) || 'light';
    } catch (e) {
      return 'light';
    }
  }

  function applyTheme(theme) {
    var t = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', t);
    return t;
  }

  function setTheme(theme) {
    var t = applyTheme(theme);
    try { localStorage.setItem(STORAGE_KEY, t); } catch (e) {}
    syncControls(t);
    try {
      document.dispatchEvent(new CustomEvent('mindcare:theme-changed', { detail: { theme: t } }));
    } catch (e) {}
  }

  function toggleTheme() {
    setTheme(getTheme() === 'dark' ? 'light' : 'dark');
  }

  function syncControls(theme) {
    var nodes = document.querySelectorAll('[data-theme-toggle]');
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (el.tagName === 'INPUT' && el.type === 'checkbox') {
        el.checked = theme === 'dark';
      } else {
        el.setAttribute('aria-pressed', String(theme === 'dark'));
      }
    }
  }

  // Aplicar inmediatamente (antes de pintar) para evitar destello del tema incorrecto
  applyTheme(getTheme());

  window.MindCareTheme = { get: getTheme, set: setTheme, toggle: toggleTheme };

  function wireControls() {
    var current = getTheme();
    var nodes = document.querySelectorAll('[data-theme-toggle]');
    for (var i = 0; i < nodes.length; i++) {
      (function (el) {
        if (el.tagName === 'INPUT' && el.type === 'checkbox') {
          el.checked = current === 'dark';
          el.addEventListener('change', function () {
            setTheme(el.checked ? 'dark' : 'light');
          });
        } else {
          el.setAttribute('aria-pressed', String(current === 'dark'));
          el.addEventListener('click', function () { toggleTheme(); });
        }
      })(nodes[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wireControls);
  } else {
    wireControls();
  }
})();
