<?php require_once __DIR__.'/config/bootstrap.php'; if(is_internal()) redirect('/admin/index.php'); if(is_client()) redirect('/cliente/index.php'); redirect('/login.php');
