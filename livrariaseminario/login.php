<?php
require_once __DIR__.'/config/bootstrap.php';
if(is_internal()) redirect('/admin/index.php'); if(is_client()) redirect('/cliente/index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    check_csrf(); $login=trim($_POST['login']??''); $senha=$_POST['senha']??'';
    $st=db()->prepare('SELECT * FROM usuarios WHERE login=? AND ativo=1 LIMIT 1'); $st->execute([$login]); $u=$st->fetch();
    if($u && password_verify($senha,$u['senha_hash'])){
        session_regenerate_id(true);
        $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['nome'],'login'=>$u['login'],'kind'=>'internal','first_access'=>(int)$u['primeiro_acesso']];
        log_action('login','usuarios',(int)$u['id']); redirect('/admin/index.php');
    }
    $st=db()->prepare('SELECT * FROM clientes WHERE login=? AND ativo=1 LIMIT 1'); $st->execute([$login]); $c=$st->fetch();
    if($c && password_verify($senha,$c['senha_hash'])){
        session_regenerate_id(true);
        $_SESSION['user']=['id'=>(int)$c['id'],'name'=>$c['nome'],'login'=>$c['login'],'kind'=>'client'];
        log_action('login','clientes',(int)$c['id']); redirect('/cliente/index.php');
    }
    $error='Login ou senha inválidos.';
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(APP_NAME)?></title><meta name="app-base" content="<?=e(app_base_path())?>"><link rel="stylesheet" href="<?=e(app_url('assets/css/style.css'))?>"></head>
<body class="auth-body"><div class="auth-card"><div class="brand-mark">NS</div><h1><?=e(APP_NAME)?></h1><p class="muted">Acesso à área do cliente e ao sistema interno.</p><?php if(isset($_GET['logout'])): ?><div class="alert success">Sessão encerrada.</div><?php endif; ?><?php if($error): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Login<input name="login" required autofocus autocomplete="username"></label><label>Senha<input type="password" name="senha" required autocomplete="current-password"></label><button class="btn primary w100">Entrar</button></form><div class="auth-tools"><button type="button" class="link-button" onclick="toggleTheme()">Tema claro/escuro</button></div></div><script src="<?=e(app_url('assets/js/app.js'))?>"></script></body></html>
