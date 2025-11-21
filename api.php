<?php
header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/data/items.json';

function loadItems(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $items = json_decode($json, true);

    return is_array($items) ? $items : [];
}

function saveItems(string $file, array $items): bool
{
    $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    return (bool) file_put_contents($file, $json);
}

function filterItems(array $items, string $search, string $category): array
{
    return array_values(array_filter($items, function ($item) use ($search, $category) {
        $matchesSearch = $search === '' || stripos($item['nombre'], $search) !== false;
        $matchesCategory = $category === '' || strcasecmp($item['categoria'], $category) === 0;

        return $matchesSearch && $matchesCategory;
    }));
}

function validatePayload(array $data): array
{
    $errors = [];

    foreach (['nombre', 'categoria', 'descripcion', 'imagen'] as $field) {
        if (empty($data[$field]) || !is_string($data[$field])) {
            $errors[$field] = 'Campo obligatorio';
            continue;
        }

        $data[$field] = trim($data[$field]);
        if (mb_strlen($data[$field]) < 3) {
            $errors[$field] = 'Debe contener al menos 3 caracteres';
        }
    }

    if (!empty($data['imagen']) && !filter_var($data['imagen'], FILTER_VALIDATE_URL)) {
        $errors['imagen'] = 'Debe ser una URL válida';
    }

    return $errors;
}

$method = $_SERVER['REQUEST_METHOD'];
$items = loadItems($dataFile);

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $errors = validatePayload($payload);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    $newItem = [
        'id' => 'destino-' . (count($items) + 1),
        'nombre' => trim($payload['nombre']),
        'categoria' => trim($payload['categoria']),
        'descripcion' => trim($payload['descripcion']),
        'imagen' => trim($payload['imagen'])
    ];

    $items[] = $newItem;
    if (!saveItems($dataFile, $items)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo guardar la información.']);
        exit;
    }

    echo json_encode(['ok' => true, 'item' => $newItem]);
    exit;
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

$filtered = filterItems($items, $search, $category);

$response = [
    'ok' => true,
    'total' => count($filtered),
    'categorias' => array_values(array_unique(array_column($items, 'categoria'))),
    'items' => $filtered
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
