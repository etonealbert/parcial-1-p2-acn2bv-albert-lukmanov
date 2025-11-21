<?php
$dataFile = __DIR__ . '/data/items.json';
$tema = isset($_GET['tema']) && $_GET['tema'] === 'oscuro' ? 'oscuro' : 'claro';
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

$items = [];
if (file_exists($dataFile)) {
    $items = json_decode(file_get_contents($dataFile), true) ?? [];
}

function filtrar(array $items, string $q, string $categoria): array
{
    return array_values(array_filter($items, function ($item) use ($q, $categoria) {
        $coincideBusqueda = $q === '' || stripos($item['nombre'], $q) !== false;
        $coincideCategoria = $categoria === '' || strcasecmp($item['categoria'], $categoria) === 0;

        return $coincideBusqueda && $coincideCategoria;
    }));
}

$itemsFiltrados = filtrar($items, $busqueda, $categoria);
$categoriasDisponibles = array_values(array_unique(array_column($items, 'categoria')));
$temaClaroUrl = '?' . http_build_query(['tema' => 'claro', 'q' => $busqueda, 'categoria' => $categoria]);
$temaOscuroUrl = '?' . http_build_query(['tema' => 'oscuro', 'q' => $busqueda, 'categoria' => $categoria]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parcial 2 - Catálogo dinámico</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="tema-<?php echo htmlspecialchars($tema); ?>">
    <header>
        <div>
            <p class="small-text" style="margin:0;opacity:0.8;">PHP + HTML + CSS + JavaScript</p>
            <h1>Parcial 2 · Catálogo de destinos</h1>
        </div>
        <div class="theme-toggle">
            <a class="btn-theme <?php echo $tema === 'claro' ? 'active' : ''; ?>" href="<?php echo $temaClaroUrl; ?>">☀️ Claro</a>
            <a class="btn-theme <?php echo $tema === 'oscuro' ? 'active' : ''; ?>" href="<?php echo $temaOscuroUrl; ?>">🌙 Oscuro</a>
        </div>
    </header>

    <main class="container">
        <section class="panel">
            <h2>Filtros y búsqueda</h2>
            <p class="small-text">Los datos provienen de <strong>data/items.json</strong> y también están disponibles vía <code>api.php</code>.</p>
            <form id="form-filtros" class="filtros-grid" method="GET">
                <div>
                    <label class="label" for="q">Buscar por nombre</label>
                    <input class="input" type="text" id="q" name="q" placeholder="Ej: playa, montaña, Barcelona" value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div>
                    <label class="label" for="categoria">Categoría</label>
                    <select class="select" id="categoria" name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categoriasDisponibles as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="tema" value="<?php echo htmlspecialchars($tema); ?>">
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Aplicar filtros</button>
                    <button class="btn btn-secondary" type="button" id="btn-limpiar">Limpiar</button>
                </div>
            </form>
            <p class="resumen" data-summary>
                <?php echo count($itemsFiltrados); ?> ítems encontrados
                <?php echo $busqueda !== '' ? 'para "' . htmlspecialchars($busqueda) . '"' : ''; ?>
            </p>
        </section>

        <section class="panel" style="margin-top:18px;">
            <h2>Listado de ítems</h2>
            <div class="grid" data-cards>
                <?php foreach ($itemsFiltrados as $item): ?>
                    <article class="card">
                        <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($item['nombre']); ?></h3>
                            <span class="badge">🏷️ <?php echo htmlspecialchars($item['categoria']); ?></span>
                            <p><?php echo htmlspecialchars($item['descripcion']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel" style="margin-top:18px;">
            <h2>Agregar nuevo ítem</h2>
            <p class="small-text">El formulario valida en frontend y backend. Si es correcto se guarda en <code>items.json</code> y se actualiza la grilla sin recargar.</p>
            <div id="alerta"></div>
            <form id="form-agregar" class="filtros-grid">
                <div>
                    <label class="label" for="nombre">Nombre *</label>
                    <input class="input" type="text" id="nombre" name="nombre" required minlength="3" placeholder="Nombre del destino">
                </div>
                <div>
                    <label class="label" for="categoria-nueva">Categoría *</label>
                    <input class="input" list="categorias" id="categoria-nueva" name="categoria" required placeholder="Playa, Montaña, Cultural...">
                    <datalist id="categorias">
                        <?php foreach ($categoriasDisponibles as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="label" for="imagen">URL de imagen *</label>
                    <input class="input" type="url" id="imagen" name="imagen" required placeholder="https://ejemplo.com/foto.jpg">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="label" for="descripcion">Descripción *</label>
                    <textarea class="textarea" id="descripcion" name="descripcion" rows="3" required minlength="10" placeholder="Describe el destino o producto"></textarea>
                </div>
                <div class="actions" style="grid-column: 1 / -1;">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <button class="btn btn-secondary" type="reset">Limpiar</button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>Repositorio con API JSON y soporte de tema <strong>claro/oscuro</strong>.</p>
    </footer>

    <script src="script.js" defer></script>
</body>
</html>
