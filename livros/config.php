<?php
/**
 * CONFIGURAÇÃO DO FTP
 * -------------------
 * Preencha aqui os dados de acesso ao seu servidor FTP.
 * Estes são os mesmos dados que você usaria em um programa
 * como FileZilla para se conectar.
 */

return [
    // Endereço do servidor FTP (sem "ftp://"), ex: ftp.meusite.com.br ou 192.168.0.10
    'host'      => '147.93.39.241',

    // Usuário do FTP
    'user'      => 'u108357758.seminario',

    // Senha do FTP
    'pass'      => '##S3m1n4r1025',

    // Porta do FTP (o padrão é 21)
    'port'      => 21,

    // Se o seu FTP exige conexão segura (FTPS), deixe true. Se for o FTP comum, deixe false.
    'ftps'      => false,

    // Modo passivo quase sempre deve ficar "true" (evita problemas de firewall)
    'passive'   => true,

    // Pasta dentro do FTP onde os arquivos serão enviados e listados.
    // Use "/" para a pasta raiz, ou algo como "/uploads" para uma subpasta.
    'remote_dir' => '/media/livros',

    // Tamanho máximo de upload permitido, em MB (apenas trava no lado do site;
    // o php.ini do servidor também precisa permitir esse tamanho)
    'max_upload_mb' => 50,
];
