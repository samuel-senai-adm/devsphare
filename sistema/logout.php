<?php
session_start();

// Remove todas as variáveis da sessão
$_SESSION = [];

// Destrói a sessão
session_destroy();

// Redireciona para a tela de login
header("Location: login.php");
exit;
