<?php
require_once __DIR__ . '/config/sessao.php';
iniciarSessao();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>SmartLocker - Cadastro</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23e74c3c' d='M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z'/%3E%3C/svg%3E">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="login.css">

</head>

<body>

    <div class="container">

        <div class="logo">

            <i class="fas fa-lock"></i>

            <h1>SmartLocker</h1>

            <p>Sistema inteligente de armários</p>

        </div>


        <!-- MENSAGEM DE ERRO -->

        <?php if (isset($_GET['erro'])): ?>

            <div class="erro">

                <?php if ($_GET['erro'] == 'email'): ?>

                    Este e-mail já está cadastrado.

                <?php else: ?>

                    Erro ao realizar cadastro.

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <!-- FORMULÁRIO DE CADASTRO -->

        <form class="form-box"
            action="controller/UsuarioController.php"
            method="POST">

            <h2>Cadastrar</h2>


            <div class="input-group">

                <i class="fas fa-user"></i>

                <input type="text"
                    name="nome"
                    placeholder="Digite seu nome"
                    required>

            </div>


            <div class="input-group">

                <i class="fas fa-envelope"></i>

                <input type="email"
                    name="email"
                    placeholder="Digite seu email"
                    required>

            </div>


            <div class="input-group">

                <i class="fas fa-lock"></i>

                <input type="password"
                    name="senha"
                    placeholder="Crie uma senha"
                    required>

            </div>


            <div class="input-group">

                <i class="fas fa-id-card"></i>

                <input type="text"
                    name="cpf"
                    placeholder="Digite seu CPF"
                    required>

            </div>


            <button type="submit"
                name="cadastrar"
                class="btn">

                Criar Conta

            </button>

        </form>


        <div class="toggle">

            <a href="login.php">
                Já possui conta? Fazer login
            </a>

        </div>

    </div>

</body>

</html>
