<?php require_once __DIR__.'/config/bootstrap.php';
if(is_logged()){ log_action('logout'); }
$_SESSION=[]; session_destroy(); redirect('/login.php?logout=1');
