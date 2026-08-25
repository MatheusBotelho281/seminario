<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/ftp_helper.php';
$cfg = require __DIR__ . '/config.php';

function responder($ok, $mensagem, $extra = [])
{
    echo json_encode(array_merge(['ok' => $ok, 'mensagem' => $mensagem], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['arquivo'])) {
    responder(false, 'Nenhum arquivo recebido.');
}

$arquivo = $_FILES['arquivo'];

if ($arquivo['error'] !== UPLOAD_ERR_OK) {
    responder(false, 'Falha ao receber o arquivo (código ' . $arquivo['error'] . ').');
}

$limiteBytes = $cfg['max_upload_mb'] * 1024 * 1024;
if ($arquivo['size'] > $limiteBytes) {
    responder(false, 'Arquivo maior que o limite permitido (' . $cfg['max_upload_mb'] . ' MB).');
}

$nomeFinal = nome_seguro($arquivo['name']);

try {
    $conn = ftp_conectar($cfg);

    $sucesso = ftp_put($conn, $nomeFinal, $arquivo['tmp_name'], FTP_BINARY);

    ftp_close($conn);

    if ($sucesso) {
        responder(true, 'Arquivo "' . $nomeFinal . '" enviado com sucesso.');
    } else {
        responder(false, 'O servidor FTP recusou o envio do arquivo "' . $nomeFinal . '".');
    }
} catch (Exception $e) {
    responder(false, $e->getMessage());
}
