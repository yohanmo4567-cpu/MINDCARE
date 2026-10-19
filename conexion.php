<?php
/**
 * MINDCARE+ · conexion.php
 * ─────────────────────────────────────────────────────────────
 * Módulo centralizado de conexión a la base de datos MySQL (PDO).
 * Compatible con producción en Railway y desarrollo local.
 * ─────────────────────────────────────────────────────────────
 */

declare(strict_types=1);

if (!function_exists('conectarDB')) {
    /**
     * Crea y retorna una instancia PDO configurada dinámicamente mediante
     * variables de entorno de Railway o valores por defecto del proxy Railway.
     *
     * @return PDO
     * @throws PDOException
     */
    function conectarDB(): PDO
    {
        // Parámetros de conexión dinámicos para Railway
        $host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'mainline.proxy.rlwy.net');
        $port = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '54550');
        $name = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'railway');
        $user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
        $pass = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: (getenv('MYSQLPASSWORD') ?: '');
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $user, $pass, $opciones);
    }
}
