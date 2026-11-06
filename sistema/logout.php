<?php
require_once __DIR__ . '/config/sessao.php';

// Apaga as variáveis, o arquivo da sessão no servidor e o cookie do navegador
encerrarSessao();

// Redireciona para a tela de login
header("Location: login.php");
exit;
