<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/conexion.php';

if (!$esAdmin) {
    header('Location: dashboard.php');
    exit;
}

$msg = '';
$msgTipo = '';
$totUsuarios = 0;
$totEmociones = 0;
$totHabitos = 0;
$totActividades = 0;
$usuarios = [];
$recursos = [];
$actividadesList = [];
$pdo = null;

try {
    $pdo = conectarDB();
} catch (PDOException $e) {
    error_log('[MindCare+ Admin] Error de conexión PDO: ' . $e->getMessage());
    $msg = 'No se pudo conectar a la base de datos de Railway. Verifica las credenciales.';
    $msgTipo = 'error';
}

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'crear_recurso') {
            $titulo    = trim($_POST['titulo']    ?? '');
            $contenido = trim($_POST['contenido'] ?? '');
            $idAdmin   = (int)($_SESSION['admin_id'] ?? $uid);
            if ($titulo && $contenido) {
                $stmt = $pdo->prepare("INSERT INTO recurso_educativo (id_admin, titulo, contenido, fecha_publicacion) VALUES (?, ?, ?, CURDATE())");
                $stmt->execute([$idAdmin, $titulo, $contenido]);
                $msg = '¡Recurso creado!'; $msgTipo = 'success';
            } else {
                $msg = 'El título y el contenido son requeridos.'; $msgTipo = 'error';
            }

        } elseif ($accion === 'crear_actividad') {
            $nombreAct = trim($_POST['nombre_actividad'] ?? '');
            $desc      = trim($_POST['descripcion']      ?? '');
            $dur       = (int)($_POST['duracion']        ?? 0);
            $urlVideo  = trim($_POST['url_video']        ?? '');
            if ($urlVideo !== '' && !filter_var($urlVideo, FILTER_VALIDATE_URL)) {
                $msg = 'La URL del video no es válida.'; $msgTipo = 'error';
            } elseif ($nombreAct) {
                $stmt = $pdo->prepare("INSERT INTO actividad (nombre_actividad, descripcion, duracion, url_video) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombreAct, $desc, $dur > 0 ? $dur : null, $urlVideo ?: null]);
                $msg = '¡Actividad creada!'; $msgTipo = 'success';
            } else {
                $msg = 'El nombre de la actividad es requerido.'; $msgTipo = 'error';
            }

        } elseif ($accion === 'eliminar_usuario') {
            $idUsuario = (int)($_POST['id_usuario'] ?? 0);
            // No permitir que el admin se elimine a sí mismo
            if ($idUsuario && $idUsuario !== $uid) {
                $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?")->execute([$idUsuario]);
                $msg = 'Usuario eliminado.'; $msgTipo = 'success';
            } else {
                $msg = 'No puedes eliminar tu propia cuenta desde aquí.'; $msgTipo = 'error';
            }

        } elseif ($accion === 'eliminar_actividad') {
            $idAct = (int)($_POST['id_actividad'] ?? 0);
            if ($idAct) {
                // Primero se borra el historial de participación asociado, para no violar la FK
                $pdo->prepare("DELETE FROM participacion_actividad WHERE id_actividad = ?")->execute([$idAct]);
                $pdo->prepare("DELETE FROM actividad WHERE id_actividad = ?")->execute([$idAct]);
                $msg = 'Actividad eliminada.'; $msgTipo = 'success';
            }

        } elseif ($accion === 'eliminar_recurso') {
            $idRec = (int)($_POST['id_recurso'] ?? 0);
            if ($idRec) {
                // Primero se borran las relaciones con categorías, para no violar la FK
                $pdo->prepare("DELETE FROM recurso_categoria WHERE id_recurso = ?")->execute([$idRec]);
                $pdo->prepare("DELETE FROM recurso_educativo WHERE id_recurso = ?")->execute([$idRec]);
                $msg = 'Recurso eliminado.'; $msgTipo = 'success';
            }
        }
    } catch (PDOException $e) {
        error_log('[MindCare+ Admin Action] Error en consulta SQL: ' . $e->getMessage());
        $msg = 'Ocurrió un error en la base de datos al realizar la acción. Por favor revisa los registros.';
        $msgTipo = 'error';
    }
}

if ($pdo !== null) {
    try {
        // Totales globales
        $totUsuarios    = (int)$pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
        $totEmociones   = (int)$pdo->query("SELECT COUNT(*) FROM emocion")->fetchColumn();
        $totHabitos     = (int)$pdo->query("SELECT COUNT(*) FROM habito")->fetchColumn();
        $totActividades = (int)$pdo->query("SELECT COUNT(*) FROM actividad")->fetchColumn();

        // Usuarios recientes
        $usuarios = $pdo->query("SELECT id_usuario, nombre, apellido, correo, fecha_registro, edad FROM usuario ORDER BY fecha_registro DESC LIMIT 10")->fetchAll();

        // Recursos
        $recursos = $pdo->query("SELECT * FROM recurso_educativo ORDER BY fecha_publicacion DESC LIMIT 10")->fetchAll();

        // Actividades
        $actividadesList = $pdo->query("SELECT * FROM actividad ORDER BY nombre_actividad")->fetchAll();
    } catch (PDOException $e) {
        error_log('[MindCare+ Admin Data] Error al consultar datos: ' . $e->getMessage());
        if (empty($msg)) {
            $msg = 'Hubo un inconveniente al cargar algunos datos del panel. La conexión está activa.';
            $msgTipo = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Panel Admin | MindCare+</title>
<script src="theme.js"></script>
<script src="color-themes.js"></script>
<link rel="stylesheet" href="dashboard.css"/>
</head>
<body>
<div class="app-shell">
<?php renderSidebar('panel_admin'); ?>
<div class="main-content">
<?php renderTopbar('Panel de Administración ⚙️', 'Gestión y supervisión de la plataforma'); ?>
<main class="page-body">

<?php if ($msg): ?>
<div class="alert alert-<?= $msgTipo === 'success' ? 'success' : 'error' ?> show"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Stats globales -->
<div class="stats-grid mb-24">
  <div class="stat-card"><div class="stat-icon green">👥</div><div class="stat-info"><div class="stat-value"><?= $totUsuarios ?></div><div class="stat-label">Usuarios registrados</div></div></div>
  <div class="stat-card"><div class="stat-icon blue">💚</div><div class="stat-info"><div class="stat-value"><?= $totEmociones ?></div><div class="stat-label">Emociones registradas</div></div></div>
  <div class="stat-card"><div class="stat-icon accent">🔁</div><div class="stat-info"><div class="stat-value"><?= $totHabitos ?></div><div class="stat-label">Hábitos creados</div></div></div>
  <div class="stat-card"><div class="stat-icon primary">🧘</div><div class="stat-info"><div class="stat-value"><?= $totActividades ?></div><div class="stat-label">Actividades disponibles</div></div></div>
</div>

<div class="grid-2 mb-24">
  <!-- Crear recurso educativo -->
  <div class="card">
    <div class="card-header"><div class="card-title">📚 Crear recurso educativo</div></div>
    <form method="POST">
      <input type="hidden" name="accion" value="crear_recurso"/>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
      <div class="form-group">
        <label class="form-label">Título</label>
        <input type="text" name="titulo" class="form-input" placeholder="Título del recurso" required maxlength="200"/>
      </div>
      <div class="form-group">
        <label class="form-label">Contenido</label>
        <textarea name="contenido" class="form-textarea" placeholder="Escribe el contenido del recurso..." required></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">📚 Publicar recurso</button>
    </form>
  </div>

  <!-- Crear actividad -->
  <div class="card">
    <div class="card-header"><div class="card-title">🧘 Crear actividad de bienestar</div></div>
    <form method="POST">
      <input type="hidden" name="accion" value="crear_actividad"/>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
      <div class="form-group">
        <label class="form-label">Nombre de la actividad</label>
        <input type="text" name="nombre_actividad" class="form-input" placeholder="Ej: Respiración profunda" required maxlength="150"/>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-textarea" style="min-height:70px;" placeholder="¿En qué consiste?" maxlength="500"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Duración (minutos)</label>
        <input type="number" name="duracion" class="form-input" placeholder="Ej: 15" min="1" max="480"/>
      </div>
      <div class="form-group">
        <label class="form-label">Link del video (YouTube, opcional)</label>
        <input type="url" name="url_video" class="form-input" placeholder="https://www.youtube.com/watch?v=..." maxlength="255"/>
        <small style="color:var(--muted);font-size:.75rem;">Se muestra como video incrustado en la tarjeta de la actividad.</small>
      </div>
      <button type="submit" class="btn btn-accent" style="width:100%">🧘 Crear actividad</button>
    </form>
  </div>
</div>

<!-- Usuarios registrados -->
<div class="card mb-24">
  <div class="card-header">
    <div class="card-title">👥 Usuarios registrados</div>
    <span class="badge badge-primary"><?= $totUsuarios ?> usuarios</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nombre</th><th>Correo</th><th>Edad</th><th>Registro</th></tr></thead>
      <tbody>
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td style="color:var(--muted);"><?= (int)$u['id_usuario'] ?></td>
          <td><strong><?= htmlspecialchars($u['nombre']) ?> <?= htmlspecialchars($u['apellido']) ?></strong></td>
          <td style="color:var(--muted);"><?= htmlspecialchars($u['correo']) ?></td>
          <td><?= $u['edad'] ? (int)$u['edad'] : '—' ?></td>
          <td><?= $u['fecha_registro'] ? date('d/m/Y', strtotime($u['fecha_registro'])) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recursos publicados -->
<div class="card">
  <div class="card-header"><div class="card-title">📚 Recursos publicados</div></div>
  <?php if ($recursos): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Título</th><th>Publicado</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recursos as $r): ?>
        <tr>
          <td><strong><?= htmlspecialchars($r['titulo']) ?></strong></td>
          <td><?= date('d/m/Y', strtotime($r['fecha_publicacion'])) ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('¿Eliminar el recurso «<?= htmlspecialchars(addslashes($r['titulo'])) ?>»? Esta acción no se puede deshacer.')">
              <input type="hidden" name="accion" value="eliminar_recurso"/>
              <input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>"/>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
              <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">🗑</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-state"><div class="empty-icon">📚</div><p>Aún no hay recursos publicados.</p></div>
  <?php endif; ?>
</div>

<!-- Actividades creadas -->
<div class="card mb-24">
  <div class="card-header">
    <div class="card-title">🧘 Actividades creadas</div>
    <span class="badge badge-primary"><?= $totActividades ?> actividades</span>
  </div>
  <?php if ($actividadesList): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nombre</th><th>Duración</th><th>Video</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($actividadesList as $a): ?>
        <tr>
          <td><strong><?= htmlspecialchars($a['nombre_actividad']) ?></strong></td>
          <td><?= !empty($a['duracion']) ? (int)$a['duracion'] . ' min' : '—' ?></td>
          <td><?= !empty($a['url_video']) ? '<span class="badge badge-green">▶ Sí</span>' : '<span class="badge badge-blue">—</span>' ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('¿Eliminar la actividad «<?= htmlspecialchars(addslashes($a['nombre_actividad'])) ?>»? Se borrará también el historial de participación de los usuarios.')">
              <input type="hidden" name="accion" value="eliminar_actividad"/>
              <input type="hidden" name="id_actividad" value="<?= (int)$a['id_actividad'] ?>"/>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"/>
              <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">🗑</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-state"><div class="empty-icon">🧘</div><p>Aún no hay actividades creadas.</p></div>
  <?php endif; ?>
</div>

</main>
</div>
</div>
<script src="dashboard.js"></script>
</body>
</html>
