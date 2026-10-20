<?php
declare(strict_types=1);

$appEnv = strtolower(trim((string) (getenv('APP_ENV') ?: 'production')));
$debugMode = in_array($appEnv, ['local', 'development'], true);

ini_set('display_errors', $debugMode ? '1' : '0');
ini_set('display_startup_errors', $debugMode ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function registrarError(Throwable $error, string $contexto): void
{
    $current = $error;
    while ($current !== null) {
        $mensaje = preg_replace('~(?:mysql|mariadb)://[^\s]+~i', '[redacted database URL]', $current->getMessage());
        foreach (['DB_PASS', 'MYSQLPASSWORD', 'MYSQL_URL', 'DATABASE_URL'] as $nombre) {
            $secreto = getenv($nombre);
            if ($secreto !== false && $secreto !== '') {
                $mensaje = str_replace($secreto, '[redacted]', $mensaje);
            }
        }

        error_log(sprintf(
            '[MindCare] %s: %s (%s) en %s:%d',
            $contexto,
            $mensaje,
            get_class($current),
            basename($current->getFile()),
            $current->getLine()
        ));
        $current = $current->getPrevious();
    }
}

set_exception_handler(static function (Throwable $error) use ($debugMode): void {
    registrarError($error, 'Error no controlado');

    if (headers_sent()) {
        return;
    }

    http_response_code(500);
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $esJson = $script === 'guardar_encuesta.php' || stripos($accept, 'application/json') !== false;

    if ($esJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'exito' => false,
            'mensaje' => $debugMode ? $error->getMessage() : 'Error interno del servidor. Intenta de nuevo.',
        ]);
        return;
    }

    header('Content-Type: text/html; charset=UTF-8');
    if ($debugMode) {
        echo '<pre>' . htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') . '</pre>';
        return;
    }

    echo 'Error interno del servidor. Intenta de nuevo más tarde.';
});

function obtenerVariableEntorno(array $nombres, bool $permitirVacia = false): ?string
{
    $vacia = null;
    foreach ($nombres as $nombre) {
        $valor = getenv($nombre);
        if ($valor === false && array_key_exists($nombre, $_ENV)) {
            $valor = (string) $_ENV[$nombre];
        }
        if ($valor === false && array_key_exists($nombre, $_SERVER)) {
            $valor = (string) $_SERVER[$nombre];
        }
        if ($valor === false) {
            continue;
        }
        if ($valor !== '') {
            return $valor;
        }
        $vacia = '';
    }

    return $permitirVacia ? $vacia : null;
}

function conectarDB(): PDO
{
    global $appEnv;
    global $debugMode;

    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $url = obtenerVariableEntorno(['MYSQL_URL', 'DATABASE_URL']);
    if ($url !== null) {
        $partes = parse_url($url);
        if (
            $partes === false
            || strtolower($partes['scheme'] ?? '') !== 'mysql'
            || empty($partes['host'])
            || empty($partes['user'])
            || !isset($partes['pass'])
            || empty($partes['path'])
        ) {
            throw new RuntimeException('La URL de conexión MySQL configurada no es válida.');
        }

        $host = $partes['host'];
        $port = (string) ($partes['port'] ?? '3306');
        $name = rawurldecode(ltrim($partes['path'], '/'));
        $user = rawurldecode($partes['user']);
        $pass = rawurldecode($partes['pass']);
        if ($pass === '' && !in_array($appEnv, ['local', 'development'], true)) {
            throw new RuntimeException('La URL de conexión MySQL no contiene una contraseña configurada.');
        }
    } else {
        $host = obtenerVariableEntorno(['DB_HOST', 'MYSQLHOST']);
        $port = obtenerVariableEntorno(['DB_PORT', 'MYSQLPORT']);
        $name = obtenerVariableEntorno(['DB_NAME', 'MYSQLDATABASE']);
        $user = obtenerVariableEntorno(['DB_USER', 'MYSQLUSER']);
        $pass = obtenerVariableEntorno(['DB_PASS', 'MYSQLPASSWORD'], $debugMode);

        $faltantes = [];
        foreach ([
            'DB_HOST o MYSQLHOST' => $host,
            'DB_PORT o MYSQLPORT' => $port,
            'DB_NAME o MYSQLDATABASE' => $name,
            'DB_USER o MYSQLUSER' => $user,
            'DB_PASS o MYSQLPASSWORD' => $pass,
        ] as $variable => $valor) {
            if ($valor === null) {
                $faltantes[] = $variable;
            }
        }
        if ($faltantes) {
            throw new RuntimeException('Faltan variables de conexión de base de datos: ' . implode(', ', $faltantes) . '.');
        }
    }

    if ($port === null || !ctype_digit($port) || (int) $port < 1 || (int) $port > 65535 || $name === '' || $user === '') {
        throw new RuntimeException('La configuración de conexión MySQL contiene valores no válidos.');
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $error) {
        throw new RuntimeException('No fue posible conectar con MySQL; verifica la configuración y disponibilidad del servicio.', 0, $error);
    }

    return $pdo;
}
