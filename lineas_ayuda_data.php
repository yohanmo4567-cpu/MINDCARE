<?php
/**
 * ──────────────────────────────────────────────────────────────────
 * MINDCARE+ · lineas_ayuda_data.php
 * ──────────────────────────────────────────────────────────────────
 * Capa de DATOS y LÓGICA del módulo "Líneas de Ayuda".
 * No genera HTML. Se incluye desde lineas_ayuda.php (separación de
 * responsabilidades: datos/lógica vs. vista, igual que el resto del
 * proyecto separa consultas de presentación).
 *
 * No requiere tablas nuevas: la información de las líneas es estática
 * y de bajo cambio, por eso vive en un array PHP versionado con el
 * código (fácil de mantener, sin riesgo de inyección, sin consultas).
 * ──────────────────────────────────────────────────────────────────
 */

// ── NIVELES DE RIESGO SOPORTADOS ─────────────────────────────────
const NIVEL_VERDE    = 'VERDE';
const NIVEL_AMARILLO = 'AMARILLO';
const NIVEL_ROJO     = 'ROJO';
const NIVELES_VALIDOS = [NIVEL_VERDE, NIVEL_AMARILLO, NIVEL_ROJO];


/**
 * Determina el nivel emocional/de riesgo actual del usuario.
 *
 * ── PUNTO DE INTEGRACIÓN FUTURO ─────────────────────────────────
 * Aquí es donde este módulo debe conectarse con el sistema de
 * análisis emocional de MindCare+ cuando exista. Por ejemplo:
 *
 *     function determinarNivelRiesgo(PDO $pdo, int $uid): string {
 *         $stmt = $pdo->prepare(
 *             "SELECT nivel_riesgo FROM analisis_emocional
 *              WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 1"
 *         );
 *         $stmt->execute([$uid]);
 *         return $stmt->fetchColumn() ?: NIVEL_VERDE;
 *     }
 *
 * Mientras ese sistema no exista, se permite forzar el nivel vía
 * querystring (?nivel=VERDE|AMARILLO|ROJO) SOLO con fines de
 * demostración/pruebas. Quitar ese bloque al integrar el análisis real.
 *
 * @param PDO $pdo Conexión activa (reservada para la integración futura)
 * @param int $uid ID del usuario en sesión (reservado para la integración futura)
 */
function determinarNivelRiesgo(PDO $pdo, int $uid): string {
    // --- INICIO bloque de demostración (retirar al integrar el análisis real) ---
    $nivelSolicitado = strtoupper(trim($_GET['nivel'] ?? ''));
    if (in_array($nivelSolicitado, NIVELES_VALIDOS, true)) {
        return $nivelSolicitado;
    }
    // --- FIN bloque de demostración ---

    return NIVEL_VERDE; // Valor por defecto seguro si no hay información
}


/**
 * Devuelve los contactos de emergencia registrados por el usuario.
 *
 * ── PUNTO DE INTEGRACIÓN FUTURO ─────────────────────────────────
 * Aún no existe una tabla de contactos de emergencia en el proyecto.
 * Cuando se cree (ej. tabla `contacto_emergencia` con id_usuario,
 * nombre, telefono, parentesco), reemplazar este cuerpo por:
 *
 *     $stmt = $pdo->prepare(
 *         "SELECT nombre, telefono, parentesco
 *          FROM contacto_emergencia WHERE id_usuario = ?"
 *     );
 *     $stmt->execute([$uid]);
 *     return $stmt->fetchAll();
 *
 * Por ahora devuelve un arreglo vacío; la vista maneja ese caso
 * mostrando un estado vacío con enlace a "Mi Perfil".
 *
 * @param PDO $pdo Conexión activa (reservada para la integración futura)
 * @param int $uid ID del usuario en sesión (reservado para la integración futura)
 */
function obtenerContactosEmergencia(PDO $pdo, int $uid): array {
    return [];
}


/**
 * Catálogo estático de líneas de ayuda psicológica y de emergencia.
 * Fuente: Secretaría Distrital de Salud de Bogotá, ICBF, Univ. Javeriana
 * y Fundación Santa Fe de Bogotá (verificar vigencia periódicamente).
 *
 * Campos por línea:
 *   id, nombre, numero_display, numero_tel, whatsapp (o null),
 *   cobertura, horario, poblacion, descripcion, tags (filtro),
 *   color (variable semántica de dashboard.css), emergencia (bool)
 */
function obtenerLineasAyuda(): array {
    return [
        [
            'id'             => 'linea_106',
            'nombre'         => 'Línea 106',
            'numero_display' => '106',
            'numero_tel'     => '106',
            'whatsapp'       => '573007548933',
            'cobertura'      => 'Bogotá (también Valle del Cauca, Antioquia y Boyacá)',
            'horario'        => 'Las 24 horas, los 365 días del año',
            'poblacion'      => 'Niños, niñas, adolescentes y adultos',
            'descripcion'    => 'Escucha y apoyo psicológico gratuito en momentos de crisis, ofrecido por la Secretaría Distrital de Salud.',
            'tags'           => ['general', 'ninos', 'adolescentes', 'adultos'],
            'color'          => 'primary',
            'emergencia'     => false,
        ],
        [
            'id'             => 'linea_123',
            'nombre'         => 'Línea 123',
            'numero_display' => '123',
            'numero_tel'     => '123',
            'whatsapp'       => null,
            'cobertura'      => 'Nacional / Distrital',
            'horario'        => 'Las 24 horas, los 365 días del año',
            'poblacion'      => 'Toda la ciudadanía',
            'descripcion'    => 'Línea única de emergencias (Policía, Bomberos, Gestión del Riesgo, Salud). Úsala si hay riesgo inminente para la vida.',
            'tags'           => ['general', 'emergencia'],
            'color'          => 'red',
            'emergencia'     => true,
        ],
        [
            'id'             => 'porque_quiero_estar_bien',
            'nombre'         => 'Porque Quiero Estar Bien',
            'numero_display' => '333 033 3588',
            'numero_tel'     => '3330333588',
            'whatsapp'       => '573330333588',
            'cobertura'      => 'Nacional (Colombia)',
            'horario'        => 'Verificar horario vigente en porquequieroestarbien.com',
            'poblacion'      => 'Mayores de 18 años',
            'descripcion'    => 'Primeros auxilios psicológicos y teleorientación, impulsado por la Fundación Santa Fe de Bogotá.',
            'tags'           => ['adultos', 'general'],
            'color'          => 'blue',
            'emergencia'     => false,
        ],
        [
            'id'             => 'linea_purpura',
            'nombre'         => 'Línea Púrpura',
            'numero_display' => '01 8000 112 137',
            'numero_tel'     => '018000112137',
            'whatsapp'       => '573007551846',
            'cobertura'      => 'Bogotá',
            'horario'        => 'Las 24 horas, los 365 días del año',
            'poblacion'      => 'Mujeres mayores de 18 años',
            'descripcion'    => 'Orientación gratuita ante situaciones de violencia de género, atendida por psicólogas, trabajadoras sociales y abogadas.',
            'tags'           => ['mujeres', 'violencia_genero'],
            'color'          => 'rose',
            'emergencia'     => false,
        ],
        [
            'id'             => 'linea_calma',
            'nombre'         => 'Línea Calma',
            'numero_display' => '01 8000 423 614',
            'numero_tel'     => '018000423614',
            'whatsapp'       => null,
            'cobertura'      => 'Bogotá',
            'horario'        => 'Horario extendido, verificar vigencia',
            'poblacion'      => 'Hombres mayores de 18 años',
            'descripcion'    => 'Espacio de escucha y orientación para manejar emociones difíciles: ira, celos, ansiedad, soledad, entre otras.',
            'tags'           => ['hombres', 'general'],
            'color'          => 'blue',
            'emergencia'     => false,
        ],
        [
            'id'             => 'linea_salvavidas',
            'nombre'         => 'Línea Salvavidas',
            'numero_display' => '311 766 8666',
            'numero_tel'     => '3117668666',
            'whatsapp'       => '573117668666',
            'cobertura'      => 'Bogotá',
            'horario'        => 'Verificar horario de atención vigente',
            'poblacion'      => 'Personas mayores de 14 años',
            'descripcion'    => 'Orientación y escucha de la Pontificia Universidad Javeriana ante crisis emocional o riesgo suicida.',
            'tags'           => ['adolescentes', 'adultos', 'general'],
            'color'          => 'purple',
            'emergencia'     => true,
        ],
        [
            'id'             => 'linea_psicoactiva',
            'nombre'         => 'Línea Psicoactiva',
            'numero_display' => '01 8000 112 439',
            'numero_tel'     => '018000112439',
            'whatsapp'       => '573012761197',
            'cobertura'      => 'Bogotá',
            'horario'        => 'Todos los días, 8:00 a.m. a 8:00 p.m.',
            'poblacion'      => 'Personas con consumo de sustancias psicoactivas y sus familias',
            'descripcion'    => 'Información, orientación e intervención breve para la prevención y atención del consumo de sustancias psicoactivas.',
            'tags'           => ['consumo_spa', 'general'],
            'color'          => 'accent',
            'emergencia'     => false,
        ],
        [
            'id'             => 'linea_141_icbf',
            'nombre'         => 'Línea 141 del ICBF',
            'numero_display' => '141',
            'numero_tel'     => '141',
            'whatsapp'       => null,
            'cobertura'      => 'Nacional',
            'horario'        => 'Las 24 horas, los 7 días de la semana',
            'poblacion'      => 'Niños, niñas y adolescentes',
            'descripcion'    => 'Línea del ICBF para reportar maltrato infantil, violencia sexual, acoso escolar o cualquier amenaza contra un menor.',
            'tags'           => ['ninos', 'adolescentes', 'maltrato_infantil'],
            'color'          => 'green',
            'emergencia'     => true,
        ],
    ];
}


/**
 * Filtra el catálogo de líneas por una etiqueta de población objetivo.
 * Reutilizable desde la vista; evita duplicar lógica de filtrado.
 *
 * @param array  $lineas Catálogo completo (de obtenerLineasAyuda())
 * @param string $tag    Etiqueta a filtrar ('' = sin filtro)
 */
function filtrarLineasPorPoblacion(array $lineas, string $tag): array {
    $tag = trim($tag);
    if ($tag === '') {
        return $lineas;
    }
    return array_values(array_filter(
        $lineas,
        fn(array $linea): bool => in_array($tag, $linea['tags'], true)
    ));
}


/**
 * Consejos de autocuidado mostrados cuando el nivel es VERDE.
 * Enlazan a módulos ya existentes del dashboard (respiración, hábitos,
 * emociones) para reutilizar funcionalidad en vez de duplicarla.
 */
function obtenerConsejosAutocuidado(): array {
    return [
        [
            'emoji' => '🌬️', 'titulo' => 'Respira con calma',
            'texto' => 'Unos minutos de respiración guiada ayudan a regular el sistema nervioso.',
            'href'  => 'respiracion.php', 'accion' => 'Practicar respiración',
        ],
        [
            'emoji' => '📝', 'titulo' => 'Registra cómo te sientes',
            'texto' => 'Anotar tus emociones hoy te ayuda a identificar patrones a tiempo.',
            'href'  => 'emociones.php', 'accion' => 'Ir a Control Emocional',
        ],
        [
            'emoji' => '🌱', 'titulo' => 'Cuida tus hábitos',
            'texto' => 'Dormir bien, moverte y comer a tus horas sostienen tu bienestar.',
            'href'  => 'habitos.php', 'accion' => 'Ver mis hábitos',
        ],
        [
            'emoji' => '❤️', 'titulo' => 'Conecta con alguien',
            'texto' => 'Habla con una persona de confianza; el acompañamiento importa.',
            'href'  => null, 'accion' => null,
        ],
    ];
}


/**
 * Construye un enlace de WhatsApp válido (wa.me) a partir de un número
 * con indicativo de país y un mensaje inicial opcional, ya codificado.
 */
function generarLinkWhatsApp(string $numeroConIndicativo, string $mensaje = ''): string {
    $url = 'https://wa.me/' . preg_replace('/\D/', '', $numeroConIndicativo);
    if ($mensaje !== '') {
        $url .= '?text=' . rawurlencode($mensaje);
    }
    return $url;
}
