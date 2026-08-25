<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/ftp_helper.php';
$cfg = require __DIR__ . '/config.php';

try {
    $conn = ftp_conectar($cfg);

    // ftp_rawlist traz nome, tamanho e data em uma linha de texto (formato "ls -l")
    $linhas = ftp_rawlist($conn, '.');
    ftp_close($conn);

    $arquivos = [];
    foreach ($linhas as $linha) {
        // Ignora pastas (linhas que começam com "d")
        if (preg_match('/^d/', $linha)) {
            continue;
        }

        // Formato típico Unix: permissões links dono grupo tamanho mes dia hora/ano nome
        $partes = preg_split('/\s+/', $linha, 9);
        if (count($partes) < 9) {
            continue;
        }

        $tamanho = (int) $partes[4];
        $data = $partes[5] . ' ' . $partes[6] . ' ' . $partes[7];
        $nome = $partes[8];

        $arquivos[] = [
            'nome' => $nome,
            'tamanho' => formatar_tamanho($tamanho),
            'data' => $data,
        ];
    }

    echo json_encode(['ok' => true, 'arquivos' => $arquivos]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensagem' => $e->getMessage()]);
}
