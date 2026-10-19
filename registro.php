<?php
/**
 * MindCare+ · registro.php
 * ─────────────────────────────────────────────────────────────
 * Registra un nuevo usuario en la base de datos MySQL (Railway / Local).
 * Inicia la sesión automáticamente tras el registro y redirige a encuesta.php.
 * ─────────────────────────────────────────────────────────────
 */

declare(strict_types=1);

define('REDIRECT_REGISTRO',    'registro.html');
define('REDIRECT_ENCUESTA',    'encuesta.php');
define('MIN_PASSWORD_LENGTH',  8);
define('MIN_EDAD',             13);
define('MAX_EDAD',             120);

// ── 2. CABECERAS Y SESIÓN ────────────────────────────────────────
header('Content-Type: text/html; charset=UTF-8');
session_start();

// Si ya tiene sesión activa y completó la encuesta, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    if (!empty($_SESSION['encuesta_completada'])) {
        header('Location: dashboard.php');
    } else {
        header('Location: ' . REDIRECT_ENCUESTA);
    }
    exit;
}

// ── 3. SOLO POST ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . REDIRECT_REGISTRO);
    exit;
}

require_once __DIR__ . '/conexion.php';

function redirigirConError(string $codigo, ?string $detalle = null): never {
    $url = REDIRECT_REGISTRO . '?error=' . urlencode($codigo);
    if ($detalle !== null) {
        $url .= '&msg=' . urlencode($detalle);
    }
    header('Location: ' . $url);
    exit;
}

function limpiarTexto(string $valor): string {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

// ── 5. INPUTS ─────────────────────────────────────────────────────
$nombre   = limpiarTexto($_POST['nombre']   ?? '');
$apellido = limpiarTexto($_POST['apellido'] ?? '');
$email    = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
$edadRaw  = trim($_POST['edad'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm']  ?? '';

// ── 6. VALIDACIONES ───────────────────────────────────────────────
if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($confirm)) {
    redirigirConError('campos');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirigirConError('campos');
}

if (mb_strlen($nombre) > 100 || mb_strlen($apellido) > 100) {
    redirigirConError('campos');
}

if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
    redirigirConError('campos');
}

if ($password !== $confirm) {
    redirigirConError('contrasenas');
}

$edad = null;
if ($edadRaw !== '') {
    $edadInt = filter_var($edadRaw, FILTER_VALIDATE_INT);
    if ($edadInt === false || $edadInt < MIN_EDAD || $edadInt > MAX_EDAD) {
        redirigirConError('edad_invalida');
    }
    $edad = $edadInt;
}

// ── 7. PROCESO DE REGISTRO EN BASE DE DATOS ────────────────────────
try {
    // 1. Conexión dinámica con getenv() para Railway / local
    $pdo = conectarDB();

    // 2. Verificar si el correo ya existe en la tabla de usuarios
    $tabla = 'usuario';
    try {
        $stmtCheck = $pdo->prepare('SELECT id_usuario FROM usuario WHERE correo = :correo LIMIT 1');
        $stmtCheck->execute([':correo' => $email]);
        if ($stmtCheck->fetch() !== false) {
            redirigirConError('correo_existe');
        }
    } catch (PDOException $eCheck) {
        // Soporte en caso de que la tabla se haya creado como 'usuarios'
        if (str_contains($eCheck->getMessage(), "Table") && str_contains($eCheck->getMessage(), "doesn't exist")) {
            $tabla = 'usuarios';
            $stmtCheck = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1');
            $stmtCheck->execute([':correo' => $email]);
            if ($stmtCheck->fetch() !== false) {
                redirigirConError('correo_existe');
            }
        } else {
            throw $eCheck;
        }
    }

    $hashPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    if ($hashPassword === false) {
        throw new PDOException('Error al generar el hash seguro de la contraseña.');
    }

    // 3. Inserción con columnas del esquema: nombre, apellido, correo, contraseña, fecha_registro, edad
    $sqlInsert = "INSERT INTO {$tabla} (nombre, apellido, correo, contraseña, fecha_registro, edad)
                  VALUES (:nombre, :apellido, :correo, :contrasena, CURDATE(), :edad)";

    try {
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':nombre'     => $nombre,
            ':apellido'   => $apellido,
            ':correo'     => $email,
            ':contrasena' => $hashPassword,
            ':edad'       => $edad,
        ]);
    } catch (PDOException $eInsert) {
        // Fallback por si la columna contraseña fue definida sin eñe ('contrasena')
        if (str_contains($eInsert->getMessage(), "Unknown column 'contraseña'")) {
            $sqlInsertAlt = "INSERT INTO {$tabla} (nombre, apellido, correo, contrasena, fecha_registro, edad)
                             VALUES (:nombre, :apellido, :correo, :contrasena, CURDATE(), :edad)";
            $stmtInsert = $pdo->prepare($sqlInsertAlt);
            $stmtInsert->execute([
                ':nombre'     => $nombre,
                ':apellido'   => $apellido,
                ':correo'     => $email,
                ':contrasena' => $hashPassword,
                ':edad'       => $edad,
            ]);
        } else {
            throw $eInsert;
        }
    }

    $nuevoId = (int) $pdo->lastInsertId();
    error_log(sprintf('[MindCare+ Registro] Usuario registrado con éxito. ID: %d, Correo: %s', $nuevoId, $email));

    // 4. Iniciar sesión automáticamente para el nuevo usuario
    session_regenerate_id(true);
    $_SESSION['usuario_id']          = $nuevoId;
    $_SESSION['nombre']              = $nombre;
    $_SESSION['apellido']            = $apellido;
    $_SESSION['correo']              = $email;
    $_SESSION['es_admin']            = false;
    $_SESSION['autenticado_en']      = time();
    $_SESSION['encuesta_completada'] = false;

    // 5. Redirección limpia a 'encuesta.php'
    header('Location: ' . REDIRECT_ENCUESTA);
    exit;

} catch (PDOException $e) {
    // Manejo de excepciones sin provocar error 500 en el servidor
    error_log('[MindCare+ Registro] Error BD: ' . $e->getMessage());

    if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
        redirigirConError('correo_existe');
    }

    $mensajeDetallado = 'Error al registrar usuario: ' . $e->getMessage();
    redirigirConError('servidor', $mensajeDetallado);
}
