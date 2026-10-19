<?php
/**
 * MINDCARE+ · guardar_encuesta.php
 * Recibe las respuestas del test inicial y las guarda en encuesta_inicial.
 * Responde JSON. Solo acepta POST con sesión activa.
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
session_start();

function responder(bool $exito, string $mensaje): never {
    echo json_encode(['exito' => $exito, 'mensaje' => $mensaje]);
    exit;
}

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    responder(false, 'Sesión no válida. Inicia sesión de nuevo.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}

// Recoger respuestas
$uid             = (int) $_SESSION['usuario_id'];
$razon_uso       = trim($_POST['razon_uso']        ?? '');
$estado_emocional= trim($_POST['estado_emocional'] ?? '');
$expectativa     = trim($_POST['expectativa']       ?? '');
$frecuencia_uso  = trim($_POST['frecuencia_uso']    ?? '');
$area_mejora     = trim($_POST['area_mejora']       ?? '');

// Validar que llegaron todas las respuestas
if (!$razon_uso || !$estado_emocional || !$expectativa || !$frecuencia_uso || !$area_mejora) {
    responder(false, 'Faltan respuestas. Completa todas las preguntas.');
}

// Conexión centralizada a la base de datos
require_once __DIR__ . '/conexion.php';

try {
    $pdo = conectarDB();

    // Verificar si ya respondió (no debería llegar aquí, pero por seguridad)
    $check = $pdo->prepare('SELECT id_encuesta FROM encuesta_inicial WHERE id_usuario = ? LIMIT 1');
    $check->execute([$uid]);
    if ($check->fetch()) {
        // Ya respondió → marcar sesión y dejar pasar
        $_SESSION['encuesta_completada'] = true;
        responder(true, 'Ya completaste la encuesta anteriormente.');
    }

    // Insertar respuestas
    $stmt = $pdo->prepare(
        'INSERT INTO encuesta_inicial
            (id_usuario, razon_uso, estado_emocional, expectativa, frecuencia_uso, area_mejora)
         VALUES
            (:uid, :razon, :estado, :expectativa, :frecuencia, :area)'
    );
    $stmt->execute([
        ':uid'         => $uid,
        ':razon'       => $razon_uso,
        ':estado'      => $estado_emocional,
        ':expectativa' => $expectativa,
        ':frecuencia'  => $frecuencia_uso,
        ':area'        => $area_mejora,
    ]);

    // Marcar en sesión que ya completó la encuesta
    $_SESSION['encuesta_completada'] = true;

    responder(true, '¡Encuesta guardada correctamente!');

} catch (PDOException $e) {
    error_log('[MindCare+ Encuesta] Error BD: ' . $e->getMessage());
    responder(false, 'Error interno del servidor. Intenta de nuevo.');
}
