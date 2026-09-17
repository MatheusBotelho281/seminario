<?php
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $draw = read_json('draw.json', null);
    json_response(['draw' => $draw]);
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $body['action'] ?? '';

    if ($action === 'draw') {
        $count = max(1, intval($body['count'] ?? 1));
        $suggestions = read_json('suggestions.json', []);

        if (!count($suggestions)) {
            json_response(['error' => 'Não há filmes sugeridos para sortear.'], 400);
        }
        $count = min($count, count($suggestions));

        $shuffled = $suggestions;
        shuffle($shuffled);
        $chosen = array_slice($shuffled, 0, $count);

        $chosenIds = array_map(function ($m) { return $m['id']; }, $chosen);
        $remaining = array_values(array_filter($suggestions, function ($m) use ($chosenIds) {
            return !in_array($m['id'], $chosenIds);
        }));
        write_json('suggestions.json', $remaining);

        $votes = [];
        foreach ($chosen as $m) {
            $votes[$m['id']] = 0;
        }

        $draw = [
            'movies' => $chosen,
            'votes' => $votes,
            'closed' => false,
            'voters' => []
        ];
        write_json('draw.json', $draw);
        json_response(['draw' => $draw]);
    }

    if ($action === 'vote') {
        $draw = read_json('draw.json', null);
        if (!$draw || $draw['closed']) {
            json_response(['error' => 'Não há votação em andamento.'], 400);
        }

        $movieId = $body['movieId'] ?? null;
        $voterId = $body['voterId'] ?? null;
        if (!$movieId || !$voterId) {
            json_response(['error' => 'Dados incompletos.'], 400);
        }
        if (!array_key_exists($movieId, $draw['votes'])) {
            json_response(['error' => 'Este filme não está na rodada atual.'], 400);
        }
        if (in_array($voterId, $draw['voters'])) {
            json_response(['error' => 'Você já votou nesta rodada.'], 409);
        }

        $draw['votes'][$movieId] += 1;
        $draw['voters'][] = $voterId;
        write_json('draw.json', $draw);
        json_response(['draw' => $draw]);
    }

    if ($action === 'close') {
        $draw = read_json('draw.json', null);
        if (!$draw) json_response(['error' => 'Nenhuma votação ativa.'], 400);
        $draw['closed'] = true;
        write_json('draw.json', $draw);
        json_response(['draw' => $draw]);
    }

    if ($action === 'newRound') {
        write_json('draw.json', null);
        json_response(['draw' => null]);
    }

    json_response(['error' => 'Ação inválida.'], 400);
}

json_response(['error' => 'Método não suportado'], 405);
