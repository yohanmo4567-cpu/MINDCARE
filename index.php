<?php
/**
 * MINDCARE+ · index.php
 * Página de inicio / Landing page principal.
 * 
 * Control de sesión y encuesta:
 * - Si el usuario tiene sesión activa:
 *     - Si es admin -> redirige a panel_admin.php
 *     - Si es usuario -> verifica en la BD si el 'id_usuario' ya registró una encuesta:
 *         * Si ya la completó -> redirige directamente a dashboard.php.
 *         * Si no la ha completado -> redirige a la encuesta (encuesta_inicial.php).
 * - Si no tiene sesión activa:
 *     - Muestra la página de inicio (index.html).
 */

declare(strict_types=1);
session_start();

if (isset($_SESSION['usuario_id'])) {
    if (!empty($_SESSION['es_admin'])) {
        header('Location: panel_admin.php');
        exit;
    }

    require_once __DIR__ . '/conexion.php';

    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare('SELECT id_encuesta FROM encuesta_inicial WHERE id_usuario = ? LIMIT 1');
        $stmt->execute([(int)$_SESSION['usuario_id']]);
        $encuestaHecha = (bool)$stmt->fetch();
        $_SESSION['encuesta_completada'] = $encuestaHecha;

        if ($encuestaHecha) {
            header('Location: dashboard.php');
        } else {
            header('Location: encuesta_inicial.php');
        }
        exit;
    } catch (PDOException $e) {
        error_log('[MindCare+ Inicio] Error verificando encuesta: ' . $e->getMessage());
        header('Location: dashboard.php');
        exit;
    }
}

// Si no hay sesión iniciada, mostrar la página de inicio pública
require_once __DIR__ . '/index.html';
