<?php

require_once __DIR__ . '/../config/sessao.php';
iniciarSessao();

// Erros vão para o error_log do servidor, nunca para a tela do visitante
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Usuario.php';

$usuario = new Usuario($conn);


/* =========================
   CADASTRO
   (o perfil nunca é lido do formulário: o model grava sempre 'usuario')
   ========================= */

if (isset($_POST['cadastrar'])) {

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    $cpf = trim((string) ($_POST['cpf'] ?? ''));

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '' || $cpf === '') {
        header("Location: ../cadastro.php?erro=1");
        exit;
    }


    /* =========================
       VERIFICA SE O EMAIL JÁ EXISTE
       ========================= */

    if ($usuario->emailExiste($email)) {

        header("Location: ../cadastro.php?erro=email");
        exit;
    }


    /* =========================
       REALIZA O CADASTRO
       ========================= */

    $cadastro = $usuario->cadastrar(
        $nome,
        $email,
        $senha,
        $cpf
    );


    /* =========================
       VERIFICA SE O CADASTRO DEU CERTO
       ========================= */

    if ($cadastro) {

        header("Location: ../login.php?sucesso=1");
        exit;

    } else {

        header("Location: ../cadastro.php?erro=1");
        exit;
    }
}


/* =========================
   LOGIN
   Um formulário só: o tipo da conta vem do banco, nunca do navegador.
   ========================= */

if (isset($_POST['login'])) {

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    $dadosUsuario = $usuario->login($email, $senha);

    if ($dadosUsuario && !empty($dadosUsuario['bloqueado'])) {

        // Senha certa, mas a conta foi bloqueada por um administrador
        header("Location: ../login.php?bloqueado=1");
        exit;
    }

    if ($dadosUsuario) {

        // Novo ID de sessão a cada login (evita sequestro / fixação de sessão)
        session_regenerate_id(true);

        // Só o ID fica na sessão; perfil e bloqueio são relidos do banco a cada página
        $_SESSION = [];
        $_SESSION['usuario_id'] = (int) $dadosUsuario['id'];
        $_SESSION['usuario_nome'] = $dadosUsuario['nome'];
        $_SESSION['login_em'] = time();
        tokenCsrf();

        // Administrador vai para o painel; usuário comum, para o dashboard
        if ($dadosUsuario['perfil'] === 'admin') {
            header("Location: ../admin/");
        } else {
            header("Location: ../dashboard.php");
        }
        exit;

    } else {

        // Login inválido
        header("Location: ../login.php?erro=1");
        exit;
    }
}

header("Location: ../login.php");
exit;
