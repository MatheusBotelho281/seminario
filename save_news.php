<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mantenha isso para testes, mas proteja em produção!
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
// ESTE BLOCO DE CÓDIGO DEVE SER O PRIMEIRO NO ARQUIVO!

// Proteção de login: Se não estiver logado, encerra a execução com erro 403 (Acesso Proibido)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado. Por favor, faça login.']);
    exit;
}

// O restante do seu script PHP (headers, verificação POST, file_put_contents...) continua abaixo.

header('Content-Type: application/json');
// 1. Define o caminho do arquivo JSON
// ASSEGURE-SE de que a pasta 'data/' existe e tem permissão de escrita (CHMOD 777)
$json_file = 'data/noticias.json';

// Lida com requisições OPTIONS (necessário para CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// 3. Recebe o conteúdo JSON e decodifica
$json_input = file_get_contents('php://input');
$new_news_item = json_decode($json_input, true);

if (empty($new_news_item) || !isset($new_news_item['slug'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos recebidos. Verifique se o slug foi gerado.']);
    exit;
}
$new_news_item['status'] = 'published'; // <--- ADICIONE ESTA LINHA PARA DEFINIR O STATUS PADRÃO

array_unshift($news_array, $new_news_item);
// 4. Carrega as notícias existentes
if (file_exists($json_file)) {
    // Tenta carregar o conteúdo, se falhar, assume um array vazio
    $current_data = file_get_contents($json_file);
    // Adicionado tratamento de erro para json_decode
    $news_array = json_decode($current_data, true);
    if ($news_array === null) {
        $news_array = [];
    }
} else {
    $news_array = [];
}

// 5. Adiciona a nova notícia (na primeira posição para ser a mais recente)
array_unshift($news_array, $new_news_item);

// 6. Salva o array de volta no arquivo JSON
// OTIMIZAÇÃO: JSON_PRETTY_PRINT (formatação legível) e JSON_UNESCAPED_UNICODE (acentos)
$result = file_put_contents(
    $json_file,
    json_encode($news_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($result !== false) {
    // CORRIGIDO: 201 Created é o código correto para criação de recurso
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Notícia salva com sucesso!', 'file' => $json_file]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao escrever no arquivo. Verifique permissões de escrita na pasta data/ e no arquivo noticias.json.']);
}
?>