<?php
// delete_news.php
session_start();
header('Content-Type: application/json');

// Proteção de login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$json_file = 'data/noticias.json';
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);
$slug_to_delete = $data['slug'] ?? null;

if (empty($slug_to_delete)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Slug não fornecido.']);
    exit;
}

// 1. Carrega as notícias existentes
if (file_exists($json_file)) {
    $current_data = file_get_contents($json_file);
    $news_array = json_decode($current_data, true) ?? [];
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Arquivo de dados não encontrado.']);
    exit;
}

// 2. Filtra o array, mantendo apenas os itens que NÃO correspondem ao slug a ser excluído
$initial_count = count($news_array);
$news_array = array_filter($news_array, function($item) use ($slug_to_delete) {
    return $item['slug'] !== $slug_to_delete;
});
$final_count = count($news_array);

if ($initial_count === $final_count) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Notícia não encontrada para exclusão.']);
    exit;
}

// 3. Salva o novo array (com índices redefinidos para evitar objetos vazios no JSON)
$result = file_put_contents(
    $json_file, 
    json_encode(array_values($news_array), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($result !== false) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Notícia excluída com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao escrever no arquivo. Verifique permissões.']);
}
?>