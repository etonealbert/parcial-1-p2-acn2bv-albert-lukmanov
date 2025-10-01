<?php
/**
 * Mini Travel Guide - Parcial 1 P2
 * 
 * @author Albert Lukmanov
 * @email albert.lukmanov@davinci.edu.ar
 * @github https://github.com/etonealbert
 * 
 * Sistema de guía de viajes con búsqueda, filtrado, temas y sugerencias con IA/fallback
 */

// Cargar variables de entorno desde .env
function cargarEnv($archivo = '.env') {
    if (!file_exists($archivo)) {
        return false;
    }
    
    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        // Ignorar comentarios
        if (strpos(trim($linea), '#') === 0) {
            continue;
        }
        
        // Parsear línea KEY=VALUE
        if (strpos($linea, '=') !== false) {
            list($nombre, $valor) = explode('=', $linea, 2);
            $nombre = trim($nombre);
            $valor = trim($valor);
            
            if (!empty($nombre)) {
                $_ENV[$nombre] = $valor;
                putenv("$nombre=$valor");
            }
        }
    }
    return true;
}

// Cargar configuración
cargarEnv();

// Array asociativo con 8+ destinos turísticos
$destinos = [
    [
        'titulo' => 'Rio de Janeiro',
        'pais' => 'Brasil',
        'continente' => 'Sudamérica',
        'tipo' => 'playa',
        'descripcion' => 'Ciudad vibrante famosa por sus playas de Copacabana e Ipanema, el Cristo Redentor y su animado carnaval.',
        'imagen' => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=500'
    ],
    [
        'titulo' => 'Cusco',
        'pais' => 'Perú',
        'continente' => 'Sudamérica',
        'tipo' => 'aventura',
        'descripcion' => 'Antigua capital del Imperio Inca, punto de partida hacia Machu Picchu. Rica en historia y cultura andina.',
        'imagen' => 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=500'
    ],
    [
        'titulo' => 'Patagonia',
        'pais' => 'Argentina/Chile',
        'continente' => 'Sudamérica',
        'tipo' => 'montaña',
        'descripcion' => 'Región de paisajes épicos: glaciares, montañas escarpadas y lagos cristalinos. Ideal para trekking y aventura.',
        'imagen' => 'https://images.unsplash.com/photo-1589802829985-817e51171b92?w=500'
    ],
    [
        'titulo' => 'Cartagena',
        'pais' => 'Colombia',
        'continente' => 'Sudamérica',
        'tipo' => 'urbano',
        'descripcion' => 'Ciudad colonial amurallada con arquitectura colorida, playas caribeñas y rica gastronomía.',
        'imagen' => 'https://images.unsplash.com/photo-1568632234157-ce7aecd03d0d?w=500'
    ],
    [
        'titulo' => 'Kioto',
        'pais' => 'Japón',
        'continente' => 'Asia',
        'tipo' => 'cultural',
        'descripcion' => 'Antigua capital imperial con templos milenarios, jardines zen y tradiciones preservadas.',
        'imagen' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=500'
    ],
    [
        'titulo' => 'Bali',
        'pais' => 'Indonesia',
        'continente' => 'Asia',
        'tipo' => 'playa',
        'descripcion' => 'Isla paradisíaca con playas de ensueño, arrozales en terrazas, templos hindúes y surf de clase mundial.',
        'imagen' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=500'
    ],
    [
        'titulo' => 'Zermatt',
        'pais' => 'Suiza',
        'continente' => 'Europa',
        'tipo' => 'montaña',
        'descripcion' => 'Pueblo alpino sin autos al pie del icónico Matterhorn. Esquí de primer nivel y senderismo espectacular.',
        'imagen' => 'https://images.unsplash.com/photo-1531366936337-7c912a4589a7?w=500'
    ],
    [
        'titulo' => 'Barcelona',
        'pais' => 'España',
        'continente' => 'Europa',
        'tipo' => 'urbano',
        'descripcion' => 'Ciudad cosmopolita con arquitectura de Gaudí, playas mediterráneas, tapas y vida nocturna vibrante.',
        'imagen' => 'https://images.unsplash.com/photo-1583422409516-2895a77efded?w=500'
    ],
    [
        'titulo' => 'Marrakech',
        'pais' => 'Marruecos',
        'continente' => 'África',
        'tipo' => 'cultural',
        'descripcion' => 'Ciudad imperial con zocos laberínticos, palacios ornamentados y la famosa plaza Jemaa el-Fna.',
        'imagen' => 'https://images.unsplash.com/photo-1489749798305-4fea3ae63d43?w=500'
    ],
    [
        'titulo' => 'Serengeti',
        'pais' => 'Tanzania',
        'continente' => 'África',
        'tipo' => 'aventura',
        'descripcion' => 'Parque nacional mundialmente famoso por la gran migración de ñus y safaris inolvidables.',
        'imagen' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=500'
    ]
];

// Obtener parámetros GET
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtroContinente = isset($_GET['continente']) ? $_GET['continente'] : '';
$filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$tema = isset($_GET['tema']) ? $_GET['tema'] : 'claro';

// Variables para el formulario POST
$sugerenciaEnviada = false;
$erroresValidacion = [];
$datosSugerencia = [];

// Procesar formulario POST de sugerencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'sugerir') {
    // Validación backend
    $campos = ['titulo', 'pais', 'continente', 'tipo', 'descripcion', 'imagen'];
    
    foreach ($campos as $campo) {
        if (empty($_POST[$campo])) {
            $erroresValidacion[$campo] = "El campo es obligatorio";
        } else {
            $datosSugerencia[$campo] = htmlspecialchars($_POST[$campo]);
        }
    }
    
    // Si no hay errores, marcar como enviada
    if (empty($erroresValidacion)) {
        $sugerenciaEnviada = true;
    }
}

// Función para filtrar destinos según criterios
function filtrarDestinos($destinos, $busqueda, $continente, $tipo) {
    return array_filter($destinos, function($destino) use ($busqueda, $continente, $tipo) {
        $coincide = true;
        
        // Filtrar por búsqueda de texto
        if ($busqueda !== '') {
            $coincide = $coincide && (
                stripos($destino['titulo'], $busqueda) !== false ||
                stripos($destino['pais'], $busqueda) !== false ||
                stripos($destino['descripcion'], $busqueda) !== false
            );
        }
        
        // Filtrar por continente
        if ($continente !== '') {
            $coincide = $coincide && ($destino['continente'] === $continente);
        }
        
        // Filtrar por tipo
        if ($tipo !== '') {
            $coincide = $coincide && ($destino['tipo'] === $tipo);
        }
        
        return $coincide;
    });
}

// Función para consultar OpenAI
function consultarOpenAI($pregunta, $destinos) {
    $apiKey = getenv('OPENAI_API_KEY');
    $modelo = getenv('OPENAI_MODEL') ?: 'gpt-4o-mini';
    
    if (empty($apiKey)) {
        return null;
    }
    
    // Crear contexto con los destinos disponibles
    $listaDestinos = array_map(function($d) {
        return "- {$d['titulo']} ({$d['pais']}, {$d['continente']}) - Tipo: {$d['tipo']}";
    }, $destinos);
    
    $contexto = "Eres un asistente de viajes. Estos son los destinos disponibles:\n\n" . 
                implode("\n", $listaDestinos) . 
                "\n\nBasa tu respuesta SOLO en estos destinos. Responde en formato JSON con un array 'destinos' que contenga los títulos de los destinos recomendados.";
    
    $payload = [
        'model' => $modelo,
        'messages' => [
            ['role' => 'system', 'content' => $contexto],
            ['role' => 'user', 'content' => $pregunta]
        ],
        'temperature' => 0.7,
        'max_tokens' => 500
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$respuesta) {
        return null;
    }
    
    $datos = json_decode($respuesta, true);
    if (!isset($datos['choices'][0]['message']['content'])) {
        return null;
    }
    
    $contenido = $datos['choices'][0]['message']['content'];
    
    // Intentar extraer títulos de destinos de la respuesta
    $titulosRecomendados = [];
    foreach ($destinos as $destino) {
        if (stripos($contenido, $destino['titulo']) !== false) {
            $titulosRecomendados[] = $destino['titulo'];
        }
    }
    
    return $titulosRecomendados;
}

// Función para sugerir destinos con IA o fallback
function sugerirConIA($pregunta, $destinos) {
    $usandoIA = false;
    $titulosIA = [];
    
    // Intentar usar OpenAI si está configurado
    if (getenv('OPENAI_API_KEY')) {
        $titulosIA = consultarOpenAI($pregunta, $destinos);
        if ($titulosIA !== null && !empty($titulosIA)) {
            $usandoIA = true;
            // Filtrar destinos según recomendación de IA
            $sugerencias = array_filter($destinos, function($destino) use ($titulosIA) {
                return in_array($destino['titulo'], $titulosIA);
            });
            
            if (!empty($sugerencias)) {
                return [
                    'usandoIA' => $usandoIA,
                    'destinos' => array_values($sugerencias)
                ];
            }
        }
    }
    
    // FALLBACK: Análisis simple de la pregunta
    $preguntaLower = strtolower($pregunta);
    $continenteBuscado = '';
    $tipoBuscado = '';
    
    // Detectar continente en la pregunta
    if (strpos($preguntaLower, 'sudamérica') !== false || strpos($preguntaLower, 'sudamerica') !== false) {
        $continenteBuscado = 'Sudamérica';
    } elseif (strpos($preguntaLower, 'europa') !== false) {
        $continenteBuscado = 'Europa';
    } elseif (strpos($preguntaLower, 'asia') !== false) {
        $continenteBuscado = 'Asia';
    } elseif (strpos($preguntaLower, 'áfrica') !== false || strpos($preguntaLower, 'africa') !== false) {
        $continenteBuscado = 'África';
    }
    
    // Detectar tipo en la pregunta
    if (strpos($preguntaLower, 'aventura') !== false) {
        $tipoBuscado = 'aventura';
    } elseif (strpos($preguntaLower, 'playa') !== false) {
        $tipoBuscado = 'playa';
    } elseif (strpos($preguntaLower, 'urbano') !== false || strpos($preguntaLower, 'ciudad') !== false) {
        $tipoBuscado = 'urbano';
    } elseif (strpos($preguntaLower, 'cultural') !== false || strpos($preguntaLower, 'cultura') !== false) {
        $tipoBuscado = 'cultural';
    } elseif (strpos($preguntaLower, 'montaña') !== false || strpos($preguntaLower, 'montana') !== false) {
        $tipoBuscado = 'montaña';
    }
    
    // Filtrar destinos según criterios detectados
    $sugerencias = array_filter($destinos, function($destino) use ($continenteBuscado, $tipoBuscado) {
        $coincide = true;
        if ($continenteBuscado !== '') {
            $coincide = $coincide && ($destino['continente'] === $continenteBuscado);
        }
        if ($tipoBuscado !== '') {
            $coincide = $coincide && ($destino['tipo'] === $tipoBuscado);
        }
        return $coincide;
    });
    
    // Si no hay coincidencias específicas, devolver todos los destinos
    if (empty($sugerencias)) {
        $sugerencias = $destinos;
    }
    
    return [
        'usandoIA' => $usandoIA,
        'destinos' => array_values($sugerencias)
    ];
}

// Procesar consulta de IA si viene por GET
$resultadoIA = null;
if (isset($_GET['pregunta_ia']) && !empty($_GET['pregunta_ia'])) {
    $resultadoIA = sugerirConIA($_GET['pregunta_ia'], $destinos);
}

// Aplicar filtros a los destinos
$destinosFiltrados = filtrarDestinos($destinos, $busqueda, $filtroContinente, $filtroTipo);

// Obtener listas únicas para los selectores
$continentes = array_unique(array_column($destinos, 'continente'));
$tipos = array_unique(array_column($destinos, 'tipo'));
sort($continentes);
sort($tipos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Travel Guide - Descubre tu próximo destino</title>
    <style>
        /* === VARIABLES DE TEMA === */
        :root.claro {
            --color-fondo: #f5f5f5;
            --color-texto: #333333;
            --color-borde: #dddddd;
            --color-tarjeta: #ffffff;
            --color-primario: #0066cc;
            --color-primario-hover: #0052a3;
            --color-secundario: #6c757d;
            --color-error: #dc3545;
            --color-exito: #28a745;
            --sombra: rgba(0, 0, 0, 0.1);
        }
        
        :root.oscuro {
            --color-fondo: #1a1a1a;
            --color-texto: #e0e0e0;
            --color-borde: #444444;
            --color-tarjeta: #2d2d2d;
            --color-primario: #4da6ff;
            --color-primario-hover: #3d8ccc;
            --color-secundario: #adb5bd;
            --color-error: #ff6b6b;
            --color-exito: #51cf66;
            --sombra: rgba(0, 0, 0, 0.3);
        }
        
        /* === ESTILOS GENERALES === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-fondo);
            color: var(--color-texto);
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* === HEADER === */
        .header {
            background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px var(--sombra);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header h1 {
            font-size: 2.5em;
            font-weight: 700;
        }
        
        .theme-switcher {
            display: flex;
            gap: 10px;
        }
        
        .btn-theme {
            padding: 10px 20px;
            border: 2px solid white;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-theme:hover {
            background: white;
            color: var(--color-primario);
        }
        
        .btn-theme.active {
            background: white;
            color: var(--color-primario);
        }
        
        /* === FILTROS === */
        .filtros-section {
            background: var(--color-tarjeta);
            border: 1px solid var(--color-borde);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px var(--sombra);
        }
        
        .filtros-section h2 {
            margin-bottom: 20px;
            color: var(--color-primario);
        }
        
        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid var(--color-borde);
            border-radius: 5px;
            background: var(--color-fondo);
            color: var(--color-texto);
            font-size: 1em;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--color-primario);
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--color-primario);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--color-primario-hover);
        }
        
        .btn-secondary {
            background: var(--color-secundario);
            color: white;
        }
        
        .btn-secondary:hover {
            opacity: 0.9;
        }
        
        .filtros-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        /* === TARJETAS DE DESTINOS === */
        .destinos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .tarjeta-destino {
            background: var(--color-tarjeta);
            border: 1px solid var(--color-borde);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px var(--sombra);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .tarjeta-destino:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px var(--sombra);
        }
        
        .tarjeta-imagen {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .tarjeta-contenido {
            padding: 20px;
        }
        
        .tarjeta-titulo {
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--color-primario);
        }
        
        .tarjeta-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-continente {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-tipo {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        body.oscuro .badge-continente {
            background: #1565c0;
            color: #bbdefb;
        }
        
        body.oscuro .badge-tipo {
            background: #6a1b9a;
            color: #e1bee7;
        }
        
        .tarjeta-descripcion {
            color: var(--color-texto);
            line-height: 1.6;
        }
        
        .no-resultados {
            text-align: center;
            padding: 40px;
            background: var(--color-tarjeta);
            border: 2px dashed var(--color-borde);
            border-radius: 10px;
            color: var(--color-secundario);
        }
        
        /* === FORMULARIO SUGERENCIA === */
        .sugerencia-section {
            background: var(--color-tarjeta);
            border: 1px solid var(--color-borde);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px var(--sombra);
        }
        
        .sugerencia-section h2 {
            margin-bottom: 20px;
            color: var(--color-primario);
        }
        
        .form-error {
            color: var(--color-error);
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .mensaje-exito {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        body.oscuro .mensaje-exito {
            background: #1e4620;
            border-color: #2d5f2f;
            color: #a3cfbb;
        }
        
        .sugerencia-preview {
            background: var(--color-fondo);
            border: 1px solid var(--color-borde);
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .sugerencia-preview h3 {
            margin-bottom: 10px;
            color: var(--color-primario);
        }
        
        /* === SECCIÓN IA === */
        .ia-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px var(--sombra);
        }
        
        .ia-section h2 {
            margin-bottom: 20px;
        }
        
        .ia-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .ia-input {
            flex: 1;
            min-width: 250px;
            padding: 12px;
            border: 2px solid white;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .ia-resultado {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .ia-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #764ba2;
            color: white;
            border-radius: 15px;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }
            
            .destinos-grid {
                grid-template-columns: 1fr;
            }
            
            .filtros-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo $tema === 'oscuro' ? 'oscuro' : 'claro'; ?>">
    <script>
        // Aplicar clase al root para variables CSS
        document.documentElement.className = '<?php echo $tema === 'oscuro' ? 'oscuro' : 'claro'; ?>';
    </script>

    <!-- HEADER -->
    <div class="header">
        <div class="container">
            <div class="header-content">
                <h1>🌍 Mini Travel Guide</h1>
                <div class="theme-switcher">
                    <a href="?tema=claro" class="btn-theme <?php echo $tema === 'claro' ? 'active' : ''; ?>">
                        ☀️ Claro
                    </a>
                    <a href="?tema=oscuro" class="btn-theme <?php echo $tema === 'oscuro' ? 'active' : ''; ?>">
                        🌙 Oscuro
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- SECCIÓN IA -->
        <div class="ia-section">
            <h2>🤖 Asistente de Viajes Inteligente</h2>
            <p>Pregúntame sobre tu próximo destino y te sugeriré opciones personalizadas</p>
            <form method="GET" class="ia-form">
                <input type="hidden" name="tema" value="<?php echo htmlspecialchars($tema); ?>">
                <input 
                    type="text" 
                    name="pregunta_ia" 
                    class="ia-input" 
                    placeholder="Ej: ¿Dónde debería ir para turismo de aventura en Sudamérica?"
                    value="<?php echo isset($_GET['pregunta_ia']) ? htmlspecialchars($_GET['pregunta_ia']) : ''; ?>"
                >
                <button type="submit" class="btn btn-secondary">Sugerir con IA</button>
            </form>
            
            <?php if ($resultadoIA): ?>
                <div class="ia-resultado">
                    <span class="ia-badge">
                        <?php echo $resultadoIA['usandoIA'] ? '🤖 Sugerencias con IA' : '💡 Sugerencias locales'; ?>
                    </span>
                    <h3>Encontramos <?php echo count($resultadoIA['destinos']); ?> destinos para ti:</h3>
                    <div class="destinos-grid" style="margin-top: 20px;">
                        <?php foreach (array_slice($resultadoIA['destinos'], 0, 6) as $destino): ?>
                            <div class="tarjeta-destino">
                                <img src="<?php echo htmlspecialchars($destino['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($destino['titulo']); ?>" 
                                     class="tarjeta-imagen">
                                <div class="tarjeta-contenido">
                                    <h3 class="tarjeta-titulo"><?php echo htmlspecialchars($destino['titulo']); ?></h3>
                                    <div class="tarjeta-meta">
                                        <span class="badge badge-continente"><?php echo htmlspecialchars($destino['continente']); ?></span>
                                        <span class="badge badge-tipo"><?php echo htmlspecialchars($destino['tipo']); ?></span>
                                    </div>
                                    <p class="tarjeta-descripcion">
                                        <?php echo htmlspecialchars($destino['descripcion']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- FILTROS -->
        <div class="filtros-section">
            <h2>🔍 Buscar y Filtrar Destinos</h2>
            <form method="GET">
                <input type="hidden" name="tema" value="<?php echo htmlspecialchars($tema); ?>">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label for="busqueda">Buscar por nombre</label>
                        <input 
                            type="text" 
                            id="busqueda" 
                            name="q" 
                            placeholder="Ej: Rio, Playa, Aventura..."
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="continente">Continente</label>
                        <select id="continente" name="continente">
                            <option value="">Todos los continentes</option>
                            <?php foreach ($continentes as $cont): ?>
                                <option value="<?php echo htmlspecialchars($cont); ?>" 
                                        <?php echo $filtroContinente === $cont ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cont); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo de viaje</label>
                        <select id="tipo" name="tipo">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" 
                                        <?php echo $filtroTipo === $t ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($t)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="filtros-actions">
                    <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                    <a href="?tema=<?php echo htmlspecialchars($tema); ?>" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>

        <!-- RESULTADOS -->
        <h2 style="margin-bottom: 20px;">
            <?php 
            $totalResultados = count($destinosFiltrados);
            echo $totalResultados > 0 
                ? "📍 {$totalResultados} destino" . ($totalResultados > 1 ? 's' : '') . " encontrado" . ($totalResultados > 1 ? 's' : '')
                : "Sin resultados";
            ?>
        </h2>
        
        <?php if (count($destinosFiltrados) > 0): ?>
            <div class="destinos-grid">
                <?php foreach ($destinosFiltrados as $index => $destino): ?>
                    <div class="tarjeta-destino" id="destino-<?php echo $index; ?>">
                        <img src="<?php echo htmlspecialchars($destino['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($destino['titulo']); ?>" 
                             class="tarjeta-imagen"
                             onerror="this.src='https://via.placeholder.com/500x300?text=Imagen+no+disponible'">
                        <div class="tarjeta-contenido">
                            <h3 class="tarjeta-titulo"><?php echo htmlspecialchars($destino['titulo']); ?></h3>
                            <div class="tarjeta-meta">
                                <span class="badge badge-continente">
                                    📍 <?php echo htmlspecialchars($destino['continente']); ?>
                                </span>
                                <span class="badge badge-tipo">
                                    <?php 
                                    $iconos = [
                                        'playa' => '🏖️',
                                        'montaña' => '⛰️',
                                        'urbano' => '🏙️',
                                        'aventura' => '🧗',
                                        'cultural' => '🏛️'
                                    ];
                                    echo ($iconos[$destino['tipo']] ?? '🌟') . ' ' . ucfirst(htmlspecialchars($destino['tipo']));
                                    ?>
                                </span>
                            </div>
                            <p style="margin-bottom: 10px;">
                                <strong>País:</strong> <?php echo htmlspecialchars($destino['pais']); ?>
                            </p>
                            <p class="tarjeta-descripcion">
                                <?php echo htmlspecialchars($destino['descripcion']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-resultados">
                <h3>😔 No se encontraron destinos</h3>
                <p>Intenta ajustar tus filtros de búsqueda</p>
            </div>
        <?php endif; ?>

        <!-- FORMULARIO DE SUGERENCIA -->
        <div class="sugerencia-section">
            <h2>✨ Sugerir un Nuevo Destino</h2>
            
            <?php if ($sugerenciaEnviada): ?>
                <div class="mensaje-exito">
                    <strong>✅ ¡Gracias por tu sugerencia!</strong>
                    <p>Hemos recibido tu propuesta de destino. A continuación puedes ver un resumen:</p>
                </div>
                <div class="sugerencia-preview">
                    <h3>📝 Resumen de tu sugerencia:</h3>
                    <p><strong>Título:</strong> <?php echo $datosSugerencia['titulo']; ?></p>
                    <p><strong>País:</strong> <?php echo $datosSugerencia['pais']; ?></p>
                    <p><strong>Continente:</strong> <?php echo $datosSugerencia['continente']; ?></p>
                    <p><strong>Tipo:</strong> <?php echo ucfirst($datosSugerencia['tipo']); ?></p>
                    <p><strong>Descripción:</strong> <?php echo $datosSugerencia['descripcion']; ?></p>
                    <p><strong>URL de imagen:</strong> <?php echo $datosSugerencia['imagen']; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="accion" value="sugerir">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label for="sug-titulo">Título del destino *</label>
                        <input 
                            type="text" 
                            id="sug-titulo" 
                            name="titulo" 
                            placeholder="Ej: Machu Picchu"
                            value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>"
                            required
                        >
                        <?php if (isset($erroresValidacion['titulo'])): ?>
                            <div class="form-error"><?php echo $erroresValidacion['titulo']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="sug-pais">País *</label>
                        <input 
                            type="text" 
                            id="sug-pais" 
                            name="pais" 
                            placeholder="Ej: Perú"
                            value="<?php echo isset($_POST['pais']) ? htmlspecialchars($_POST['pais']) : ''; ?>"
                            required
                        >
                        <?php if (isset($erroresValidacion['pais'])): ?>
                            <div class="form-error"><?php echo $erroresValidacion['pais']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="sug-continente">Continente *</label>
                        <select id="sug-continente" name="continente" required>
                            <option value="">Selecciona un continente</option>
                            <option value="Sudamérica" <?php echo (isset($_POST['continente']) && $_POST['continente'] === 'Sudamérica') ? 'selected' : ''; ?>>Sudamérica</option>
                            <option value="Europa" <?php echo (isset($_POST['continente']) && $_POST['continente'] === 'Europa') ? 'selected' : ''; ?>>Europa</option>
                            <option value="Asia" <?php echo (isset($_POST['continente']) && $_POST['continente'] === 'Asia') ? 'selected' : ''; ?>>Asia</option>
                            <option value="África" <?php echo (isset($_POST['continente']) && $_POST['continente'] === 'África') ? 'selected' : ''; ?>>África</option>
                            <option value="América del Norte" <?php echo (isset($_POST['continente']) && $_POST['continente'] === 'América del Norte') ? 'selected' : ''; ?>>América del Norte</option>
                            <option value="Oceanía" <?php echo (isset($_POST['continente']) && $_POST['continente'] === 'Oceanía') ? 'selected' : ''; ?>>Oceanía</option>
                        </select>
                        <?php if (isset($erroresValidacion['continente'])): ?>
                            <div class="form-error"><?php echo $erroresValidacion['continente']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="sug-tipo">Tipo de viaje *</label>
                        <select id="sug-tipo" name="tipo" required>
                            <option value="">Selecciona un tipo</option>
                            <option value="playa" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'playa') ? 'selected' : ''; ?>>Playa</option>
                            <option value="montaña" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'montaña') ? 'selected' : ''; ?>>Montaña</option>
                            <option value="urbano" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'urbano') ? 'selected' : ''; ?>>Urbano</option>
                            <option value="aventura" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'aventura') ? 'selected' : ''; ?>>Aventura</option>
                            <option value="cultural" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'cultural') ? 'selected' : ''; ?>>Cultural</option>
                        </select>
                        <?php if (isset($erroresValidacion['tipo'])): ?>
                            <div class="form-error"><?php echo $erroresValidacion['tipo']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="sug-descripcion">Descripción *</label>
                    <textarea 
                        id="sug-descripcion" 
                        name="descripcion" 
                        rows="3" 
                        placeholder="Describe el destino turístico..."
                        style="padding: 10px; border: 1px solid var(--color-borde); border-radius: 5px; background: var(--color-fondo); color: var(--color-texto); font-family: inherit; width: 100%; resize: vertical;"
                        required
                    ><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                    <?php if (isset($erroresValidacion['descripcion'])): ?>
                        <div class="form-error"><?php echo $erroresValidacion['descripcion']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="sug-imagen">URL de imagen *</label>
                    <input 
                        type="url" 
                        id="sug-imagen" 
                        name="imagen" 
                        placeholder="https://ejemplo.com/imagen.jpg"
                        value="<?php echo isset($_POST['imagen']) ? htmlspecialchars($_POST['imagen']) : ''; ?>"
                        required
                    >
                    <?php if (isset($erroresValidacion['imagen'])): ?>
                        <div class="form-error"><?php echo $erroresValidacion['imagen']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Enviar Sugerencia</button>
                </div>
            </form>
        </div>

        <!-- FOOTER -->
        <footer style="text-align: center; padding: 30px 0; border-top: 1px solid var(--color-borde); margin-top: 40px;">
            <p>
                <strong>Mini Travel Guide</strong> - Parcial 1 P2 ACN2BV<br>
                Desarrollado por <strong>Albert Lukmanov</strong><br>
                <a href="mailto:albert.lukmanov@davinci.edu.ar" style="color: var(--color-primario);">albert.lukmanov@davinci.edu.ar</a> | 
                <a href="https://github.com/etonealbert" target="_blank" style="color: var(--color-primario);">GitHub</a>
            </p>
        </footer>
    </div>
</body>
</html>

