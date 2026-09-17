<?php
/**
 * Funções auxiliares usadas por todos os endpoints:
 * - leitura/escrita dos arquivos JSON que guardam os dados no servidor
 * - chamada à API do TMDb usando a chave definida em config.php
 * - resposta padronizada em JSON
 */

function data_path($name) {
    return __DIR__ . '/data/' . $name;
}

function read_json($name, $default) {
    $path = data_path($name);
    if (!file_exists($path)) {
        return $default;
    }
    $fp = fopen($path, 'r');
    if (!$fp) return $default;
    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = json_decode($content, true);
    return $data === null && trim($content) !== 'null' ? $default : $data;
}

function write_json($name, $data) {
    $path = data_path($name);
    $fp = fopen($path, 'c');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function tmdb_get($path, $params = []) {
    $params['api_key'] = TMDB_API_KEY;
    $url = TMDB_BASE . $path . '?' . http_build_query($params);
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    return json_decode($raw, true);
}
