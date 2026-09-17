<?php
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
if ($query === '') {
    json_response(['results' => []]);
}

$data = tmdb_get('/search/movie', [
    'query' => $query,
    'language' => 'pt-BR',
    'region' => 'BR',
    'include_adult' => 'false',
    'page' => 1
]);

if (!$data || !isset($data['results'])) {
    json_response(['error' => 'Não foi possível consultar o TMDb. Verifique a chave da API em config.php.'], 502);
}

$results = array_map(function ($m) {
    return [
        'id' => $m['id'],
        'title' => $m['title'],
        'year' => (!empty($m['release_date']) && strlen($m['release_date']) >= 4) ? substr($m['release_date'], 0, 4) : '—',
        'rating' => (!empty($m['vote_average'])) ? round($m['vote_average'], 1) : null,
        'poster' => !empty($m['poster_path']) ? 'https://image.tmdb.org/t/p/w200' . $m['poster_path'] : null
    ];
}, array_slice($data['results'], 0, 8));

json_response(['results' => $results]);
