<?php
// update_news.php
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

$slug_to_update = $data['slug'] ?? null;
$new_status = $data['status'] ?? null;
$action = $data['action'] ?? null; 

// Verificação de dados para o toggle status
if ($action !== 'status' || empty($slug_to_update) || !in_array($new_status, ['published', 'hidden'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados de status inválidos ou incompletos.']);
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

$updated = false;

// 2. Itera e atualiza apenas o campo 'status'
foreach ($news_array as $key => $item) {
    if ($item['slug'] === $slug_to_update) {
        $news_array[$key]['status'] = $new_status;
        $updated = true;
        break;
    }
}

if (!$updated) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Notícia não encontrada para alteração de status.']);
    exit;
}

// 3. Salva o novo array no arquivo JSON
$result = file_put_contents(
    $json_file, 
    json_encode($news_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($result !== false) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Status da notícia atualizado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao escrever no arquivo. Verifique permissões.']);
}
?>