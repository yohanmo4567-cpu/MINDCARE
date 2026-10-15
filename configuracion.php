<?php
require_once 'sesion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Configuración | MindCare+</title>
<link rel="stylesheet" href="dashboard.css"/>
<script src="theme.js"></script>
<script src="color-themes.js"></script>
<style>
.theme-swatch-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;
}
.theme-swatch {
  border: 2px solid var(--line, #e5e7eb);
  border-radius: var(--radius, 16px);
  background: var(--surface, #fff);
  padding: 22px 16px;
  text-align: center;
  cursor: pointer;
  transition: all .2s ease;
  display: flex; flex-direction: column; align-items: center; gap: 10px;
  position: relative;
}
.theme-swatch:hover { transform: translateY(-3px); box-shadow: var(--shadow, 0 8px 20px rgba(0,0,0,.08)); }
.theme-swatch.active { border-color: var(--swatch-primary); }
.theme-swatch-preview {
  width: 60px; height: 60px; border-radius: 50%;
  background: linear-gradient(135deg, var(--swatch-primary), var(--swatch-accent));
  box-shadow: inset 0 0 0 4px rgba(255,255,255,.65);
}
.theme-swatch strong { font-size: .88rem; font-weight: 700; }
.theme-swatch span.theme-desc { font-size: .74rem; color: var(--muted, #6b7280); }
.theme-swatch-badge {
  position: absolute; top: 10px; right: 10px;
  font-size: .65rem; font-weight: 700; color: #fff;
  background: var(--swatch-primary);
  padding: 3px 9px; border-radius: 999px;
  opacity: 0; transform: scale(.8);
  transition: all .2s ease;
}
.theme-swatch.active .theme-swatch-badge { opacity: 1; transform: scale(1); }

@media (max-width: 700px) {
  .theme-swatch-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>
<div class="app-shell">
<?php renderSidebar('configuracion'); ?>
<div class="main-content">
<?php renderTopbar('Configuración ⚙️', 'Personaliza los colores de tu MindCare+'); ?>
<main class="page-body">

<div class="card mb-24">
  <div class="card-header">
    <div>
      <div class="card-title">🎨 Paleta de colores</div>
      <div class="card-subtitle">Elige el tono pastel con el que te sientas más cómodo. Se guarda automáticamente en este navegador.</div>
    </div>
  </div>

  <div class="theme-swatch-grid">

    <div class="theme-swatch" data-theme-option="lavanda"
         style="--swatch-primary:#9B87D4;--swatch-accent:#C9A0E0;"
         onclick="aplicarTemaColor('lavanda')">
      <span class="theme-swatch-badge">✓ Activo</span>
      <div class="theme-swatch-preview"></div>
      <strong>Lavanda</strong>
      <span class="theme-desc">Morado suave y orquídea</span>
    </div>

    <div class="theme-swatch" data-theme-option="glicina"
         style="--swatch-primary:#A98FE0;--swatch-accent:#D6A8E8;"
         onclick="aplicarTemaColor('glicina')">
      <span class="theme-swatch-badge">✓ Activo</span>
      <div class="theme-swatch-preview"></div>
      <strong>Glicina</strong>
      <span class="theme-desc">Violeta y lila pastel</span>
    </div>

    <div class="theme-swatch" data-theme-option="orquidea"
         style="--swatch-primary:#B98FE0;--swatch-accent:#DDA6E0;"
         onclick="aplicarTemaColor('orquidea')">
      <span class="theme-swatch-badge">✓ Activo</span>
      <div class="theme-swatch-preview"></div>
      <strong>Orquídea</strong>
      <span class="theme-desc">Morado intenso y rosado</span>
    </div>

    <div class="theme-swatch" data-theme-option="malva"
         style="--swatch-primary:#A79EE0;--swatch-accent:#C9A9E4;"
         onclick="aplicarTemaColor('malva')">
      <span class="theme-swatch-badge">✓ Activo</span>
      <div class="theme-swatch-preview"></div>
      <strong>Malva</strong>
      <span class="theme-desc">Morado azulado pastel</span>
    </div>

  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">👁️ Vista previa en vivo</div></div>
  <p style="color:var(--muted);font-size:.85rem;margin-bottom:16px;">
    Así se ven tus botones e insignias con el tono elegido — cambia en tiempo real:
  </p>
  <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <button class="btn btn-primary" type="button">Botón primario</button>
    <button class="btn btn-accent" type="button">Botón de acento</button>
    <span class="badge badge-primary">Insignia</span>
    <div class="progress-bar" style="width:140px;"><div class="progress-fill" style="width:70%"></div></div>
  </div>
</div>

</main>
</div>
</div>
<script src="dashboard.js"></script>
</body>
</html>
