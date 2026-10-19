<?php
/**
 * MINDCARE+ · encuesta_inicial.php
 * Controlador de la encuesta inicial.
 * Verifica en la BD si el usuario ya completó la encuesta:
 * - Si ya la completó -> Redirige directamente al panel principal (dashboard.php).
 * - Si no la ha completado -> Muestra la encuesta inicial.
 */

declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

if (!empty($_SESSION['es_admin'])) {
    header('Location: panel_admin.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare('SELECT id_encuesta FROM encuesta_inicial WHERE id_usuario = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['usuario_id']]);
    $yaCompletada = (bool)$stmt->fetch();

    if ($yaCompletada) {
        $_SESSION['encuesta_completada'] = true;
        header('Location: dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('[MindCare+ Encuesta Inicial] Error comprobando encuesta: ' . $e->getMessage());
}

// Si no la ha completado, mostrar la interfaz de la encuesta
require_once __DIR__ . '/encuesta_inicial.html';
