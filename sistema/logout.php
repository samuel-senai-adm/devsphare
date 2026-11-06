<?php
require_once __DIR__ . '/config/sessao.php';
iniciarSessao();

// Remove todas as variáveis da sessão
$_SESSION = [];

// Destrói a sessão
session_destroy();

// Redireciona para a tela de login
header("Location: login.php");
exit;
