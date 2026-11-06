<?php

require_once __DIR__ . '/../config/sessao.php';
iniciarSessao();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/conexao.php";
require_once "../model/Usuario.php";

$usuario = new Usuario($conn);


/* =========================
   CADASTRO
   ========================= */

if (isset($_POST['cadastrar'])) {

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $cpf = $_POST['cpf'];


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
   ========================= */

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $dadosUsuario = $usuario->login($email, $senha);

    if ($dadosUsuario) {

        // Novo ID de sessão a cada login (evita sequestro de sessão)
        session_regenerate_id(true);

        // Dados do usuário salvos na sessão
        $_SESSION['usuario_id'] = $dadosUsuario['id'];
        $_SESSION['usuario_nome'] = $dadosUsuario['nome'];
        $_SESSION['usuario_email'] = $dadosUsuario['email'];
        $_SESSION['perfil_id'] = $dadosUsuario['perfil'];

        // Vai para o dashboard
        header("Location: ../dashboard.php");
        exit;

    } else {

        // Login inválido
        header("Location: ../login.php?erro=1");
        exit;
    }
}

?>

