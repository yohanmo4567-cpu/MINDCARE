<?php
require_once 'sesion.php';
// Página 100% en cliente: no requiere lectura/escritura en base de datos.
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Respiración | MindCare+</title>
<script src="theme.js"></script>
<script src="color-themes.js"></script>
<link rel="stylesheet" href="dashboard.css"/>
</head>
<body>
<div class="app-shell">
<?php renderSidebar('respiracion'); ?>
<div class="main-content">
<?php renderTopbar('Respiración Guiada 🌬️', 'Un momento para calmar tu mente y tu cuerpo'); ?>
<main class="page-body">

<div class="grid-2 mb-24" style="align-items:start;">

  <!-- Ejercicio de respiración -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">🌬️ Ejercicio de respiración</div>
        <div class="card-subtitle" id="patternSubtitle">Respiración en caja · 4-4-4-4</div>
      </div>
    </div>

    <div class="breathing-wrap">
      <div class="breathing-stage">
        <div class="breathing-ring"></div>
        <div class="breathing-orb" id="breathOrb">
          <div style="text-align:center;">
            <div class="breathing-phase-text" id="breathPhaseText">Presiona iniciar</div>
            <div class="breathing-count" id="breathCount"></div>
          </div>
        </div>
      </div>

      <div class="breathing-controls">
        <button type="button" class="btn btn-primary" id="btnStart" onclick="MindCareBreathing.start()">▶️ Iniciar</button>
        <button type="button" class="btn btn-danger" id="btnStop" style="display:none;" onclick="MindCareBreathing.stop('Sesión detenida.')">⏹ Detener</button>
      </div>

      <div class="breathing-progress-wrap">
        <div class="progress-bar"><div class="progress-fill" id="breathProgressFill" style="width:0%"></div></div>
        <div class="breathing-session-time" id="breathSessionTime">Selecciona un patrón y una duración para comenzar.</div>
      </div>

      <div class="breathing-status" id="breathStatus"></div>
    </div>
  </div>

  <!-- Configuración -->
  <div>
    <div class="card mb-20">
      <div class="card-header"><div class="card-title">🎯 Patrón de respiración</div></div>
      <div class="pattern-grid">
        <button type="button" class="pattern-btn selected" data-pattern="4-4-4-4" onclick="MindCareBreathing.selectPattern('4-4-4-4')">
          Respiración en caja<small>Inhala 4 · Mantén 4 · Exhala 4 · Mantén 4</small>
        </button>
        <button type="button" class="pattern-btn" data-pattern="4-7-8" onclick="MindCareBreathing.selectPattern('4-7-8')">
          Relajación 4-7-8<small>Inhala 4 · Mantén 7 · Exhala 8</small>
        </button>
        <button type="button" class="pattern-btn" data-pattern="6-6" onclick="MindCareBreathing.selectPattern('6-6')">
          Respiración simple<small>Inhala 6 · Exhala 6</small>
        </button>
      </div>
    </div>

    <div class="card mb-20">
      <div class="card-header"><div class="card-title">⏱ Duración de la sesión</div></div>
      <div class="duration-grid">
        <button type="button" class="duration-btn" data-duration="1" onclick="MindCareBreathing.selectDuration(1)">1 min</button>
        <button type="button" class="duration-btn selected" data-duration="3" onclick="MindCareBreathing.selectDuration(3)">3 min</button>
        <button type="button" class="duration-btn" data-duration="5" onclick="MindCareBreathing.selectDuration(5)">5 min</button>
        <button type="button" class="duration-btn" data-duration="10" onclick="MindCareBreathing.selectDuration(10)">10 min</button>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><div class="card-title">💡 Consejo</div></div>
      <div class="breathing-tip">
        <span style="font-size:1.4rem;">🧘</span>
        <span>Siéntate cómodamente, relaja los hombros y sigue el círculo con tu respiración. Si tu mente se distrae, simplemente vuelve a notar el aire entrando y saliendo.</span>
      </div>
    </div>
  </div>
</div>

</main>
</div>
</div>
<script src="dashboard.js"></script>
<script>
/**
 * MindCare+ · Respiración guiada
 * Motor cliente para animar el círculo de respiración según el patrón elegido.
 */
const MindCareBreathing = (function () {
  const PATTERNS = {
    '4-4-4-4': {
      label: 'Respiración en caja · 4-4-4-4',
      phases: [
        { name: 'Inhala', seconds: 4, scale: 1.65 },
        { name: 'Mantén', seconds: 4, scale: 1.65 },
        { name: 'Exhala', seconds: 4, scale: 1 },
        { name: 'Mantén', seconds: 4, scale: 1 },
      ],
    },
    '4-7-8': {
      label: 'Relajación 4-7-8',
      phases: [
        { name: 'Inhala', seconds: 4, scale: 1.65 },
        { name: 'Mantén', seconds: 7, scale: 1.65 },
        { name: 'Exhala', seconds: 8, scale: 1 },
      ],
    },
    '6-6': {
      label: 'Respiración simple · 6-6',
      phases: [
        { name: 'Inhala', seconds: 6, scale: 1.65 },
        { name: 'Exhala', seconds: 6, scale: 1 },
      ],
    },
  };

  const BASE_SIZE = 120;

  let orb, phaseTextEl, countEl, statusEl, sessionTimeEl, progressFillEl, subtitleEl, btnStart, btnStop;
  let currentPatternKey = '4-4-4-4';
  let currentDurationMin = 3;
  let running = false;
  let phaseIndex = 0;
  let phaseTimeout = null;
  let countdownInterval = null;
  let sessionInterval = null;
  let sessionTotalSeconds = 0;
  let sessionRemainingSeconds = 0;

  function init() {
    orb            = document.getElementById('breathOrb');
    phaseTextEl    = document.getElementById('breathPhaseText');
    countEl        = document.getElementById('breathCount');
    statusEl       = document.getElementById('breathStatus');
    sessionTimeEl  = document.getElementById('breathSessionTime');
    progressFillEl = document.getElementById('breathProgressFill');
    subtitleEl     = document.getElementById('patternSubtitle');
    btnStart       = document.getElementById('btnStart');
    btnStop        = document.getElementById('btnStop');
    resetVisual();
  }

  function resetVisual() {
    orb.style.transitionDuration = '1s';
    orb.style.width  = BASE_SIZE + 'px';
    orb.style.height = BASE_SIZE + 'px';
    phaseTextEl.textContent = 'Presiona iniciar';
    countEl.textContent = '';
    progressFillEl.style.width = '0%';
  }

  function selectPattern(key) {
    if (!PATTERNS[key]) return;
    currentPatternKey = key;
    document.querySelectorAll('.pattern-btn').forEach(function (b) {
      b.classList.toggle('selected', b.dataset.pattern === key);
    });
    subtitleEl.textContent = PATTERNS[key].label;
    if (!running) resetVisual();
  }

  function selectDuration(min) {
    currentDurationMin = min;
    document.querySelectorAll('.duration-btn').forEach(function (b) {
      b.classList.toggle('selected', parseInt(b.dataset.duration, 10) === min);
    });
    if (!running) {
      sessionTimeEl.textContent = 'Duración seleccionada: ' + min + ' min';
    }
  }

  function start() {
    if (running) return;
    running = true;
    phaseIndex = 0;
    sessionTotalSeconds = currentDurationMin * 60;
    sessionRemainingSeconds = sessionTotalSeconds;
    statusEl.textContent = '';
    btnStart.style.display = 'none';
    btnStop.style.display  = 'inline-flex';
    updateSessionTimeDisplay();
    runPhase();
    sessionInterval = setInterval(function () {
      sessionRemainingSeconds--;
      updateSessionTimeDisplay();
      if (sessionRemainingSeconds <= 0) {
        stop('✅ ¡Sesión completada! Que te sientas en calma.', true);
      }
    }, 1000);
  }

  function stop(message, completed) {
    running = false;
    clearTimeout(phaseTimeout);
    clearInterval(countdownInterval);
    clearInterval(sessionInterval);
    btnStart.style.display = 'inline-flex';
    btnStop.style.display  = 'none';
    resetVisual();
    statusEl.textContent = message || '';
    if (!completed) {
      sessionTimeEl.textContent = 'Duración seleccionada: ' + currentDurationMin + ' min';
      progressFillEl.style.width = '0%';
    } else {
      progressFillEl.style.width = '100%';
    }
  }

  function updateSessionTimeDisplay() {
    const m = Math.floor(sessionRemainingSeconds / 60);
    const s = sessionRemainingSeconds % 60;
    sessionTimeEl.textContent = 'Tiempo restante: ' + m + ':' + String(s).padStart(2, '0');
    const pct = 100 - Math.round((sessionRemainingSeconds / sessionTotalSeconds) * 100);
    progressFillEl.style.width = Math.max(0, Math.min(100, pct)) + '%';
  }

  function runPhase() {
    if (!running) return;
    const pattern = PATTERNS[currentPatternKey];
    const phase = pattern.phases[phaseIndex % pattern.phases.length];

    phaseTextEl.textContent = phase.name + (phase.name === 'Mantén' ? ' 🤍' : '');
    orb.style.transitionDuration = phase.seconds + 's';
    orb.style.width  = (BASE_SIZE * phase.scale) + 'px';
    orb.style.height = (BASE_SIZE * phase.scale) + 'px';

    let remaining = phase.seconds;
    countEl.textContent = remaining;
    clearInterval(countdownInterval);
    countdownInterval = setInterval(function () {
      remaining--;
      if (remaining > 0) {
        countEl.textContent = remaining;
      } else {
        clearInterval(countdownInterval);
      }
    }, 1000);

    phaseTimeout = setTimeout(function () {
      if (!running) return;
      phaseIndex++;
      runPhase();
    }, phase.seconds * 1000);
  }

  return { init: init, start: start, stop: stop, selectPattern: selectPattern, selectDuration: selectDuration };
})();

document.addEventListener('DOMContentLoaded', MindCareBreathing.init);
</script>
</body>
</html>
