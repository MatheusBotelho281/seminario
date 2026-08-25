<?php
/**
 * Funções auxiliares para conexão FTP.
 * Não precisa mexer neste arquivo.
 */

function ftp_conectar(array $cfg)
{
    if (!extension_loaded('ftp')) {
        throw new Exception('A extensão FTP do PHP não está habilitada neste servidor. Peça ao suporte da hospedagem para habilitar a extensão "ftp".');
    }

    if ($cfg['ftps']) {
        $conn = @ftp_ssl_connect($cfg['host'], $cfg['port'], 10);
    } else {
        $conn = @ftp_connect($cfg['host'], $cfg['port'], 10);
    }

    if (!$conn) {
        throw new Exception('Não foi possível conectar ao servidor FTP. Verifique o endereço (host) e a porta em config.php.');
    }

    $login = @ftp_login($conn, $cfg['user'], $cfg['pass']);
    if (!$login) {
        ftp_close($conn);
        throw new Exception('Usuário ou senha do FTP incorretos. Verifique config.php.');
    }

    if ($cfg['passive']) {
        ftp_pasv($conn, true);
    }

    // Garante que a pasta remota exista; se não existir, tenta criar.
    $dir = $cfg['remote_dir'];
    if ($dir !== '' && $dir !== '/') {
        if (!@ftp_chdir($conn, $dir)) {
            if (!@ftp_mkdir($conn, $dir)) {
                ftp_close($conn);
                throw new Exception('A pasta remota "' . $dir . '" não existe e não foi possível criá-la. Verifique as permissões do usuário FTP.');
            }
            ftp_chdir($conn, $dir);
        }
    }

    return $conn;
}

function formatar_tamanho(int $bytes): string
{
    if ($bytes <= 0) return '0 B';
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    $i = max(0, min($i, count($unidades) - 1));
    return round($bytes / pow(1024, $i), 1) . ' ' . $unidades[$i];
}

function nome_seguro(string $nome): string
{
    // Remove caracteres problemáticos do nome do arquivo, mantendo acentos comuns.
    $nome = str_replace(['/', '\\'], '_', $nome);
    return trim($nome);
}
