<?php
/**
 * ──────────────────────────────────────────────────────────────────
 * MINDCARE+ · lineas_ayuda.php
 * ──────────────────────────────────────────────────────────────────
 * Vista del módulo "Líneas de Ayuda". Sigue el mismo esqueleto que
 * dashboard.php / recursos.php / emociones.php: sesion.php + conexión
 * + render de sidebar/topbar reutilizados + <main class="page-body">.
 *
 * Toda la lógica de datos vive en lineas_ayuda_data.php (separación
 * vista/lógica). Esta vista solo orquesta y pinta.
 * ──────────────────────────────────────────────────────────────────
 */
require_once 'sesion.php';
require_once 'lineas_ayuda_data.php';

$pdo = conectarDB();

// Nivel de riesgo actual del usuario (ver punto de integración futura
// en lineas_ayuda_data.php::determinarNivelRiesgo)
$nivelRiesgo = determinarNivelRiesgo($pdo, $uid);
$esEmergencia = ($nivelRiesgo === NIVEL_ROJO);

// Catálogo de líneas + filtro opcional por población objetivo (?poblacion=)
$filtroPoblacion = trim($_GET['poblacion'] ?? '');
$lineas          = filtrarLineasPorPoblacion(obtenerLineasAyuda(), $filtroPoblacion);

// Solo se necesitan si el nivel lo amerita (evita trabajo innecesario)
$consejos            = ($nivelRiesgo === NIVEL_VERDE) ? obtenerConsejosAutocuidado() : [];
$contactosEmergencia = $esEmergencia ? obtenerContactosEmergencia($pdo, $uid) : [];

// Chips de población objetivo para el filtro (etiqueta => valor)
$poblacionesFiltro = [
    ''                  => 'Todas',
    'general'           => 'General',
    'ninos'             => 'Niños y niñas',
    'adolescentes'      => 'Adolescentes',
    'adultos'           => 'Adultos',
    'mujeres'           => 'Mujeres',
    'hombres'           => 'Hombres',
    'consumo_spa'       => 'Consumo de sustancias',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Líneas de Ayuda | MindCare+</title>
<script src="theme.js"></script>
<script src="color-themes.js"></script>
<link rel="stylesheet" href="dashboard.css"/>
<style>
/* ── Estilos propios del módulo Líneas de Ayuda ──────────────────
   Usan exclusivamente las variables de dashboard.css, por lo que
   heredan el modo oscuro automáticamente sin reglas adicionales. */

.linea-card {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.linea-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: var(--primary); }
.linea-header { display: flex; align-items: center; gap: 12px; }
.linea-icon {
  width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
  display: grid; place-items: center; font-size: 1.35rem;
}
.linea-icon.color-primary { background: var(--primary-light); color: var(--primary-dark); }
.linea-icon.color-red     { background: var(--red-light);     color: var(--red); }
.linea-icon.color-blue    { background: var(--blue-light);    color: #6C5CAE; }
.linea-icon.color-green   { background: var(--green-light);   color: #7657A8; }
.linea-icon.color-purple  { background: var(--purple-light);  color: #6b3faa; }
.linea-icon.color-rose    { background: var(--rose-light);    color: #8C4B96; }
.linea-icon.color-accent  { background: var(--accent-light);  color: #8A4FAE; }

.linea-nombre { font-weight: 700; font-size: 1rem; line-height: 1.25; }
.linea-numero { font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); letter-spacing: .02em; }
.linea-meta   { display: flex; flex-direction: column; gap: 3px; font-size: .74rem; color: var(--muted); }
.linea-meta strong { color: var(--ink); font-weight: 600; }
.linea-desc   { font-size: .85rem; color: var(--muted); line-height: 1.55; flex: 1; }
.linea-actions { display: flex; gap: 8px; margin-top: 6px; }
.linea-actions .btn { flex: 1; justify-content: center; }

.btn-whatsapp { background: #25D366; color: #fff; }
.btn-whatsapp:hover { background: #1DA851; transform: translateY(-1px); box-shadow: var(--shadow); }

/* Filtro por población (mismo patrón visual que recursos.php) */
.filtro-poblacion { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

/* Banner AMARILLO */
.alerta-amarilla {
  background: var(--accent-light); border: 1px solid var(--accent);
  border-radius: var(--radius); padding: 22px 24px; margin-bottom: 24px;
  display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
}
.alerta-amarilla .alerta-icono { font-size: 2.2rem; }
.alerta-amarilla h3 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
.alerta-amarilla p  { font-size: .85rem; color: var(--muted); margin-bottom: 0; }
.alerta-amarilla .btn { margin-left: auto; }

/* Consejos de autocuidado (VERDE) */
.consejo-card {
  background: var(--surface-soft); border: 1px solid var(--line);
  border-radius: var(--radius); padding: 18px; display: flex; flex-direction: column; gap: 8px;
  transition: transform .22s ease, box-shadow .22s ease;
}
.consejo-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.consejo-card .emoji { font-size: 1.8rem; }
.consejo-card strong { font-size: .9rem; }
.consejo-card span   { font-size: .8rem; color: var(--muted); line-height: 1.5; }

/* Pantalla de prioridad máxima (ROJO) — navegación secundaria oculta */
.emergencia-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: var(--bg); }
.emergencia-wrap { max-width: 620px; width: 100%; text-align: center; }
.emergencia-wrap .emoji-grande { font-size: 3rem; margin-bottom: 10px; }
.emergencia-wrap h1 { color: var(--red); font-size: 1.4rem; font-weight: 800; margin-bottom: 10px; }
.emergencia-wrap p.lead { color: var(--muted); font-size: .92rem; margin-bottom: 26px; line-height: 1.6; }

.btn-emergencia-123 {
  display: block; width: 100%; padding: 26px; font-size: 1.7rem; font-weight: 800;
  background: var(--red); color: #fff; border-radius: var(--radius);
  text-decoration: none; box-shadow: var(--shadow-lg);
  transition: transform .18s ease, background .18s ease;
}
.btn-emergencia-123:hover { transform: scale(1.02); background: #c94654; }

.emergencia-secundario { margin-top: 16px; }
.emergencia-contactos { margin-top: 34px; text-align: left; }
.emergencia-contactos h3 { font-size: .95rem; font-weight: 700; margin-bottom: 12px; }
.contacto-item {
  background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius-sm);
  padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;
}
.emergencia-salida { margin-top: 40px; }
.emergencia-salida a { font-size: .78rem; color: var(--muted); }

@media (max-width: 600px) {
  .btn-emergencia-123 { font-size: 1.3rem; padding: 20px; }
  .alerta-amarilla { flex-direction: column; align-items: flex-start; }
  .alerta-amarilla .btn { margin-left: 0; width: 100%; }
}
</style>
</head>
<body>

<?php if ($esEmergencia): ?>
<!-- ═══════════════════════════════════════════════════════════════
     PANTALLA DE PRIORIDAD MÁXIMA (nivel ROJO)
     Se omite intencionalmente renderSidebar()/renderTopbar(): esto
     bloquea la navegación secundaria y enfoca toda la atención en
     obtener ayuda inmediata.
     ═══════════════════════════════════════════════════════════════ -->
<div class="emergencia-shell">
  <div class="emergencia-wrap">
    <div class="emoji-grande">🚨</div>
    <h1>Tu seguridad es lo más importante ahora mismo</h1>
    <p class="lead">
      Si sientes que tu vida o tu integridad están en riesgo, no esperes:
      busca ayuda inmediata. No estás solo/a, hay personas listas para escucharte.
    </p>

    <a href="tel:123" class="btn-emergencia-123">📞 Llamar ahora a la Línea 123</a>

    <div class="emergencia-secundario">
      <a href="tel:106" class="btn btn-ghost btn-sm">También puedes llamar a la Línea 106 (apoyo psicológico)</a>
    </div>

    <div class="emergencia-contactos">
      <h3>Tus contactos de emergencia</h3>
      <?php if ($contactosEmergencia): ?>
        <?php foreach ($contactosEmergencia as $c): ?>
          <div class="contacto-item">
            <div>
              <strong><?= htmlspecialchars($c['nombre']) ?></strong>
              <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($c['parentesco'] ?? '') ?></div>
            </div>
            <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $c['telefono'])) ?>" class="btn btn-primary btn-sm">Llamar</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state" style="padding:20px 0;">
          <p>Aún no tienes contactos de emergencia registrados.</p>
          <a href="perfil.php" class="btn btn-ghost btn-sm" style="margin-top:8px;">Agregar en Mi Perfil</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="emergencia-salida">
      <a href="dashboard.php">Continuar al dashboard</a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════════════════════════
     VISTA NORMAL (niveles VERDE / AMARILLO) — layout estándar
     ═══════════════════════════════════════════════════════════════ -->
<div class="app-shell">
<?php renderSidebar('lineas_ayuda'); ?>
<div class="main-content">
<?php renderTopbar('Líneas de Ayuda 📞', 'Apoyo psicológico y de emergencia, cuando lo necesites'); ?>
<main class="page-body">

  <?php if ($nivelRiesgo === NIVEL_AMARILLO): ?>
    <!-- Recomendación prioritaria: contactar Línea 106 -->
    <div class="alerta-amarilla">
      <div class="alerta-icono">💛</div>
      <div>
        <h3>Notamos que podrías necesitar apoyo ahora</h3>
        <p>Hablar con un profesional puede ayudar. La Línea 106 ofrece escucha y orientación gratuita, las 24 horas.</p>
      </div>
      <a href="tel:106" class="btn btn-primary">📞 Llamar a la Línea 106</a>
    </div>
  <?php elseif ($nivelRiesgo === NIVEL_VERDE && $consejos): ?>
    <!-- Consejos de autocuidado -->
    <div class="card mb-24">
      <div class="card-header">
        <div>
          <div class="card-title">🌿 Consejos de autocuidado</div>
          <div class="card-subtitle">Tu estado actual se ve estable; estas prácticas ayudan a mantenerlo así</div>
        </div>
      </div>
      <div class="grid-3">
        <?php foreach ($consejos as $c): ?>
          <div class="consejo-card">
            <div class="emoji"><?= $c['emoji'] ?></div>
            <strong><?= htmlspecialchars($c['titulo']) ?></strong>
            <span><?= htmlspecialchars($c['texto']) ?></span>
            <?php if ($c['href']): ?>
              <a href="<?= htmlspecialchars($c['href']) ?>" class="btn btn-ghost btn-sm" style="margin-top:auto;"><?= htmlspecialchars($c['accion']) ?></a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Filtro por población objetivo -->
  <div class="card mb-24">
    <div class="filtro-poblacion">
      <span style="font-weight:600;font-size:.85rem;">Filtrar por:</span>
      <?php foreach ($poblacionesFiltro as $valor => $etiqueta): ?>
        <a href="?poblacion=<?= urlencode($valor) ?>" class="btn btn-sm <?= $filtroPoblacion === $valor ? 'btn-primary' : 'btn-ghost' ?>"><?= htmlspecialchars($etiqueta) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Catálogo de líneas de ayuda -->
  <?php if ($lineas): ?>
  <div class="grid-3">
    <?php foreach ($lineas as $l): ?>
      <div class="linea-card">
        <div class="linea-header">
          <div class="linea-icon color-<?= htmlspecialchars($l['color']) ?>">
            <?= $l['emergencia'] ? '🚨' : '💬' ?>
          </div>
          <div>
            <div class="linea-nombre"><?= htmlspecialchars($l['nombre']) ?></div>
            <div class="linea-numero"><?= htmlspecialchars($l['numero_display']) ?></div>
          </div>
        </div>

        <div class="linea-meta">
          <span><strong>Cobertura:</strong> <?= htmlspecialchars($l['cobertura']) ?></span>
          <span><strong>Horario:</strong> <?= htmlspecialchars($l['horario']) ?></span>
          <span><strong>Población:</strong> <?= htmlspecialchars($l['poblacion']) ?></span>
        </div>

        <p class="linea-desc"><?= htmlspecialchars($l['descripcion']) ?></p>

        <div class="linea-actions">
          <a href="tel:<?= htmlspecialchars($l['numero_tel']) ?>" class="btn btn-primary btn-sm">📞 Llamar</a>
          <?php if ($l['whatsapp']): ?>
            <a href="<?= htmlspecialchars(generarLinkWhatsApp($l['whatsapp'])) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">💬 WhatsApp</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="card"><div class="empty-state"><div class="empty-icon">🔍</div><p>No hay líneas para el filtro seleccionado.</p></div></div>
  <?php endif; ?>

</main>
</div>
</div>
<script src="dashboard.js"></script>
<?php endif; // fin de la vista normal ?>

<script>
// Sidebar toggle móvil — mismo patrón que dashboard.php (no aplica en modo emergencia)
const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebar = document.getElementById('sidebar');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', e => {
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
}
</script>
</body>
</html>
