<?php
/**
 * MINDCARE+ · conexion.php
 * ─────────────────────────────────────────────────────────────
 * Módulo centralizado de conexión a la base de datos MySQL (PDO).
 * Compatible con producción en Railway y desarrollo local.
 * ─────────────────────────────────────────────────────────────
 */

declare(strict_types=1);

if (!function_exists('mindcareObtenerEnv')) {
    /**
     * Obtiene una variable de entorno priorizando $_ENV, $_SERVER y getenv().
     */
    function mindcareObtenerEnv(string $clave): ?string
    {
        if (isset($_ENV[$clave]) && $_ENV[$clave] !== '') {
            return (string)$_ENV[$clave];
        }
        if (isset($_SERVER[$clave]) && $_SERVER[$clave] !== '') {
            return (string)$_SERVER[$clave];
        }
        $val = getenv($clave);
        if ($val !== false && $val !== '') {
            return (string)$val;
        }
        return null;
    }
}

if (!function_exists('mindcareCargarEnv')) {
    /**
     * Carga variables de entorno desde un archivo .env si existe localmente.
     */
    function mindcareCargarEnv(): void
    {
        $rutas = [
            __DIR__ . '/.env',
            __DIR__ . '/../.env',
            dirname(__DIR__) . '/.env'
        ];

        foreach ($rutas as $ruta) {
            if (is_file($ruta) && is_readable($ruta)) {
                $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lineas === false) {
                    continue;
                }

                foreach ($lineas as $linea) {
                    $linea = trim($linea);
                    if ($linea === '' || str_starts_with($linea, '#')) {
                        continue;
                    }

                    if (str_contains($linea, '=')) {
                        [$clave, $valor] = explode('=', $linea, 2);
                        $clave = trim($clave);
                        $valor = trim($valor);

                        if (preg_match('/^"(.*)"$/', $valor, $coincidencias) || preg_match("/^'(.*)'$/", $valor, $coincidencias)) {
                            $valor = $coincidencias[1];
                        }

                        if (!array_key_exists($clave, $_SERVER) && !array_key_exists($clave, $_ENV)) {
                            putenv("{$clave}={$valor}");
                            $_ENV[$clave] = $valor;
                            $_SERVER[$clave] = $valor;
                        }
                    }
                }
                break;
            }
        }
    }
}

if (!function_exists('conectarDB')) {
    /**
     * Crea y retorna una instancia PDO configurada mediante variables de entorno de Railway
     * o con valores por defecto del servidor MySQL remoto en Railway.
     *
     * Prioridad de variables:
     * 1. Variables Railway: MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
     * 2. URL Railway: MYSQL_URL, DATABASE_URL
     * 3. Variables alternativas: DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME
     * 4. Fallback remoto Railway: mainline.proxy.rlwy.net:54550 / railway / root
     *
     * @return PDO
     * @throws PDOException
     */
    function conectarDB(): PDO
    {
        mindcareCargarEnv();

        // 1. Soporte para URL completa de Railway
        $databaseUrl = mindcareObtenerEnv('MYSQL_URL') ?? mindcareObtenerEnv('DATABASE_URL');
        $urlParts = !empty($databaseUrl) ? parse_url($databaseUrl) : [];

        // 2. Priorizar variables provistas por Railway (MYSQL*) antes de las genéricas (DB_*)
        $host = $urlParts['host']
            ?? mindcareObtenerEnv('MYSQLHOST')
            ?? mindcareObtenerEnv('DB_HOST')
            ?? 'mainline.proxy.rlwy.net';

        $port = isset($urlParts['port']) ? (string)$urlParts['port']
            : (mindcareObtenerEnv('MYSQLPORT')
            ?? mindcareObtenerEnv('DB_PORT')
            ?? '54550');

        $name = !empty($urlParts['path']) ? ltrim($urlParts['path'], '/')
            : (mindcareObtenerEnv('MYSQLDATABASE')
            ?? mindcareObtenerEnv('DB_NAME')
            ?? 'railway');

        $user = $urlParts['user']
            ?? mindcareObtenerEnv('MYSQLUSER')
            ?? mindcareObtenerEnv('DB_USER')
            ?? 'root';

        $pass = $urlParts['pass']
            ?? mindcareObtenerEnv('MYSQLPASSWORD')
            ?? mindcareObtenerEnv('DB_PASS')
            ?? mindcareObtenerEnv('DB_PASSWORD')
            ?? 'bUfENyGgOPkqnpCOUcQfJdLuLHJfgMJu';

        // 3. Prevenir fallos si el entorno intenta conectar a localhost o 127.0.0.1 en Railway
        if ($host === '127.0.0.1' || $host === 'localhost' || empty($host)) {
            $host = 'mainline.proxy.rlwy.net';
            if ($port === '3306' || empty($port)) {
                $port = '54550';
            }
        }

        if ($name === 'mindcare' || empty($name)) {
            $name = 'railway';
        }

        if (empty($user)) {
            $user = 'root';
        }

        if (empty($port)) {
            $port = '54550';
        }

        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 7,
        ];

        try {
            return new PDO($dsn, $user, $pass, $opciones);
        } catch (PDOException $e) {
            $codigoError = $e->getCode();
            $mensajeOriginal = $e->getMessage();

            $diagnostico = 'No se pudo conectar a la base de datos MySQL en Railway.';
            if (str_contains($mensajeOriginal, '1045') || $codigoError == 1045) {
                $diagnostico = 'Acceso denegado (credenciales incorrectas). Verifica MYSQLPASSWORD.';
            } elseif (str_contains($mensajeOriginal, '2002') || $codigoError == 2002) {
                $diagnostico = "No se pudo alcanzar el host '{$host}:{$port}' (Conexión rechazada o timeout). Verifica que el proxy de Railway esté activo.";
            } elseif (str_contains($mensajeOriginal, '1049') || $codigoError == 1049) {
                $diagnostico = "La base de datos '{$name}' no existe en el servidor. Debe usarse el esquema 'railway'.";
            }

            error_log(sprintf(
                '[MindCare+ DB Error] %s | Causa: %s | Host: %s:%s | BD: %s | Usuario: %s',
                $diagnostico,
                $mensajeOriginal,
                $host,
                $port,
                $name,
                $user
            ));

            throw new PDOException(
                "Error de conexión MySQL [Host: {$host}:{$port}, BD: {$name}, Usuario: {$user}]: {$mensajeOriginal} ({$diagnostico})",
                (int)$codigoError,
                $e
            );
        }
    }
}
