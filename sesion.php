<?php
// ── MINDCARE+ · sesion.php ──────────────────────────────────────
// Incluir en TODOS los archivos PHP del dashboard.
// ───────────────────────────────────────────────────────────────

// Fija la zona horaria de Colombia de forma explícita. Sin esto, PHP usa
// la zona horaria por defecto del servidor (a menudo UTC), lo que hace
// que "ahora" según PHP quede varias horas adelantado respecto a la hora
// real de Bogotá — causando, por ejemplo, que un recordatorio para "hoy
// más tarde" se rechace por parecer una fecha ya pasada.
date_default_timezone_set('America/Bogota');

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

function conectarDB(): PDO {
    // Lee las credenciales desde variables de entorno del servidor.
    // En producción (Railway, Render, etc.) se configuran en el panel.
    // En local (XAMPP) se usan los valores por defecto si no hay .env.
    $host    = getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1';
    $port    = getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306';
    $name    = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'mindcare';
    $user    = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
    $pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

$uid      = (int) $_SESSION['usuario_id'];
$nombre   = htmlspecialchars($_SESSION['nombre']   ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$apellido = htmlspecialchars($_SESSION['apellido'] ?? '',         ENT_QUOTES, 'UTF-8');
$esAdmin  = (bool)($_SESSION['es_admin'] ?? false);
$iniciales = strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1));

// ── VIDEO (YouTube embed) ─────────────────────────────────────────
// Convierte cualquier link de YouTube (watch?v=, youtu.be/, shorts/, embed/)
// en una URL de embed válida. Devuelve null si no reconoce el link.
function youtubeEmbedUrl(?string $url): ?string {
    $url = trim((string)$url);
    if ($url === '') return null;
    $patrones = [
        '~youtu\.be/([A-Za-z0-9_-]{11})~i',
        '~youtube\.com/watch\?v=([A-Za-z0-9_-]{11})~i',
        '~youtube\.com/embed/([A-Za-z0-9_-]{11})~i',
        '~youtube\.com/shorts/([A-Za-z0-9_-]{11})~i',
    ];
    foreach ($patrones as $p) {
        if (preg_match($p, $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
    }
    return null;
}

// ── ICONOS SVG ───────────────────────────────────────────────────
function svgIcon(string $name): string {
    $icons = [
        'dashboard'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'perfil'        => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'emociones'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
        'habitos'       => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
        'actividades'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
        'recursos'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'estadisticas'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
        'recordatorios' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
        'respiracion'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/></svg>',
        'lineas_ayuda'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'mascota'       => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-3.87 0-7-2.24-7-6 0-2.5 1.5-4 2-6 .5 1 2 2 5 2s4.5-1 5-2c.5 2 2 3.5 2 6 0 3.76-3.13 6-7 6Z"/><circle cx="8.5" cy="8.5" r="1.5"/><circle cx="15.5" cy="8.5" r="1.5"/><path d="M9 3c-1 .5-1.5 2-1 3M15 3c1 .5 1.5 2 1 3"/></svg>',
        'settings'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
        'admin'         => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>',
        'logout'        => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'bell'          => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
        'user'          => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'menu'          => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
    ];
    return $icons[$name] ?? '';
}

// ── SIDEBAR ──────────────────────────────────────────────────────
function renderSidebar(string $paginaActiva = ''): void {
    global $nombre, $apellido, $iniciales, $esAdmin;

    $pages = [
        'dashboard'     => ['icon' => 'dashboard',     'label' => 'Dashboard',       'href' => 'dashboard.php'],
        'mascota'       => ['icon' => 'mascota',       'label' => 'Mi Mascota',       'href' => 'mascota.php'],
        'perfil'        => ['icon' => 'perfil',        'label' => 'Mi Perfil',        'href' => 'perfil.php'],
        'emociones'     => ['icon' => 'emociones',     'label' => 'Control Emocional','href' => 'emociones.php'],
        'habitos'       => ['icon' => 'habitos',       'label' => 'Hábitos',          'href' => 'habitos.php'],
        'actividades'   => ['icon' => 'actividades',   'label' => 'Actividades',      'href' => 'actividades.php'],
        'respiracion'   => ['icon' => 'respiracion',   'label' => 'Respiración',      'href' => 'respiracion.php'],
        'lineas_ayuda'  => ['icon' => 'lineas_ayuda',  'label' => 'Líneas de Ayuda',  'href' => 'lineas_ayuda.php'],
        'recursos'      => ['icon' => 'recursos',      'label' => 'Recursos',         'href' => 'recursos.php'],
        'estadisticas'  => ['icon' => 'estadisticas',  'label' => 'Estadísticas',     'href' => 'estadisticas.php'],
        'recordatorios' => ['icon' => 'recordatorios', 'label' => 'Recordatorios',    'href' => 'recordatorios.php'],
        'configuracion' => ['icon' => 'settings',      'label' => 'Configuración',    'href' => 'configuracion.php'],
    ];
    $adminPages = [
        'panel_admin' => ['icon' => 'admin', 'label' => 'Panel Admin', 'href' => 'panel_admin.php'],
    ];

    echo '<aside class="sidebar" id="sidebar">';

    // Brand
    echo '<div class="sidebar-brand">';
    echo '<img src="logo-mindcare.png" alt="MindCare+" class="brand-mark"/>';
    echo '<span>Mind<em>Care</em>+</span>';
    echo '</div>';

    // Nav
    echo '<nav class="sidebar-nav">';
    echo '<span class="nav-label">Principal</span>';
    foreach ($pages as $key => $page) {
        $active = ($paginaActiva === $key) ? ' active' : '';
        $icon   = svgIcon($page['icon']);
        echo "<a href='" . htmlspecialchars($page['href'], ENT_QUOTES) . "' class='nav-item{$active}'>{$icon} {$page['label']}</a>";
    }
    if ($esAdmin) {
        echo '<span class="nav-label">Administración</span>';
        foreach ($adminPages as $key => $page) {
            $active = ($paginaActiva === $key) ? ' active' : '';
            $icon   = svgIcon($page['icon']);
            echo "<a href='" . htmlspecialchars($page['href'], ENT_QUOTES) . "' class='nav-item{$active}'>{$icon} {$page['label']}</a>";
        }
    }
    echo '</nav>';

    // Footer
    echo '<div class="sidebar-footer"><div class="user-pill">';
    echo "<div class='user-avatar'>{$iniciales}</div>";
    echo "<div class='user-info'><strong>{$nombre} {$apellido}</strong><span>" . ($esAdmin ? 'Administrador' : 'Usuario') . "</span></div>";
    echo "<a href='logout.php' class='btn-logout' title='Cerrar sesión'>" . svgIcon('logout') . "</a>";
    echo '</div></div>';

    echo '</aside>';
}

// ── TOPBAR ───────────────────────────────────────────────────────
function renderTopbar(string $titulo, string $subtitulo = ''): void {
    $tituloSafe    = htmlspecialchars($titulo,    ENT_QUOTES, 'UTF-8');
    $subtituloSafe = htmlspecialchars($subtitulo, ENT_QUOTES, 'UTF-8');

    echo '<header class="topbar">';
    echo '<div class="flex gap-10">';
    echo '<button class="sidebar-toggle" id="sidebarToggleBtn" aria-label="Menú">' . svgIcon('menu') . '</button>';
    echo "<div class='topbar-title'><h1>{$tituloSafe}</h1>" . ($subtituloSafe ? "<p>{$subtituloSafe}</p>" : '') . '</div>';
    echo '</div>';
    echo '<div class="topbar-actions">';
    echo '<button type="button" class="theme-toggle-btn" data-theme-toggle title="Cambiar a modo oscuro/claro" aria-label="Cambiar tema">';
    echo '<svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
    echo '<svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
    echo '</button>';
    echo '<a href="recordatorios.php" class="btn btn-ghost btn-sm" title="Recordatorios">' . svgIcon('bell') . '</a>';
    echo '<a href="perfil.php" class="btn btn-ghost btn-sm">' . svgIcon('user') . ' Mi perfil</a>';
    echo '</div>';
    echo '</header>';

    renderRecordatorioToasts();
}

// ── NOTIFICACIONES DE RECORDATORIOS (en toda la plataforma) ───────
/**
 * Imprime el contenedor de avisos emergentes + el script que programa
 * una notificación (dentro de la página, y nativa del navegador si hay
 * permiso) para cada recordatorio próximo del usuario. Se llama una
 * sola vez desde renderTopbar(), así que aparece en TODAS las páginas
 * que usan el layout estándar (dashboard, hábitos, mascota, etc.),
 * no solo en recordatorios.php.
 *
 * Limitación honesta: sigue siendo JavaScript del lado del cliente,
 * así que solo funciona mientras el usuario tenga alguna pestaña de
 * MindCare+ abierta en el navegador — no hay notificaciones push
 * reales que lleguen con el navegador cerrado.
 */
function renderRecordatorioToasts(): void {
    global $uid;
    static $yaRenderizado = false; // evita duplicar si renderTopbar() se llamara más de una vez
    if ($yaRenderizado) return;
    $yaRenderizado = true;

    $proximos = [];
    try {
        $pdoToasts = conectarDB();
        $margen = (new DateTime())->modify('-2 minutes')->format('Y-m-d H:i:s');
        $stmt = $pdoToasts->prepare("SELECT id_recordatorio, titulo, mensaje, fecha_hora FROM recordatorio WHERE id_usuario = ? AND fecha_hora >= ? ORDER BY fecha_hora ASC LIMIT 20");
        $stmt->execute([$uid, $margen]);
        $proximos = $stmt->fetchAll();
    } catch (Throwable $e) {
        // Si algo falla (p. ej. la tabla no existe todavía), la app sigue
        // funcionando con normalidad; simplemente no habrá avisos.
    }
    ?>
    <div id="recordatorioToasts" aria-live="polite"></div>
    <style>
    #recordatorioToasts {
      position: fixed; top: 90px; right: 20px; z-index: 999;
      display: flex; flex-direction: column; gap: 12px;
      max-width: min(90vw, 360px);
    }
    .recordatorio-toast {
      position: relative; overflow: hidden;
      background: var(--surface); border: 1px solid var(--line);
      border-radius: var(--radius); box-shadow: 0 16px 40px rgba(46,42,64,.18);
      padding: 16px 40px 18px 16px; display: flex; gap: 12px; align-items: flex-start;
      animation: toastIn .35s cubic-bezier(.34,1.56,.64,1);
    }
    .toast-icon-badge {
      width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      display: grid; place-items: center; font-size: 1.2rem;
      box-shadow: 0 6px 14px rgba(139,126,217,.4);
      animation: toastPulse 1.8s ease-in-out infinite;
    }
    .toast-body { flex: 1; min-width: 0; }
    .toast-body strong {
      display: block; font-size: .92rem; font-weight: 800; color: var(--ink);
      margin-bottom: 3px; overflow-wrap: anywhere;
    }
    .toast-body span { display: block; font-size: .8rem; color: var(--muted); line-height: 1.45; overflow-wrap: anywhere; }
    .toast-close {
      position: absolute; top: 10px; right: 10px;
      width: 24px; height: 24px; border-radius: 50%;
      background: none; border: none; color: var(--muted);
      cursor: pointer; font-size: .95rem; line-height: 1;
      display: grid; place-items: center;
      transition: background .15s ease, color .15s ease;
    }
    .toast-close:hover { background: var(--surface-soft); color: var(--ink); }
    .toast-progress {
      position: absolute; bottom: 0; left: 0; height: 3px; width: 100%;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      animation: toastCountdown 12s linear forwards;
    }
    @keyframes toastIn {
      from { opacity: 0; transform: translateX(28px) scale(.94); }
      to   { opacity: 1; transform: translateX(0) scale(1); }
    }
    @keyframes toastPulse {
      0%, 100% { box-shadow: 0 6px 14px rgba(139,126,217,.4); }
      50%      { box-shadow: 0 6px 20px rgba(139,126,217,.65); }
    }
    @keyframes toastCountdown { from { width: 100%; } to { width: 0%; } }
    @media (prefers-reduced-motion: reduce) {
      .recordatorio-toast, .toast-icon-badge, .toast-progress { animation: none; }
    }
    </style>
    <script>
    (function () {
      const proximos = <?= json_encode(array_map(function ($r) {
          return [
              'id'      => (int)$r['id_recordatorio'],
              'titulo'  => $r['titulo'],
              'mensaje' => $r['mensaje'],
              'fecha'   => str_replace(' ', 'T', $r['fecha_hora']),
          ];
      }, $proximos), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

      const MAX_ESPERA_MS = 24 * 60 * 60 * 1000; // no programar más allá de 24h

      if (proximos.length && 'Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
      }

      const contToasts = document.getElementById('recordatorioToasts');
      const topbar = document.querySelector('.topbar');
      function posicionarToasts() {
        if (!contToasts) return;
        const base = topbar ? topbar.getBoundingClientRect().bottom : 20;
        contToasts.style.top = Math.round(base + 14) + 'px';
      }
      posicionarToasts();
      window.addEventListener('resize', posicionarToasts);

      function mostrarToast(titulo, mensaje) {
        if (!contToasts) return;
        const toast = document.createElement('div');
        toast.className = 'recordatorio-toast';
        toast.innerHTML = `
          <div class="toast-icon-badge">🔔</div>
          <div class="toast-body"><strong></strong><span></span></div>
          <button type="button" class="toast-close" aria-label="Cerrar aviso">✕</button>
          <div class="toast-progress"></div>
        `;
        toast.querySelector('strong').textContent = titulo;
        toast.querySelector('span').textContent = mensaje || '¡Es hora de este recordatorio!';
        const cerrar = () => toast.remove();
        toast.querySelector('.toast-close').addEventListener('click', cerrar);
        setTimeout(cerrar, 12000);
        contToasts.appendChild(toast);
      }

      function dispararRecordatorio(r) {
        mostrarToast(r.titulo, r.mensaje);
        if ('Notification' in window && Notification.permission === 'granted') {
          try {
            new Notification('🔔 ' + r.titulo, {
              body: r.mensaje || 'Es hora de tu recordatorio en MindCare+',
              icon: 'logo-mindcare.png',
            });
          } catch (e) { /* algunos navegadores bloquean Notification fuera de gestos del usuario */ }
        }
      }

      proximos.forEach((r) => {
        const objetivo = new Date(r.fecha).getTime();
        const espera = objetivo - Date.now();
        if (espera <= MAX_ESPERA_MS) {
          setTimeout(() => dispararRecordatorio(r), Math.max(0, espera));
        }
      });
    })();
    </script>
    <?php
}

// ── CSRF ─────────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Solicitud inválida.');
    }
}
