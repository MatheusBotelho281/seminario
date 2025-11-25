<?php
// Inicia a sessão PHP para armazenar o estado de login
session_start();

// Credenciais de Admin (Substitua por credenciais reais!)
$valid_username = 'admin';
$valid_password = 'amordivinosemi'; // Mude isso!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verifica se as credenciais correspondem
    if ($username === $valid_username && $password === $valid_password) {
        // Credenciais válidas: marca o usuário como logado
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        
        // Redireciona para o painel administrativo
        header('Location: admin.html');
        exit;
    } else {
        // Credenciais inválidas: redireciona de volta para a página de login com erro
        header('Location: login.html?error=1');
        exit;
    }
} else {
    // Acesso direto sem POST: redireciona para a página de login
    header('Location: login.html');
    exit;
}
?>