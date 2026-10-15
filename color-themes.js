/**
 * MindCare+ · color-themes.js
 * ─────────────────────────────────────────────────────────────
 * Sistema de 4 paletas morado pastel intercambiables.
 * Solo re-colorea las variables de "marca" (--primary / --accent):
 * botones, banner de bienvenida, insignias primarias, resaltado del
 * sidebar. Los colores semánticos (éxito=verde, error=rojo) NO se
 * tocan, para no generar confusión en esos mensajes.
 *
 * Uso:
 *   aplicarTemaColor('lavanda' | 'glicina' | 'orquidea' | 'malva')
 *
 * La preferencia se guarda en localStorage y se vuelve a aplicar
 * automáticamente en cada página que incluya este script.
 * ───────────────────────────────────────────────────────────── */

const MINDCARE_COLOR_THEMES = {
  lavanda: {
    nombre: 'Lavanda',
    '--primary':       '#9B87D4',
    '--primary-dark':  '#7C63B8',
    '--primary-light': '#F1ECFB',
    '--accent':        '#C9A0E0',
    '--accent-light':  '#F8F0FC',
  },
  glicina: {
    nombre: 'Glicina',
    '--primary':       '#A98FE0',
    '--primary-dark':  '#8A6BC9',
    '--primary-light': '#F2ECFC',
    '--accent':        '#D6A8E8',
    '--accent-light':  '#FAF0FC',
  },
  orquidea: {
    nombre: 'Orquídea',
    '--primary':       '#B98FE0',
    '--primary-dark':  '#996BC9',
    '--primary-light': '#F5EDFC',
    '--accent':        '#DDA6E0',
    '--accent-light':  '#FCF0FC',
  },
  malva: {
    nombre: 'Malva',
    '--primary':       '#A79EE0',
    '--primary-dark':  '#8577C9',
    '--primary-light': '#F0EDFB',
    '--accent':        '#C9A9E4',
    '--accent-light':  '#F8F1FC',
  },
};

const MINDCARE_COLOR_THEME_KEY = 'mindcare_color_theme';

function aplicarTemaColor(nombre) {
  const tema = MINDCARE_COLOR_THEMES[nombre];
  if (!tema) return;

  const root = document.documentElement;
  Object.entries(tema).forEach(([propiedad, valor]) => {
    if (propiedad.startsWith('--')) root.style.setProperty(propiedad, valor);
  });
  root.setAttribute('data-color-theme', nombre);

  try { localStorage.setItem(MINDCARE_COLOR_THEME_KEY, nombre); } catch (e) { /* modo privado */ }

  // Si estamos en la página de configuración, marcar la tarjeta activa
  document.querySelectorAll('[data-theme-option]').forEach((el) => {
    el.classList.toggle('active', el.dataset.themeOption === nombre);
  });
}

// ── Aplicar automáticamente al cargar cualquier página ─────────
(function iniciarTemaColor() {
  let guardado = null;
  try { guardado = localStorage.getItem(MINDCARE_COLOR_THEME_KEY); } catch (e) { /* modo privado */ }
  aplicarTemaColor(guardado && MINDCARE_COLOR_THEMES[guardado] ? guardado : 'lavanda');
})();
