<?php
require_once 'sesion.php';
require_once 'mascota_data.php';
$pdo = conectarDB();
$msg = ''; $msgTipo = '';

// ── Renombrar (única personalización activa por ahora) ───────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'renombrar') {
    verifyCsrf();
    $mascotaActual = obtenerMascota($pdo, $uid);
    renombrarMascota($pdo, (int)$mascotaActual['id_mascota'], $_POST['nombre'] ?? '');
    $msg = '¡Nombre actualizado!'; $msgTipo = 'success';
}

// ── Cargar, desgastar y sincronizar ───────────────────────────────
$mascota = obtenerMascota($pdo, $uid);
$mascota = aplicarDesgaste($pdo, $mascota);
$sync    = sincronizarMascota($pdo, $uid, $mascota);

// Releer estado final ya actualizado tras la sincronización
if ($sync['puntos_nuevos'] > 0) {
    $mascota = obtenerMascota($pdo, $uid);
}

$nivelInfo = calcularNivel((int)$mascota['experiencia']);
$etapa     = obtenerEtapaGato($nivelInfo['nivel']);
$animo     = obtenerEstadoAnimo((int)$mascota['hambre'], (int)$mascota['felicidad']);
$consejo   = obtenerConsejoDelDia($animo);

$animoLabels = [
    'feliz'      => ['label' => '¡Feliz!',    'emoji' => '😻'],
    'contento'   => ['label' => 'Contento',   'emoji' => '😺'],
    'triste'     => ['label' => 'Un poco triste', 'emoji' => '🥺'],
    'hambriento' => ['label' => 'Con hambre', 'emoji' => '😿'],
];

// ── Cara del gato según su estado de ánimo ────────────────────────
switch ($animo) {
    case 'feliz':
        $cara = '
        <path d="M85,90 Q92,76 99,90" class="cat-line"/>
        <path d="M121,90 Q128,76 135,90" class="cat-line"/>
        <path d="M100,108 Q110,120 110,109 Q110,120 120,108" class="cat-line cat-mouth"/>
        <circle cx="80" cy="103" r="6" class="cat-blush"/>
        <circle cx="140" cy="103" r="6" class="cat-blush"/>
        <path d="M150,72 l6,-10 l6,10" class="cat-line cat-sparkle"/>';
        break;
    case 'triste':
        $cara = '
        <path d="M83,92 Q92,100 100,92" class="cat-line"/>
        <path d="M120,92 Q128,100 137,92" class="cat-line"/>
        <path d="M102,112 Q110,106 118,112" class="cat-line cat-mouth"/>
        <path d="M84,96 q-2,10 -6,12" class="cat-line cat-tear"/>';
        break;
    case 'hambriento':
        $cara = '
        <ellipse cx="91" cy="92" rx="7" ry="9" class="cat-eye-fill"/>
        <circle cx="88.5" cy="87.5" r="2.3" class="cat-eye-shine"/>
        <ellipse cx="129" cy="92" rx="7" ry="9" class="cat-eye-fill"/>
        <circle cx="126.5" cy="87.5" r="2.3" class="cat-eye-shine"/>
        <ellipse cx="110" cy="112" rx="7" ry="5" class="cat-mouth-open"/>
        <path d="M96,96 q-2,10 -6,12" class="cat-line cat-tear"/>';
        break;
    default: // contento
        $cara = '
        <ellipse cx="91" cy="92" rx="7" ry="9" class="cat-eye-fill"/>
        <circle cx="88.5" cy="87.5" r="2.3" class="cat-eye-shine"/>
        <ellipse cx="129" cy="92" rx="7" ry="9" class="cat-eye-fill"/>
        <circle cx="126.5" cy="87.5" r="2.3" class="cat-eye-shine"/>
        <path d="M100,109 Q110,116 120,109" class="cat-line cat-mouth"/>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Mi Mascota | MindCare+</title>
<script src="theme.js"></script>
<script src="color-themes.js"></script>
<link rel="stylesheet" href="dashboard.css"/>
<style>
/* ── Módulo Mi Mascota ─────────────────────────────────────────
   Usa exclusivamente variables de dashboard.css, por lo que hereda
   automáticamente el modo oscuro y el tema de color activo. */

.mascota-hero {
  display: grid;
  grid-template-columns: minmax(240px, 340px) 1fr;
  gap: 28px;
  align-items: stretch;
}
@media (max-width: 860px) {
  .mascota-hero { grid-template-columns: 1fr; }
}

.mascota-stage {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 28px 20px;
  text-align: center;
  background: radial-gradient(circle at 50% 28%, #FFF3E4, var(--surface-soft) 72%);
}
html[data-theme="dark"] .mascota-stage {
  background: radial-gradient(circle at 50% 28%, rgba(232,167,87,.14), var(--surface-soft) 72%);
}

.mascota-nombre-badge {
  font-size: 1.15rem;
  font-weight: 800;
  margin-bottom: 2px;
}
.mascota-etapa {
  font-size: .78rem;
  color: var(--muted);
  margin-bottom: 14px;
}

.mascota-svg-wrap {
  width: min(100%, 230px);
  aspect-ratio: 1 / 1;
}
.mascota-svg-wrap svg { width: 100%; height: 100%; display: block; overflow: visible; }

/* ── Paleta "natural" del gato (independiente del tema morado del sitio,
   para que siempre se vea como un gatito de verdad) ─────────────── */
.cat-body      { fill: url(#catBodyGradient); }
.cat-belly     { fill: #FFF6EC; }
.cat-inner-ear { fill: #FFC9B0; }
.cat-stripe    { stroke: #B8763A; opacity: .45; fill: none; }
.cat-tail      { stroke: #D98B45; }
.cat-leg       { stroke: #D98B45; }
.cat-paw       { fill: #FFF6EC; }
.cat-line      { stroke: #4A3225; fill: none; }
.cat-eye-fill  { fill: #4A3225; }
.cat-mouth-open{ fill: #4A3225; stroke: none; }
.cat-blush     { fill: #FF9E8A; opacity: .5; }
.cat-tear      { stroke: #7FB8E0; }
.cat-sparkle   { stroke: #F2C14E; }
.cat-nose      { fill: #4A3225; }
.cat-eye-shine { fill: #FFFFFF; }
.cat-shadow    { fill: #C98A3E; opacity: .18; }

.mascota-cat-anim { animation: catBob 3.2s ease-in-out infinite; transform-origin: 50% 100%; filter: drop-shadow(0 10px 14px rgba(80,50,20,.16)); }
@keyframes catBob {
  0%, 100% { transform: translateY(0) scale(1); }
  50%      { transform: translateY(-5px) scale(1.01); }
}
.cat-tail { animation: catTailSway 2.6s ease-in-out infinite; transform-origin: 172px 195px; }
@keyframes catTailSway {
  0%, 100% { transform: rotate(0deg); }
  50%      { transform: rotate(-6deg); }
}
@media (prefers-reduced-motion: reduce) {
  .mascota-cat-anim, .cat-tail { animation: none; }
}

.mascota-nivel-chip {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--surface); border: 1px solid var(--line);
  border-radius: 999px; padding: 5px 14px; font-size: .8rem; font-weight: 700;
  margin-top: 10px;
}

.mascota-bars { display: flex; flex-direction: column; gap: 14px; margin-top: 18px; }
.mascota-bar-row { text-align: left; }
.mascota-bar-label {
  display: flex; justify-content: space-between; align-items: center;
  font-size: .78rem; font-weight: 700; margin-bottom: 4px; color: var(--muted);
}
.mascota-bar-label span.val { color: var(--ink); }
.bar-track {
  height: 10px; border-radius: 999px; background: var(--line); overflow: hidden;
}
.bar-fill { height: 100%; border-radius: 999px; transition: width .5s ease; }
.bar-fill.hambre    { background: linear-gradient(90deg, var(--accent), var(--primary)); }
.bar-fill.felicidad { background: linear-gradient(90deg, var(--rose), var(--accent)); }
.bar-fill.xp         { background: linear-gradient(90deg, var(--blue), var(--primary-dark)); }

.consejo-bubble {
  position: relative;
  background: var(--surface-soft);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 18px 20px;
  font-size: .92rem;
  line-height: 1.6;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}
.consejo-bubble .emoji { font-size: 1.6rem; flex-shrink: 0; }

.puntos-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}
@media (max-width: 700px) { .puntos-grid { grid-template-columns: 1fr; } }
.punto-card {
  border: 1px solid var(--line);
  background: var(--surface-soft);
  border-radius: var(--radius-sm);
  padding: 16px;
  text-align: center;
}
.punto-card .punto-num { font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); }
.punto-card .punto-label { font-size: .8rem; color: var(--muted); margin: 4px 0 10px; }

.personalizacion-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
  opacity: .55; filter: grayscale(.35); pointer-events: none;
}
@media (max-width: 700px) { .personalizacion-grid { grid-template-columns: repeat(2, 1fr); } }
.personalizacion-item {
  border: 2px dashed var(--line); border-radius: var(--radius); padding: 18px 10px;
  text-align: center; font-size: .8rem; color: var(--muted);
}
.personalizacion-item .swatch {
  width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px;
  background: linear-gradient(135deg, var(--primary), var(--accent));
}
.proximamente-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--accent-light); color: var(--primary-dark);
  font-size: .72rem; font-weight: 800; padding: 4px 10px; border-radius: 999px;
}

.form-inline-nombre { display: flex; gap: 10px; flex-wrap: wrap; }
.form-inline-nombre .form-input { flex: 1; min-width: 160px; }
</style>
</head>
<body>
<div class="app-shell">
<?php renderSidebar('mascota'); ?>
<div class="main-content">
<?php renderTopbar('Mi Mascota 🐱', 'Tu compañero de bienestar crece con tus hábitos'); ?>
<main class="page-body">

<?php if ($msg): ?>
<div class="alert alert-<?= $msgTipo === 'success' ? 'success' : 'error' ?> show"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($sync['puntos_nuevos'] > 0): ?>
<div class="alert alert-success show">
  🍽️ <?= htmlspecialchars($mascota['nombre']) ?> ganó <strong>+<?= (int)$sync['puntos_nuevos'] ?> puntos</strong>
  gracias a tus hábitos, actividades y emociones registradas.
  <?php if ($sync['subio_nivel']): ?> ✨ ¡Y subió de nivel!<?php endif; ?>
</div>
<?php endif; ?>

<div class="card mb-24" style="padding:0;overflow:hidden;">
  <div class="mascota-hero">

    <!-- Escenario del gato -->
    <div class="mascota-stage">
      <div class="mascota-nombre-badge"><?= htmlspecialchars($mascota['nombre']) ?></div>
      <div class="mascota-etapa"><?= $etapa['emoji'] ?> <?= $etapa['label'] ?></div>

      <div class="mascota-svg-wrap">
        <svg viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg" class="mascota-cat-anim" role="img" aria-label="Tu mascota, un gato de bienestar con ánimo <?= htmlspecialchars($animoLabels[$animo]['label']) ?>">
          <defs>
            <linearGradient id="catBodyGradient" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#F3B36B"/>
              <stop offset="100%" stop-color="#E8A757"/>
            </linearGradient>
          </defs>

          <!-- Sombra de piso -->
          <ellipse class="cat-shadow" cx="110" cy="213" rx="70" ry="10"/>

          <!-- Cola (curva baja, pegada al cuerpo) -->
          <path class="cat-tail" d="M170,196 C198,204 212,183 200,164 C193,154 179,161 183,173 C186,182 176,184 170,179"
                fill="none" stroke-width="13" stroke-linecap="round"/>
          <!-- Cuerpo -->
          <path class="cat-body" d="M55,140 C40,140 30,156 30,176 C30,206 55,216 76,216 L144,216
                                     C165,216 190,206 190,176 C190,156 180,140 165,140
                                     C165,119 149,107 110,107 C71,107 55,119 55,140 Z"/>
          <!-- Rayitas atigradas del cuerpo -->
          <g class="cat-stripe" stroke-width="3" stroke-linecap="round">
            <path d="M62,128 Q70,120 80,126"/>
            <path d="M58,146 Q68,139 80,145"/>
            <path d="M140,126 Q150,120 158,128"/>
            <path d="M140,145 Q152,139 162,146"/>
          </g>
          <!-- Panza -->
          <ellipse class="cat-belly" cx="110" cy="178" rx="36" ry="30"/>
          <!-- Patitas (encima del cuerpo, para que se vean) -->
          <ellipse class="cat-paw" cx="66" cy="211" rx="15" ry="9"/>
          <ellipse class="cat-paw" cx="154" cy="211" rx="15" ry="9"/>
          <!-- Dos patitas delanteras -->
          <line class="cat-leg" x1="88" y1="174" x2="88" y2="208" stroke-width="10" stroke-linecap="round"/>
          <ellipse class="cat-paw" cx="88" cy="210" rx="10" ry="7"/>
          <line class="cat-leg" x1="132" y1="174" x2="132" y2="208" stroke-width="10" stroke-linecap="round"/>
          <ellipse class="cat-paw" cx="132" cy="210" rx="10" ry="7"/>
          <!-- Orejas -->
          <polygon class="cat-body" points="62,70 40,17 89,52"/>
          <polygon class="cat-body" points="158,70 180,17 131,52"/>
          <polygon class="cat-inner-ear" points="63,63 53,33 82,53"/>
          <polygon class="cat-inner-ear" points="157,63 167,33 138,53"/>
          <!-- Cabeza -->
          <circle class="cat-body" cx="110" cy="95" r="52"/>
          <!-- Rayitas de la frente (encima de la cabeza) -->
          <g class="cat-stripe" stroke-width="3" stroke-linecap="round">
            <path d="M96,60 L96,42"/>
            <path d="M110,55 L110,38"/>
            <path d="M124,60 L124,42"/>
          </g>
          <!-- Bigotes -->
          <g class="cat-line" stroke-width="2.5" opacity=".5" stroke-linecap="round">
            <line x1="58" y1="98" x2="30" y2="93"/>
            <line x1="58" y1="106" x2="30" y2="108"/>
            <line x1="162" y1="98" x2="190" y2="93"/>
            <line x1="162" y1="106" x2="190" y2="108"/>
          </g>
          <!-- Naricita ovalada + líneita que la conecta con la boca -->
          <ellipse cx="110" cy="100" rx="7" ry="5" class="cat-nose"/>
          <line x1="110" y1="104" x2="110" y2="109" class="cat-line" stroke-width="3" stroke-linecap="round"/>
          <!-- Cara (según ánimo) -->
          <g stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
            <?= $cara ?>
          </g>
        </svg>
      </div>

      <div class="mascota-nivel-chip">🏅 Nivel <?= (int)$nivelInfo['nivel'] ?> · <?= $animoLabels[$animo]['emoji'] ?> <?= htmlspecialchars($animoLabels[$animo]['label']) ?></div>
    </div>

    <!-- Panel de estado -->
    <div style="padding:24px 26px;">
      <div class="card-header" style="border:none;padding:0 0 6px;">
        <div>
          <div class="card-title">📊 Estado de <?= htmlspecialchars($mascota['nombre']) ?></div>
          <div class="card-subtitle">Se actualiza cada vez que registras hábitos, actividades o emociones</div>
        </div>
      </div>

      <div class="mascota-bars">
        <div class="mascota-bar-row">
          <div class="mascota-bar-label">🍖 Hambre <span class="val"><?= (int)$mascota['hambre'] ?>/100</span></div>
          <div class="bar-track"><div class="bar-fill hambre" style="width:<?= (int)$mascota['hambre'] ?>%"></div></div>
        </div>
        <div class="mascota-bar-row">
          <div class="mascota-bar-label">💜 Felicidad <span class="val"><?= (int)$mascota['felicidad'] ?>/100</span></div>
          <div class="bar-track"><div class="bar-fill felicidad" style="width:<?= (int)$mascota['felicidad'] ?>%"></div></div>
        </div>
        <div class="mascota-bar-row">
          <div class="mascota-bar-label">⭐ Experiencia <span class="val"><?= (int)$nivelInfo['xp_en_nivel'] ?>/<?= (int)$nivelInfo['xp_necesaria'] ?> XP</span></div>
          <div class="bar-track"><div class="bar-fill xp" style="width:<?= (int)$nivelInfo['porcentaje'] ?>%"></div></div>
        </div>
      </div>

      <div class="consejo-bubble" style="margin-top:20px;">
        <span class="emoji">💬</span>
        <div><strong>Consejo de <?= htmlspecialchars($mascota['nombre']) ?>:</strong><br><?= htmlspecialchars($consejo) ?></div>
      </div>

      <form method="POST" style="margin-top:16px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
        <button class="btn btn-primary btn-sm" style="width:100%">🔄 Actualizar a mi mascota</button>
      </form>
    </div>
  </div>
</div>

<!-- Cómo ganar puntos -->
<div class="card mb-24">
  <div class="card-header"><div class="card-title">🎯 Cómo alimentar a <?= htmlspecialchars($mascota['nombre']) ?></div></div>
  <div class="puntos-grid">
    <div class="punto-card">
      <div class="punto-num">+<?= PUNTOS_HABITO ?></div>
      <div class="punto-label">Por cada hábito completado</div>
      <a href="habitos.php" class="btn btn-ghost btn-sm">Ir a Hábitos</a>
    </div>
    <div class="punto-card">
      <div class="punto-num">+<?= PUNTOS_ACTIVIDAD ?></div>
      <div class="punto-label">Por cada actividad realizada</div>
      <a href="actividades.php" class="btn btn-ghost btn-sm">Ir a Actividades</a>
    </div>
    <div class="punto-card">
      <div class="punto-num">+<?= PUNTOS_EMOCION ?></div>
      <div class="punto-label">Por cada emoción registrada</div>
      <a href="emociones.php" class="btn btn-ghost btn-sm">Ir a Emociones</a>
    </div>
  </div>
</div>

<!-- Renombrar -->
<div class="card mb-24">
  <div class="card-header"><div class="card-title">✏️ Ponle nombre a tu mascota</div></div>
  <form method="POST" class="form-inline-nombre">
    <input type="hidden" name="accion" value="renombrar"/>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
    <input type="text" name="nombre" class="form-input" maxlength="50" placeholder="Nombre de tu mascota" value="<?= htmlspecialchars($mascota['nombre']) ?>"/>
    <button type="submit" class="btn btn-primary">Guardar nombre</button>
  </form>
</div>

<!-- Personalización futura -->
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">🎨 Personalización</div>
      <div class="card-subtitle">Desbloquea colores y accesorios a medida que subes de nivel</div>
    </div>
    <span class="proximamente-badge">🔒 Próximamente</span>
  </div>
  <div class="personalizacion-grid">
    <div class="personalizacion-item"><div class="swatch"></div>Color de pelaje</div>
    <div class="personalizacion-item"><div class="swatch" style="background:linear-gradient(135deg,var(--blue),var(--rose));"></div>Bufanda</div>
    <div class="personalizacion-item"><div class="swatch" style="background:linear-gradient(135deg,var(--accent),var(--purple));"></div>Gafas</div>
    <div class="personalizacion-item"><div class="swatch" style="background:linear-gradient(135deg,var(--rose),var(--primary-dark));"></div>Corona</div>
  </div>
</div>

</main>
</div>
</div>
<script src="dashboard.js"></script>
</body>
</html>
