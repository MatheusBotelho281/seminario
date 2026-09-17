<?php
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $suggestions = read_json('suggestions.json', []);
    json_response(['suggestions' => $suggestions]);
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || empty($body['id']) || empty($body['title'])) {
        json_response(['error' => 'Dados incompletos.'], 400);
    }

    $suggestions = read_json('suggestions.json', []);

    foreach ($suggestions as $s) {
        if ($s['id'] == $body['id']) {
            json_response(['suggestion' => $s]);
        }
    }

    $suggestion = [
        'id' => $body['id'],
        'title' => $body['title'],
        'year' => $body['year'] ?? '—',
        'rating' => $body['rating'] ?? null,
        'poster' => $body['poster'] ?? null,
        'age' => find_certification($body['id']),
        'trailer' => find_trailer($body['id'])
    ];

    $suggestions[] = $suggestion;
    write_json('suggestions.json', $suggestions);
    json_response(['suggestion' => $suggestion], 201);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        parse_str(file_get_contents('php://input'), $body);
        $id = $body['id'] ?? null;
    }
    if (!$id) json_response(['error' => 'id obrigatório'], 400);

    $suggestions = read_json('suggestions.json', []);
    $suggestions = array_values(array_filter($suggestions, function ($s) use ($id) {
        return $s['id'] != $id;
    }));
    write_json('suggestions.json', $suggestions);
    json_response(['ok' => true]);
}

json_response(['error' => 'Método não suportado'], 405);

// ---------- helpers ----------

function find_trailer($movieId) {
    $data = tmdb_get("/movie/$movieId/videos", ['language' => 'pt-BR']);
    $trailer = pick_trailer($data['results'] ?? []);
    if ($trailer) return $trailer;

    $data = tmdb_get("/movie/$movieId/videos", ['language' => 'en-US']);
    return pick_trailer($data['results'] ?? []);
}

function pick_trailer($results) {
    $yt = array_values(array_filter($results, function ($v) {
        return $v['site'] === 'YouTube';
    }));
    if (!$yt) return null;

    foreach ($yt as $v) {
        if ($v['type'] === 'Trailer' && !empty($v['official'])) return 'https://www.youtube.com/watch?v=' . $v['key'];
    }
    foreach ($yt as $v) {
        if ($v['type'] === 'Trailer') return 'https://www.youtube.com/watch?v=' . $v['key'];
    }
    foreach ($yt as $v) {
        if ($v['type'] === 'Teaser') return 'https://www.youtube.com/watch?v=' . $v['key'];
    }
    return 'https://www.youtube.com/watch?v=' . $yt[0]['key'];
}

function find_certification($movieId) {
    $data = tmdb_get("/movie/$movieId/release_dates", []);
    $results = $data['results'] ?? [];
    return extract_cert($results, 'BR') ?? extract_cert($results, 'US');
}

function extract_cert($results, $country) {
    foreach ($results as $r) {
        if ($r['iso_3166_1'] === $country) {
            foreach ($r['release_dates'] as $rd) {
                if (!empty(trim($rd['certification'] ?? ''))) return trim($rd['certification']);
            }
        }
    }
    return null;
}
