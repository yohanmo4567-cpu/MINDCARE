<?php
require_once 'sesion.php';
$pdo = conectarDB();

// ── Completar/descompletar un hábito sin salir del dashboard ─────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['accion'] ?? '', ['completar', 'descompletar'], true)) {
    verifyCsrf();
    $idHabito = (int)($_POST['id_habito'] ?? 0);
    $own = $pdo->prepare("SELECT id_habito FROM habito WHERE id_habito = ? AND id_usuario = ?");
    $own->execute([$idHabito, $uid]);

    if ($own->fetch()) {
        if ($_POST['accion'] === 'completar') {
            $check = $pdo->prepare("SELECT id_seguimiento FROM seguimiento_habito WHERE id_habito = ? AND fecha = CURDATE()");
            $check->execute([$idHabito]);
            if (!$check->fetch()) {
                $pdo->prepare("INSERT INTO seguimiento_habito (id_habito, fecha, progreso) VALUES (?, CURDATE(), 100)")->execute([$idHabito]);
            }
        } else { // descompletar
            $pdo->prepare("DELETE FROM seguimiento_habito WHERE id_habito = ? AND fecha = CURDATE()")->execute([$idHabito]);
        }
    }
    // Redirige a sí misma para evitar reenvíos duplicados al recargar (patrón POST-Redirect-GET)
    header('Location: dashboard.php');
    exit;
}

$stmtEmo = $pdo->prepare("SELECT COUNT(*) FROM emocion WHERE id_usuario = ?");
$stmtEmo->execute([$uid]);
$totalEmociones = $stmtEmo->fetchColumn();

$stmtHab = $pdo->prepare("SELECT COUNT(*) FROM habito WHERE id_usuario = ?");
$stmtHab->execute([$uid]);
$totalHabitos = $stmtHab->fetchColumn();

$stmtSeg = $pdo->prepare("SELECT COUNT(*) FROM seguimiento_habito sh JOIN habito h ON sh.id_habito = h.id_habito WHERE h.id_usuario = ? AND sh.fecha = CURDATE()");
$stmtSeg->execute([$uid]);
$habitosHoy = $stmtSeg->fetchColumn();

$stmtAct = $pdo->prepare("SELECT COUNT(*) FROM participacion_actividad WHERE id_usuario = ?");
$stmtAct->execute([$uid]);
$totalActividades = $stmtAct->fetchColumn();

$stmtUltEmo = $pdo->prepare("SELECT e.observacion, e.fecha_registro, GROUP_CONCAT(ee.nombre_estado SEPARATOR ', ') AS estados FROM emocion e LEFT JOIN emocion_estado es2 ON e.id_emocion = es2.id_emocion LEFT JOIN estado_emocional ee ON es2.id_estado = ee.id_estado WHERE e.id_usuario = ? GROUP BY e.id_emocion ORDER BY e.fecha_registro DESC LIMIT 1");
$stmtUltEmo->execute([$uid]);
$ultimaEmocion = $stmtUltEmo->fetch();

$stmtPend = $pdo->prepare("SELECT h.id_habito, h.nombre_habito, h.frecuencia FROM habito h WHERE h.id_usuario = ? AND h.id_habito NOT IN (SELECT id_habito FROM seguimiento_habito WHERE fecha = CURDATE()) LIMIT 4");
$stmtPend->execute([$uid]);
$habPendientes = $stmtPend->fetchAll();

$stmtRec = $pdo->prepare("SELECT * FROM recordatorio WHERE id_usuario = ? AND fecha_hora >= NOW() ORDER BY fecha_hora ASC LIMIT 3");
$stmtRec->execute([$uid]);
$proximosRec = $stmtRec->fetchAll();

// Emociones últimos 7 días para mini-gráfica
$stmtEmoSem = $pdo->prepare("SELECT DATE(fecha_registro) as dia, COUNT(*) as total FROM emocion WHERE id_usuario = ? AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY dia ORDER BY dia");
$stmtEmoSem->execute([$uid]);
$emocionSemana = $stmtEmoSem->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard | MindCare+</title>
<script src="theme.js"></script>
<script src="color-themes.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="dashboard.css"/>
</head>
<body>
<div class="app-shell">
<?php renderSidebar('dashboard'); ?>
<div class="main-content">
<?php renderTopbar("Hola, {$nombre} 👋", date('l, d \d\e F \d\e Y')); ?>
<main class="page-body">

  <!-- Bienvenida -->
  <div class="welcome-banner">
    <div class="welcome-text">
      <h2>¡Bienvenido de nuevo, <?= htmlspecialchars($nombre) ?>!</h2>
      <p>Aquí tienes un resumen de tu bienestar hoy. Recuerda registrar cómo te sientes.</p>
    </div>
    <a href="emociones.php" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
      Registrar emoción
    </a>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon green">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value"><?= $totalEmociones ?></div>
        <div class="stat-label">Emociones registradas</div>
        <div class="stat-trend up">↑ Total acumulado</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon blue">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value"><?= $totalHabitos ?></div>
        <div class="stat-label">Hábitos activos</div>
        <div class="stat-trend up">↑ En progreso</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon accent">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value"><?= $habitosHoy ?></div>
        <div class="stat-label">Completados hoy</div>
        <div class="stat-trend up">↑ Hoy</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon primary">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value"><?= $totalActividades ?></div>
        <div class="stat-label">Actividades realizadas</div>
        <div class="stat-trend up">↑ Total</div>
      </div>
    </div>
  </div>

  <!-- Fila principal -->
  <div class="grid-2 mb-24">

    <!-- Última emoción -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--green)"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            Último estado emocional
          </div>
          <div class="card-subtitle">Tu registro más reciente</div>
        </div>
        <a href="emociones.php" class="btn btn-primary btn-sm">+ Nuevo</a>
      </div>
      <?php if ($ultimaEmocion): ?>
        <div class="emocion-card">
          <div class="emocion-estados">
            <?php foreach (explode(', ', $ultimaEmocion['estados'] ?? 'Sin estado') as $est): ?>
              <span class="badge badge-primary"><?= htmlspecialchars(trim($est)) ?></span>
            <?php endforeach; ?>
          </div>
          <?php if ($ultimaEmocion['observacion']): ?>
            <p class="emocion-obs">"<?= htmlspecialchars($ultimaEmocion['observacion']) ?>"</p>
          <?php endif; ?>
          <div class="text-muted" style="margin-top:10px;font-size:.78rem;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?= date('d/m/Y H:i', strtotime($ultimaEmocion['fecha_registro'])) ?>
          </div>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">😶</div>
          <p>Aún no has registrado emociones.</p>
          <a href="emociones.php" class="btn btn-primary btn-sm" style="margin-top:12px;">Registrar ahora</a>
        </div>
      <?php endif; ?>

      <!-- Mini gráfica de 7 días -->
      <div class="mini-chart-wrap">
        <div class="mini-chart-label">Actividad emocional — últimos 7 días</div>
        <div class="mini-chart-bars">
          <?php
          for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
            $val = $emocionSemana[$dia] ?? 0;
            $h   = $val > 0 ? min($val * 20, 100) : 6;
            $dayName = date('D', strtotime($dia));
            echo "<div class='mini-bar-wrap'>";
            echo "  <div class='mini-bar' style='height:{$h}px;' title='{$val} registros'></div>";
            echo "  <span class='mini-bar-label'>{$dayName}</span>";
            echo "</div>";
          }
          ?>
        </div>
      </div>
    </div>

    <!-- Hábitos pendientes -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--blue)"><path d="M3 3h18v18H3z"/><path d="M3 9h18M9 21V9"/></svg>
            Hábitos pendientes hoy
          </div>
          <div class="card-subtitle">Tareas sin completar</div>
        </div>
        <a href="habitos.php" class="btn btn-ghost btn-sm">Ver todos</a>
      </div>

      <?php if ($habPendientes): ?>
        <div class="habits-list">
          <?php foreach ($habPendientes as $h): ?>
            <form method="POST" class="habit-item" style="display:flex;">
              <input type="hidden" name="accion" value="completar"/>
              <input type="hidden" name="id_habito" value="<?= (int)$h['id_habito'] ?>"/>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
              <button type="submit" class="habit-check" style="padding:0;appearance:none;-webkit-appearance:none;"
                      title="Marcar como completado hoy"
                      aria-label="Marcar «<?= htmlspecialchars($h['nombre_habito']) ?>» como completado hoy"></button>
              <div class="habit-info">
                <strong><?= htmlspecialchars($h['nombre_habito']) ?></strong>
                <span><?= htmlspecialchars($h['frecuencia']) ?></span>
              </div>
              <button type="submit" class="btn btn-sm btn-primary" style="font-size:.72rem;padding:4px 10px;">Completar</button>
            </form>
          <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:16px;">
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $totalHabitos > 0 ? round(($habitosHoy / max($totalHabitos,1)) * 100) : 0 ?>%"></div></div>
          <p style="font-size:.78rem;color:var(--muted);margin-top:6px;"><?= $habitosHoy ?> de <?= $totalHabitos ?> hábitos completados hoy</p>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">🎉</div>
          <p>¡Todos los hábitos completados hoy!</p>
        </div>
      <?php endif; ?>

      <!-- Próximos recordatorios -->
      <?php if ($proximosRec): ?>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--line);">
          <div style="font-size:.8rem;font-weight:700;margin-bottom:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Próximos recordatorios</div>
          <?php foreach ($proximosRec as $r): ?>
            <div class="rec-item">
              <div class="rec-icon">🔔</div>
              <div>
                <strong style="font-size:.85rem;"><?= htmlspecialchars($r['titulo']) ?></strong>
                <div style="font-size:.75rem;color:var(--muted);"><?= date('d/m H:i', strtotime($r['fecha_hora'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Accesos rápidos -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Accesos rápidos
      </div>
    </div>
    <div class="quick-grid">
      <a href="mascota.php" class="quick-card quick-green">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-3.87 0-7-2.24-7-6 0-2.5 1.5-4 2-6 .5 1 2 2 5 2s4.5-1 5-2c.5 2 2 3.5 2 6 0 3.76-3.13 6-7 6Z"/><circle cx="8.5" cy="8.5" r="1.5"/><circle cx="15.5" cy="8.5" r="1.5"/><path d="M9 3c-1 .5-1.5 2-1 3M15 3c1 .5 1.5 2 1 3"/></svg>
        </div>
        <strong>Mi Mascota</strong>
        <span>Cuida a tu gato</span>
      </a>
      <a href="perfil.php" class="quick-card quick-blue">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <strong>Mi Perfil</strong>
        <span>Tus datos y cuenta</span>
      </a>
      <a href="emociones.php" class="quick-card quick-primary">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
        </div>
        <strong>Control Emocional</strong>
        <span>Registra tu estado</span>
      </a>
      <a href="habitos.php" class="quick-card quick-accent">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <strong>Hábitos y Rutinas</strong>
        <span>Gestiona tus hábitos</span>
      </a>
      <a href="actividades.php" class="quick-card quick-purple">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
        </div>
        <strong>Actividades</strong>
        <span>Bienestar activo</span>
      </a>
      <a href="respiracion.php" class="quick-card quick-rose">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/></svg>
        </div>
        <strong>Respiración</strong>
        <span>Ejercicio guiado</span>
      </a>
      <a href="lineas_ayuda.php" class="quick-card quick-green">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <strong>Líneas de Ayuda</strong>
        <span>Apoyo y emergencia</span>
      </a>
      <a href="recursos.php" class="quick-card quick-blue">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <strong>Recursos</strong>
        <span>Aprende y practica</span>
      </a>
      <a href="estadisticas.php" class="quick-card quick-primary">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <strong>Estadísticas</strong>
        <span>Tu progreso</span>
      </a>
      <a href="recordatorios.php" class="quick-card quick-accent">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <strong>Recordatorios</strong>
        <span>Gestiona alertas</span>
      </a>
      <a href="configuracion.php" class="quick-card quick-purple">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </div>
        <strong>Configuración</strong>
        <span>Colores y tema</span>
      </a>
      <?php if ($esAdmin): ?>
      <a href="panel_admin.php" class="quick-card quick-rose">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
        </div>
        <strong>Panel Admin</strong>
        <span>Gestión de la plataforma</span>
      </a>
      <?php endif; ?>
    </div>
  </div>

</main>
</div>
</div>

<script>
// Sidebar toggle móvil
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
// Checkmarks visuales (solo decorativos en dashboard)
document.querySelectorAll('.habit-check').forEach(btn => {
  btn.addEventListener('click', () => btn.classList.toggle('done'));
});
</script>
<script src="dashboard.js"></script>
</body>
</html>
