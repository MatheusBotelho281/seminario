<?php
require_once __DIR__ . '/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('livraria_session');
    session_start([
        'cookie_httponly'=>true,
        'cookie_samesite'=>'Lax',
        'cookie_secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}
