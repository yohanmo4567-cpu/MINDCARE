<?php
/**
 * MindCare+ · login.php
 * ─────────────────────────────────────────────────────────────
 * Autentica al usuario contra la base de datos MySQL (XAMPP).
 * Diferencia entre Usuario normal y Administrador según la
 * tabla `administrador` del esquema mindcare.
 *
 * Flujo:
 *  1. Recibe POST: email + password
 *  2. Valida y sanitiza inputs
 *  3. Busca el usuario en `usuario`
 *  4. Verifica el hash de la contraseña
 *  5. Comprueba si existe registro en `administrador`
 *  6. Inicia sesión y redirige al dashboard correspondiente
 *
 * Requisitos: PHP >= 7.4, PDO, extensión pdo_mysql
 * ─────────────────────────────────────────────────────────────
 */

declare(strict_types=1);

// ── 1. CONFIGURACIÓN ────────────────────────────────────────────
// Las credenciales de BD se leen desde variables de entorno del servidor.
// En producción (Railway, Render, etc.) se configuran en el panel.
// En local (XAMPP) se usan los valores por defecto si no están definidas.

// Rutas de redirección
define('REDIRECT_LOGIN',          'login.html');
define('REDIRECT_DASHBOARD_USER', 'dashboard.php');
define('REDIRECT_DASHBOARD_ADMIN','panel_admin.php');

// ── 2. CABECERAS Y SESIÓN ────────────────────────────────────────
header('Content-Type: text/html; charset=UTF-8');
session_start();

// Si ya hay sesión activa, redirigir directamente
if (isset($_SESSION['usuario_id'])) {
    $destino = ($_SESSION['es_admin'] ?? false)
        ? REDIRECT_DASHBOARD_ADMIN
        : REDIRECT_DASHBOARD_USER;
    header('Location: ' . $destino);
    exit;
}

// ── 3. ACEPTAR SOLO POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . REDIRECT_LOGIN);
    exit;
}

// ── 4. FUNCIONES DE APOYO ────────────────────────────────────────

/**
 * Retorna una conexión PDO usando variables de entorno.
 */
function conectarDB(): PDO
{
    $host    = getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1';
    $port    = getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306';
    $name    = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'mindcare';
    $user    = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
    $pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $user, $pass, $opciones);
}

/**
 * Redirige al login con un código de error en la query string.
 */
function redirigirConError(string $codigo): never
{
    header('Location: ' . REDIRECT_LOGIN . '?error=' . urlencode($codigo));
    exit;
}

/**
 * Regenera el ID de sesión y establece las variables de sesión.
 * Registra la acción en la tabla `auditoria` si el usuario es admin.
 */
function iniciarSesionSegura(PDO $pdo, array $usuario, bool $esAdmin, ?array $admin): void
{
    // Regenerar ID para prevenir session fixation
    session_regenerate_id(true);

    $_SESSION['usuario_id']     = $usuario['id_usuario'];
    $_SESSION['nombre']         = $usuario['nombre'];
    $_SESSION['apellido']       = $usuario['apellido'];
    $_SESSION['correo']         = $usuario['correo'];
    $_SESSION['es_admin']       = $esAdmin;
    $_SESSION['autenticado_en'] = time();

    if ($esAdmin && $admin !== null) {
        $_SESSION['admin_id']       = $admin['id_admin'];
        $_SESSION['nivel_acceso']   = $admin['nivel_acceso'];
        $_SESSION['rol']            = $admin['rol'];

        // Registrar acción de login en auditoría
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO auditoria (id_admin, accion, fecha_accion)
                 VALUES (:id_admin, :accion, NOW())'
            );
            $stmt->execute([
                ':id_admin' => $admin['id_admin'],
                ':accion'   => 'LOGIN: Inicio de sesión de administrador',
            ]);
        } catch (PDOException) {
            // No interrumpir el login si falla la auditoría
        }
    }
}

// ── 5. OBTENER Y VALIDAR INPUTS ──────────────────────────────────

$email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '');
$password = trim($_POST['password'] ?? '');

// Validación básica de campos vacíos
if (empty($email) || empty($password)) {
    redirigirConError('campos');
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirigirConError('campos');
}

// ── 6. CONEXIÓN Y CONSULTAS ──────────────────────────────────────
try {
    $pdo = conectarDB();

    // 6a. Buscar usuario por correo
    $stmt = $pdo->prepare(
        'SELECT id_usuario, nombre, apellido, correo, contraseña
         FROM usuario
         WHERE correo = :correo
         LIMIT 1'
    );
    $stmt->execute([':correo' => $email]);
    $usuario = $stmt->fetch();

    // Usuario no encontrado → mismo mensaje que contraseña incorrecta
    // (evita enumeración de usuarios)
    if ($usuario === false) {
        redirigirConError('credenciales');
    }

    // 6b. Verificar contraseña con password_verify (bcrypt)
    if (!password_verify($password, $usuario['contraseña'])) {
        redirigirConError('credenciales');
    }

    // 6c. Comprobar si es administrador
    $stmtAdmin = $pdo->prepare(
        'SELECT id_admin, nivel_acceso, rol
         FROM administrador
         WHERE id_usuario = :id_usuario
         LIMIT 1'
    );
    $stmtAdmin->execute([':id_usuario' => $usuario['id_usuario']]);
    $admin   = $stmtAdmin->fetch();
    $esAdmin = ($admin !== false);

    // ── 7. INICIAR SESIÓN ────────────────────────────────────────
    iniciarSesionSegura($pdo, $usuario, $esAdmin, $esAdmin ? $admin : null);

    // ── 8. VERIFICAR ENCUESTA INICIAL ───────────────────────────
    $stmtEnc = $pdo->prepare(
        'SELECT id_encuesta FROM encuesta_inicial WHERE id_usuario = ? LIMIT 1'
    );
    $stmtEnc->execute([$usuario['id_usuario']]);
    $encuestaHecha = (bool) $stmtEnc->fetch();
    $_SESSION['encuesta_completada'] = $encuestaHecha;

    // ── 9. REDIRIGIR ─────────────────────────────────────────────
    if (!$encuestaHecha && !$esAdmin) {
        header('Location: encuesta_inicial.html');
    } else {
        $destino = $esAdmin ? REDIRECT_DASHBOARD_ADMIN : REDIRECT_DASHBOARD_USER;
        header('Location: ' . $destino);
    }
    exit;

} catch (PDOException $e) {
    // Loguear el error real sin exponerlo al usuario
    error_log('[MindCare+ Login] Error de base de datos: ' . $e->getMessage());
    redirigirConError('servidor');
}
