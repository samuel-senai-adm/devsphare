<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/conexao.php";
require_once "../model/Usuario.php";

$usuario = new Usuario($conn);


/* CADASTRO */

/* CADASTRO */

if(isset($_POST['cadastrar'])){

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $cpf = $_POST['cpf'];

    $usuario->cadastrar(
        $nome,
        $email,
        $senha,
        $cpf
    );

    header("Location: ../login.php?sucesso=1");
}


/* LOGIN */

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $dadosUsuario = $usuario->login($email, $senha);

    if($dadosUsuario){

        $_SESSION['usuario_id'] = $dadosUsuario['id'];
        $_SESSION['usuario_nome'] = $dadosUsuario['nome'];
        $_SESSION['usuario_email'] = $dadosUsuario['email'];
        $_SESSION['perfil_id'] = $dadosUsuario['perfil'];

        header("Location: ../dashboard.php");
    } else {

        header("Location: ../login.php?erro=1");
    }
}
?>  