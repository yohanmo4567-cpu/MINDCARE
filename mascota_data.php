<?php
/**
 * ──────────────────────────────────────────────────────────────────
 * MINDCARE+ · mascota_data.php
 * ──────────────────────────────────────────────────────────────────
 * Capa de DATOS y LÓGICA del módulo "Mi Mascota". No genera HTML.
 * Se incluye desde mascota.php (mismo patrón que lineas_ayuda_data.php).
 *
 * La mascota se alimenta con lo que el usuario YA hace en el resto
 * de la plataforma (hábitos completados, actividades realizadas,
 * emociones registradas). No hay una tabla separada de "puntos
 * manuales": todo se deriva de datos reales, y `mascota_evento`
 * evita que un mismo hábito/actividad/emoción se cuente dos veces.
 * ──────────────────────────────────────────────────────────────────
 */

// ── PUNTOS POR TIPO DE ACCIÓN ─────────────────────────────────────
const PUNTOS_HABITO    = 8;
const PUNTOS_ACTIVIDAD = 12;
const PUNTOS_EMOCION   = 5;

// ── PROGRESIÓN Y DESGASTE ─────────────────────────────────────────
const XP_POR_NIVEL         = 100; // XP necesaria para subir de nivel
const DECAY_HAMBRE_DIA     = 10;  // cuánto baja el hambre por día sin visitas
const DECAY_FELICIDAD_DIA  = 6;


/**
 * Devuelve la mascota del usuario. La crea automáticamente en su
 * primera visita (siempre nace como "Mindy", nivel 1).
 */
function obtenerMascota(PDO $pdo, int $uid): array {
    $stmt = $pdo->prepare("SELECT * FROM mascota WHERE id_usuario = ?");
    $stmt->execute([$uid]);
    $mascota = $stmt->fetch();

    if (!$mascota) {
        $hoy = (new DateTime('today'))->format('Y-m-d');
        $ins = $pdo->prepare("
            INSERT INTO mascota (id_usuario, nombre, nivel, experiencia, hambre, felicidad, tema_color, fecha_creacion, fecha_ultima_visita)
            VALUES (?, 'Mindy', 1, 0, 70, 70, 'lavanda', NOW(), ?)
        ");
        $ins->execute([$uid, $hoy]);
        $stmt->execute([$uid]);
        $mascota = $stmt->fetch();
    }
    return $mascota;
}

/**
 * Aplica el desgaste natural de hambre/felicidad por los días que
 * hayan pasado sin que el usuario visite/alimente a su mascota.
 * Solo descuenta una vez por día (usa fecha_ultima_visita = DATE).
 *
 * IMPORTANTE: la fecha "de hoy" y la comparación se calculan
 * completamente con el reloj de PHP (nunca con CURDATE() de MySQL).
 * Si se mezclan los dos relojes y no están perfectamente
 * sincronizados (típico en XAMPP, donde PHP y MySQL pueden tener
 * zonas horarias configuradas por separado), la app puede "creer"
 * que pasó un día cuando en realidad no pasó, y descontar hambre/
 * felicidad en cada visita aunque sea el mismo día. Al usar un solo
 * reloj para leer y para guardar, ese desfase desaparece.
 */
function aplicarDesgaste(PDO $pdo, array $mascota): array {
    $hoy    = new DateTime('today');
    $ultima = $mascota['fecha_ultima_visita'] ? new DateTime($mascota['fecha_ultima_visita']) : $hoy;

    // Diferencia con signo (no absoluta): si la fecha guardada quedara
    // "adelantada" por algún desfase previo, nunca debe restar de más.
    $dias = (int) floor(($hoy->getTimestamp() - $ultima->getTimestamp()) / 86400);
    if ($dias < 0) $dias = 0;

    if ($dias > 0) {
        $mascota['hambre']    = max(0, (int)$mascota['hambre']    - ($dias * DECAY_HAMBRE_DIA));
        $mascota['felicidad'] = max(0, (int)$mascota['felicidad'] - ($dias * DECAY_FELICIDAD_DIA));

        $upd = $pdo->prepare("UPDATE mascota SET hambre = ?, felicidad = ?, fecha_ultima_visita = ? WHERE id_mascota = ?");
        $upd->execute([$mascota['hambre'], $mascota['felicidad'], $hoy->format('Y-m-d'), $mascota['id_mascota']]);
    } elseif ($mascota['fecha_ultima_visita'] !== $hoy->format('Y-m-d')) {
        // La fecha guardada no es "hoy" según PHP (por ejemplo, quedó
        // adelantada por un desfase de reloj anterior a este arreglo).
        // La corregimos sin tocar hambre/felicidad, para que a partir
        // de ahora todo quede sincronizado con el reloj de PHP.
        $pdo->prepare("UPDATE mascota SET fecha_ultima_visita = ? WHERE id_mascota = ?")
            ->execute([$hoy->format('Y-m-d'), $mascota['id_mascota']]);
    }
    return $mascota;
}

/**
 * Revisa hábitos completados, actividades realizadas y emociones
 * registradas por el usuario, y "alimenta" a la mascota con los que
 * todavía no se hayan convertido en puntos. Es seguro llamarla en
 * cada carga de página: gracias al UNIQUE de mascota_evento, un
 * mismo hábito/actividad/emoción nunca suma puntos dos veces.
 *
 * @return array{puntos_nuevos:int, eventos_nuevos:int, subio_nivel:bool}
 */
function sincronizarMascota(PDO $pdo, int $uid, array $mascota): array {
    // Candidatos: [tipo, referencia (clave natural), fecha, puntos]
    $candidatos = [];

    // Hábitos completados (clave: id_habito + fecha del seguimiento)
    $stmtH = $pdo->prepare("
        SELECT sh.id_habito, sh.fecha
        FROM seguimiento_habito sh
        JOIN habito h ON sh.id_habito = h.id_habito
        WHERE h.id_usuario = ?
    ");
    $stmtH->execute([$uid]);
    foreach ($stmtH->fetchAll() as $r) {
        $candidatos[] = ['habito', $r['id_habito'] . '_' . $r['fecha'], $r['fecha'], PUNTOS_HABITO];
    }

    // Actividades realizadas (clave: id_actividad + fecha de realización)
    $stmtA = $pdo->prepare("
        SELECT id_actividad, fecha_realizacion
        FROM participacion_actividad
        WHERE id_usuario = ?
    ");
    $stmtA->execute([$uid]);
    foreach ($stmtA->fetchAll() as $r) {
        $candidatos[] = ['actividad', $r['id_actividad'] . '_' . $r['fecha_realizacion'], $r['fecha_realizacion'], PUNTOS_ACTIVIDAD];
    }

    // Emociones registradas (clave: id_emocion, ya es único de por sí)
    $stmtE = $pdo->prepare("SELECT id_emocion, fecha_registro FROM emocion WHERE id_usuario = ?");
    $stmtE->execute([$uid]);
    foreach ($stmtE->fetchAll() as $r) {
        $candidatos[] = ['emocion', (string)$r['id_emocion'], substr($r['fecha_registro'], 0, 10), PUNTOS_EMOCION];
    }

    if (!$candidatos) {
        return ['puntos_nuevos' => 0, 'eventos_nuevos' => 0, 'subio_nivel' => false];
    }

    // Traer lo que YA fue registrado para descartarlo en PHP (evita N consultas)
    $yaRegistrados = [];
    $stmtYa = $pdo->prepare("SELECT tipo, referencia FROM mascota_evento WHERE id_usuario = ?");
    $stmtYa->execute([$uid]);
    foreach ($stmtYa->fetchAll() as $r) {
        $yaRegistrados[$r['tipo'] . '::' . $r['referencia']] = true;
    }

    $puntosNuevos   = 0;
    $eventosNuevos  = 0;
    $insertEvento   = $pdo->prepare("
        INSERT INTO mascota_evento (id_usuario, tipo, referencia, puntos, fecha_evento)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($candidatos as [$tipo, $referencia, $fecha, $puntos]) {
        $clave = $tipo . '::' . $referencia;
        if (isset($yaRegistrados[$clave])) continue;

        $insertEvento->execute([$uid, $tipo, $referencia, $puntos, $fecha]);
        $puntosNuevos  += $puntos;
        $eventosNuevos++;
    }

    $subioNivel = false;
    if ($puntosNuevos > 0) {
        $nivelAntes = calcularNivel((int)$mascota['experiencia'])['nivel'];

        $nuevaExperiencia = (int)$mascota['experiencia'] + $puntosNuevos;
        $nuevaHambre       = min(100, (int)$mascota['hambre']    + $puntosNuevos);
        $nuevaFelicidad    = min(100, (int)$mascota['felicidad'] + (int)round($puntosNuevos * 0.6));

        $upd = $pdo->prepare("
            UPDATE mascota
            SET experiencia = ?, hambre = ?, felicidad = ?, fecha_ultima_visita = ?
            WHERE id_mascota = ?
        ");
        $upd->execute([$nuevaExperiencia, $nuevaHambre, $nuevaFelicidad, (new DateTime('today'))->format('Y-m-d'), $mascota['id_mascota']]);

        $nivelDespues = calcularNivel($nuevaExperiencia)['nivel'];
        $subioNivel   = $nivelDespues > $nivelAntes;
    }

    return ['puntos_nuevos' => $puntosNuevos, 'eventos_nuevos' => $eventosNuevos, 'subio_nivel' => $subioNivel];
}

/**
 * Cambia el nombre de la mascota (única personalización disponible
 * por ahora; el resto — color/accesorios — queda reservado en el
 * esquema para cuando se active esa función completa).
 */
function renombrarMascota(PDO $pdo, int $idMascota, string $nombre): void {
    $nombre = trim($nombre);
    if ($nombre === '' ) $nombre = 'Mindy';
    $nombre = mb_substr($nombre, 0, 50);
    $pdo->prepare("UPDATE mascota SET nombre = ? WHERE id_mascota = ?")->execute([$nombre, $idMascota]);
}

/**
 * Traduce la experiencia acumulada en nivel actual + progreso hacia
 * el siguiente nivel (0-100%). Fórmula fija por ahora (fácil de
 * volver progresiva —p.ej. nivel * 100— más adelante).
 */
function calcularNivel(int $experiencia): array {
    $nivel        = intdiv($experiencia, XP_POR_NIVEL) + 1;
    $xpEnNivel    = $experiencia % XP_POR_NIVEL;
    $porcentaje   = (int) round(($xpEnNivel / XP_POR_NIVEL) * 100);
    return [
        'nivel'         => $nivel,
        'xp_en_nivel'   => $xpEnNivel,
        'xp_necesaria'  => XP_POR_NIVEL,
        'porcentaje'    => $porcentaje,
    ];
}

/**
 * Etapa visual del gato según su nivel. Pensado para que, cuando se
 * implemente la personalización, cada etapa desbloquee variantes
 * propias (accesorios, colores especiales, etc.).
 */
function obtenerEtapaGato(int $nivel): array {
    if ($nivel >= 10) return ['clave' => 'sabio',  'label' => 'Gato sabio',  'emoji' => '✨'];
    if ($nivel >= 6)  return ['clave' => 'adulto', 'label' => 'Gato adulto', 'emoji' => '🌟'];
    if ($nivel >= 3)  return ['clave' => 'joven',  'label' => 'Gato joven',  'emoji' => '🌱'];
    return ['clave' => 'bebe', 'label' => 'Gatito bebé', 'emoji' => '🍼'];
}

/**
 * Estado de ánimo actual, usado tanto para elegir la cara del gato
 * como para priorizar qué consejo mostrar.
 */
function obtenerEstadoAnimo(int $hambre, int $felicidad): string {
    if ($hambre <= 30)                       return 'hambriento';
    if ($felicidad <= 30)                     return 'triste';
    if ($hambre >= 70 && $felicidad >= 70)    return 'feliz';
    return 'contento';
}

/**
 * Consejo del día. Prioriza mensajes relacionados con el estado de
 * ánimo actual; si todo está bien, rota entre consejos generales de
 * bienestar (mismo consejo durante todo el día, para que no cambie
 * cada vez que se recarga la página).
 */
function obtenerConsejoDelDia(string $animo): string {
    $porAnimo = [
        'hambriento' => [
            'Tengo hambre… ¡registra un hábito o una actividad hoy para alimentarme! 🍽️',
            'Hace días que no como bien. ¿Qué tal si marcas un hábito como completado?',
            'Un pequeño hábito de hoy es una gran comida para mí. ¡Vamos!',
        ],
        'triste' => [
            'Me siento un poco decaído. Cuéntale a tus emociones cómo te sientes tú también 💜',
            'Extraño verte por aquí. Una actividad de bienestar nos vendría bien a los dos.',
            'Todo mejora poco a poco. ¿Probamos un ejercicio de respiración juntos?',
            'Si sientes que necesitas hablar con alguien, en "Líneas de Ayuda" tienes apoyo profesional a un clic 💜',
        ],
        'contento' => [
            'Vamos por buen camino. ¡Sigamos sumando hábitos saludables!',
            'Un paso a la vez. Hoy es un buen día para registrar cómo te sientes.',
            'Me gusta este ritmo. ¿Completamos un hábito más hoy?',
        ],
        'feliz' => [
            '¡Me siento genial gracias a ti! Sigue así 🎉',
            'Estamos en racha. Tu constancia también te está ayudando a ti.',
            '¡Excelente semana! Explora una actividad nueva para variar un poco.',
        ],
    ];

    // Consejos generales de bienestar físico y rutinas
    $generales = [
        'Dormir bien es uno de los hábitos más subestimados. ¿Cómo dormiste anoche?',
        'Tomar agua con regularidad ayuda a tu concentración y tu ánimo.',
        'Una pausa de 5 minutos para respirar puede cambiar el resto del día.',
        'Anotar cómo te sientes, aunque sea en una palabra, ya cuenta como autocuidado.',
        'Moverte un poco, aunque sea caminar, también es cuidar tu mente.',
        'Está bien tener días difíciles. Lo importante es no dejar de intentarlo.',
    ];

    // Consejos enfocados en salud mental (psicoeducación general y hábitos
    // de autocuidado emocional; ninguno reemplaza la orientación de un
    // profesional, por eso a veces señalan hacia "Líneas de Ayuda").
    $saludMental = [
        'Sentir ansiedad de vez en cuando es humano. Notarlo y nombrarlo ya es un primer paso.',
        'No tienes que resolverlo todo hoy. Dividir un problema grande en pasos pequeños alivia la mente.',
        'Hablar de cómo te sientes con alguien de confianza no es debilidad, es autocuidado.',
        'El estrés constante también se cuida con descanso, no solo con "hacer más".',
        'Compararte con los demás rara vez ayuda. Tu ritmo también es válido.',
        'Poner límites sanos —decir "no" cuando lo necesitas— también es salud mental.',
        'Si un pensamiento negativo se repite mucho, intenta escribirlo: a veces pierde fuerza al verlo en papel.',
        'La respiración lenta le dice a tu cuerpo que está a salvo. Pruébala en el módulo de Respiración 🌬️',
        'Pedir ayuda profesional cuando algo te sobrepasa es un acto de valentía, no un último recurso.',
        'Celebrar logros pequeños (como hoy, que viniste a verme) también construye bienestar emocional.',
    ];

    $indiceDia = (int) date('z'); // día del año, para que el consejo sea estable durante el día

    // Si el ánimo pide atención (triste/hambriento), casi siempre prioriza
    // ese mensaje; el resto del tiempo rota entre bienestar general y salud mental.
    $prioridadAnimo = in_array($animo, ['triste', 'hambriento'], true);
    if ($prioridadAnimo && $indiceDia % 3 !== 0) {
        $lista = $porAnimo[$animo];
    } elseif ($indiceDia % 2 === 0) {
        $lista = $saludMental;
    } else {
        $lista = !empty($porAnimo[$animo]) && $indiceDia % 5 === 0 ? $porAnimo[$animo] : $generales;
    }

    return $lista[$indiceDia % count($lista)];
}
