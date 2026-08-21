<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario_nome'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SmartLocker - Dashboard</title>

    <link rel="stylesheet" href="dashboard.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="icon"
        type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23e74c3c' d='M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z'/%3E%3C/svg%3E">
</head>

<body>

    <!-- MENU LATERAL -->
    <aside class="sidebar">

        <div class="logo">
            <i class="fas fa-box"></i>
            <h2>SmartLocker</h2>
        </div>

        <nav>

            <a href="#" class="active" onclick="mostrarSecao('dashboard', this)">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>

            <a href="#" onclick="mostrarSecao('projetos', this)">
                <i class="fas fa-diagram-project"></i>
                Projetos
            </a>

            <a href="#" onclick="mostrarSecao('kanban', this)">
                <i class="fas fa-list-check"></i>
                Kanban
            </a>

            <a href="#" onclick="mostrarSecao('equipe', this)">
                <i class="fas fa-users"></i>
                Equipe
            </a>

            <a href="#" onclick="mostrarSecao('rfid', this)">
                <i class="fas fa-microchip"></i>
                RFID
            </a>

            <a href="#" onclick="mostrarSecao('clientes', this)">
                <i class="fas fa-user-tie"></i>
                Clientes
            </a>

            <a href="#" onclick="mostrarSecao('relatorios', this)">
                <i class="fas fa-file-lines"></i>
                Relatórios
            </a>

        </nav>

        <a href="logout.php" class="logout">
            <i class="fas fa-right-from-bracket"></i>
            Sair
        </a>

    </aside>


    <!-- CONTEÚDO PRINCIPAL -->
    <main class="main">

        <!-- DASHBOARD -->
        <section id="dashboard" class="secao">

            <header>

                <div>
                    <h1>
                        Bem-vindo, <?= htmlspecialchars($usuario) ?>
                    </h1>

                    <p>
                        Painel de gerenciamento SmartLocker
                    </p>
                </div>

                <div class="perfil"
                    onclick="mostrarPerfil()"
                    title="Meu perfil">

                    <i class="fas fa-user-circle"></i>

                </div>

            </header>


            <!-- CARDS -->
            <section class="cards">

                <div class="card"
                    onclick="mostrarSecao('tarefas')"
                    style="cursor: pointer;">

                    <i class="fas fa-list"></i>

                    <h3>32</h3>

                    <p>Tarefas</p>

                </div>


                <div class="card"
                    onclick="mostrarSecao('andamento')"
                    style="cursor: pointer;">

                    <i class="fas fa-spinner"></i>

                    <h3>8</h3>

                    <p>Em andamento</p>

                </div>


                <div class="card"
                    onclick="mostrarSecao('concluidas')"
                    style="cursor: pointer;">

                    <i class="fas fa-check-circle"></i>

                    <h3>24</h3>

                    <p>Concluídas</p>

                </div>


                <div class="card"
                    onclick="mostrarSecao('equipe')"
                    style="cursor: pointer;">

                    <i class="fas fa-users"></i>

                    <h3>4</h3>

                    <p>Membros</p>

                </div>

            </section>


            <!-- KANBAN -->
            <section class="kanban">

                <div class="column">

                    <h2>Ideias</h2>

                    <div class="task"
                        onclick="abrirTarefa('Definir RFID')"
                        style="cursor: pointer;">

                        Definir RFID

                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Planejar app mobile')"
                        style="cursor: pointer;">

                        Planejar app mobile

                    </div>

                </div>


                <div class="column">

                    <h2>Pesquisa</h2>

                    <div class="task"
                        onclick="abrirTarefa('Estudo ESP32')"
                        style="cursor: pointer;">

                        Estudo ESP32

                    </div>

                </div>


                <div class="column">

                    <h2>Desenvolvimento</h2>

                    <div class="task"
                        onclick="abrirTarefa('Sistema de login')"
                        style="cursor: pointer;">

                        Sistema de login

                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Dashboard')"
                        style="cursor: pointer;">

                        Dashboard

                    </div>

                </div>


                <div class="column">

                    <h2>Concluído</h2>

                    <div class="task"
                        onclick="abrirTarefa('Levantamento de requisitos')"
                        style="cursor: pointer;">

                        Levantamento de requisitos

                    </div>

                </div>

            </section>

        </section>


        <!-- PROJETOS -->
        <section id="projetos" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Projetos</h1>
                    <p>Gerenciamento dos projetos SmartLocker</p>
                </div>
            </header>

            <div class="cards">

                <div class="card">
                    <i class="fas fa-box"></i>
                    <h3>SmartLocker</h3>
                    <p>Armário inteligente</p>
                </div>

                <div class="card">
                    <i class="fas fa-mobile-screen"></i>
                    <h3>App Mobile</h3>
                    <p>Aplicativo do sistema</p>
                </div>

            </div>

        </section>


        <!-- KANBAN -->
        <section id="kanban" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Kanban</h1>
                    <p>Organização das tarefas do projeto</p>
                </div>
            </header>

            <section class="kanban">

                <div class="column">
                    <h2>Ideias</h2>

                    <div class="task"
                        onclick="abrirTarefa('Definir RFID')"
                        style="cursor:pointer;">
                        Definir RFID
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Planejar app mobile')"
                        style="cursor:pointer;">
                        Planejar app mobile
                    </div>
                </div>


                <div class="column">
                    <h2>Pesquisa</h2>

                    <div class="task"
                        onclick="abrirTarefa('Estudo ESP32')"
                        style="cursor:pointer;">
                        Estudo ESP32
                    </div>
                </div>


                <div class="column">
                    <h2>Desenvolvimento</h2>

                    <div class="task"
                        onclick="abrirTarefa('Sistema de login')"
                        style="cursor:pointer;">
                        Sistema de login
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Dashboard')"
                        style="cursor:pointer;">
                        Dashboard
                    </div>
                </div>


                <div class="column">
                    <h2>Concluído</h2>

                    <div class="task"
                        onclick="abrirTarefa('Levantamento de requisitos')"
                        style="cursor:pointer;">
                        Levantamento de requisitos
                    </div>
                </div>

            </section>

        </section>


        <!-- EQUIPE -->
        <section id="equipe" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Equipe</h1>
                    <p>Membros da equipe SmartLocker</p>
                </div>
            </header>

            <div class="cards">

                <div class="card">
                    <i class="fas fa-user"></i>
                    <h3>Membro 1</h3>
                    <p>Desenvolvedor</p>
                </div>

                <div class="card">
                    <i class="fas fa-user"></i>
                    <h3>Membro 2</h3>
                    <p>Designer</p>
                </div>

                <div class="card">
                    <i class="fas fa-user"></i>
                    <h3>Membro 3</h3>
                    <p>Analista</p>
                </div>

                <div class="card">
                    <i class="fas fa-user"></i>
                    <h3>Membro 4</h3>
                    <p>Desenvolvedor</p>
                </div>

            </div>

        </section>


        <!-- RFID -->
        <section id="rfid" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>RFID</h1>
                    <p>Controle dos dispositivos RFID</p>
                </div>
            </header>

            <div class="cards">

                <div class="card">
                    <i class="fas fa-microchip"></i>
                    <h3>RFID</h3>
                    <p>Sistema ativo</p>
                </div>

                <div class="card">
                    <i class="fas fa-lock"></i>
                    <h3>Armários</h3>
                    <p>Controle inteligente</p>
                </div>

            </div>

        </section>


        <!-- CLIENTES -->
        <section id="clientes" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Clientes</h1>
                    <p>Gerenciamento de clientes</p>
                </div>
            </header>

            <div class="cards">

                <div class="card">
                    <i class="fas fa-user-tie"></i>
                    <h3>Clientes</h3>
                    <p>Visualizar clientes cadastrados</p>
                </div>

            </div>

        </section>


        <!-- RELATÓRIOS -->
        <section id="relatorios" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Relatórios</h1>
                    <p>Informações do sistema</p>
                </div>
            </header>

            <div class="cards">

                <div class="card">
                    <i class="fas fa-chart-pie"></i>
                    <h3>32</h3>
                    <p>Total de tarefas</p>
                </div>

                <div class="card">
                    <i class="fas fa-check"></i>
                    <h3>24</h3>
                    <p>Tarefas concluídas</p>
                </div>

                <div class="card">
                    <i class="fas fa-spinner"></i>
                    <h3>8</h3>
                    <p>Em andamento</p>
                </div>

            </div>

        </section>


        <!-- TAREFAS -->
        <section id="tarefas" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Tarefas</h1>
                    <p>Todas as tarefas do projeto</p>
                </div>
            </header>

            <div class="kanban">

                <div class="column">

                    <h2>Lista de tarefas</h2>

                    <div class="task"
                        onclick="abrirTarefa('Definir RFID')"
                        style="cursor:pointer;">
                        Definir RFID
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Planejar app mobile')"
                        style="cursor:pointer;">
                        Planejar app mobile
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Estudo ESP32')"
                        style="cursor:pointer;">
                        Estudo ESP32
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Sistema de login')"
                        style="cursor:pointer;">
                        Sistema de login
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Dashboard')"
                        style="cursor:pointer;">
                        Dashboard
                    </div>

                </div>

            </div>

        </section>


        <!-- EM ANDAMENTO -->
        <section id="andamento" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Em andamento</h1>
                    <p>Tarefas que estão sendo desenvolvidas</p>
                </div>
            </header>

            <div class="kanban">

                <div class="column">

                    <h2>8 tarefas</h2>

                    <div class="task"
                        onclick="abrirTarefa('Sistema de login')"
                        style="cursor:pointer;">
                        Sistema de login
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Dashboard')"
                        style="cursor:pointer;">
                        Dashboard
                    </div>

                    <div class="task"
                        onclick="abrirTarefa('Estudo ESP32')"
                        style="cursor:pointer;">
                        Estudo ESP32
                    </div>

                </div>

            </div>

        </section>


        <!-- CONCLUÍDAS -->
        <section id="concluidas" class="secao" style="display:none;">

            <header>
                <div>
                    <h1>Concluídas</h1>
                    <p>Tarefas finalizadas</p>
                </div>
            </header>

            <div class="kanban">

                <div class="column">

                    <h2>24 tarefas concluídas</h2>

                    <div class="task">
                        <i class="fas fa-check"></i>
                        Levantamento de requisitos
                    </div>

                </div>

            </div>

        </section>

    </main>


    <!-- JAVASCRIPT -->
    <script>

        function mostrarSecao(secao, elemento = null) {

            // Esconde todas as seções
            const secoes = document.querySelectorAll(".secao");

            secoes.forEach(function (item) {
                item.style.display = "none";
            });


            // Mostra a seção escolhida
            const escolhida = document.getElementById(secao);

            if (escolhida) {
                escolhida.style.display = "block";
            }


            // Remove o active de todos os menus
            const links = document.querySelectorAll(".sidebar nav a");

            links.forEach(function (link) {
                link.classList.remove("active");
            });


            // Coloca active no botão clicado
            if (elemento) {
                elemento.classList.add("active");
            }

        }


        function abrirTarefa(nome) {

            alert(
                "Tarefa selecionada:\n\n" +
                nome +
                "\n\nAqui você poderá colocar os detalhes da tarefa."
            );

        }


        function mostrarPerfil() {

            alert(
                "Usuário conectado:\n\n" +
                "<?= htmlspecialchars($usuario) ?>"
            );

        }

    </script>

</body>

</html>