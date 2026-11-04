<?php
session_start();

/*
 * ATENÇÃO: verificação de login ainda desativada (mantida como estava).
 * Será corrigida na etapa de segurança, usando $_SESSION['usuario_id'].
 *
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
*/

$usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario = trim($usuario) !== '' ? trim($usuario) : 'Usuário';
$primeiroNome = explode(' ', $usuario)[0];
$inicial = mb_strtoupper(mb_substr($usuario, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14161b">
    <title>SmartLocker - Área do Cliente</title>

    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23e11d2e' d='M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

<a class="pular" href="#conteudo">Pular para o conteúdo</a>

<!-- ================= MENU LATERAL ================= -->
<aside class="sidebar" id="sidebar" aria-label="Menu principal">

    <div class="logo">
        <div class="logo-marca" aria-hidden="true"><i class="fas fa-lock"></i></div>
        <div class="logo-texto">
            <strong>SmartLocker</strong>
            <span>Área do cliente</span>
        </div>
        <button class="btn-icone sidebar-fechar" id="btnFecharMenu" type="button" aria-label="Fechar menu">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    <nav aria-label="Seções">
        <p class="nav-titulo">Geral</p>

        <a href="#inicio" class="nav-link" data-view="inicio">
            <i class="fas fa-house" aria-hidden="true"></i><span>Início</span>
        </a>
        <a href="#armarios" class="nav-link" data-view="armarios">
            <i class="fas fa-table-cells-large" aria-hidden="true"></i><span>Armários</span>
        </a>

        <p class="nav-titulo">Minha conta</p>

        <a href="#reserva" class="nav-link" data-view="reserva">
            <i class="fas fa-calendar-check" aria-hidden="true"></i><span>Minha reserva</span>
            <span class="nav-badge" id="badgeReserva" aria-label="1 reserva ativa">1</span>
        </a>
        <a href="#historico" class="nav-link" data-view="historico">
            <i class="fas fa-clock-rotate-left" aria-hidden="true"></i><span>Histórico</span>
        </a>
        <a href="#qrcode" class="nav-link" data-view="qrcode">
            <i class="fas fa-qrcode" aria-hidden="true"></i><span>Meu QR Code</span>
        </a>
        <a href="#perfil" class="nav-link" data-view="perfil">
            <i class="fas fa-user" aria-hidden="true"></i><span>Meu perfil</span>
        </a>
    </nav>

    <div class="sidebar-rodape">
        <div class="sidebar-status" id="sidebarStatus">
            <span class="led" aria-hidden="true"></span>
            <div>
                <strong id="sidebarStatusTitulo">Sem reserva ativa</strong>
                <small id="sidebarStatusTexto">Escolha um armário disponível</small>
            </div>
        </div>

        <a href="logout.php" class="logout">
            <i class="fas fa-right-from-bracket" aria-hidden="true"></i><span>Sair</span>
        </a>
    </div>
</aside>

<div class="overlay" id="overlay" aria-hidden="true"></div>

<!-- ================= CONTEÚDO ================= -->
<div class="area">

    <header class="topbar">
        <button class="btn-icone btn-menu" id="btnMenu" type="button" aria-label="Abrir menu"
            aria-controls="sidebar" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-titulo">
            <small id="dataTopo"></small>
            <strong id="tituloTopo">Início</strong>
        </div>

        <button class="btn btn-primario btn-topo" id="btnTopoAcao" type="button">
            <i class="fas fa-calendar-plus" aria-hidden="true"></i><span>Reservar</span>
        </button>

        <div class="usuario">
            <button class="usuario-chip" id="btnUsuario" type="button"
                aria-haspopup="menu" aria-expanded="false" aria-controls="menuUsuario">
                <span class="avatar" aria-hidden="true"><?= htmlspecialchars($inicial) ?></span>
                <span class="usuario-texto">
                    <strong><?= htmlspecialchars($usuario) ?></strong>
                    <small>Cliente</small>
                </span>
                <i class="fas fa-chevron-down seta" aria-hidden="true"></i>
            </button>

            <div class="menu-usuario" id="menuUsuario" role="menu" hidden>
                <div class="menu-usuario-cabecalho">
                    <strong><?= htmlspecialchars($usuario) ?></strong>
                    <small>Conta de cliente</small>
                </div>
                <button type="button" role="menuitem" data-ir="perfil">
                    <i class="fas fa-user" aria-hidden="true"></i> Meu perfil
                </button>
                <button type="button" role="menuitem" data-ir="reserva">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i> Minha reserva
                </button>
                <a href="logout.php" role="menuitem" class="sair">
                    <i class="fas fa-right-from-bracket" aria-hidden="true"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <main class="main" id="conteudo" tabindex="-1">

        <!-- ============ INÍCIO ============ -->
        <section class="view" id="inicio" aria-labelledby="tituloInicio">

            <div class="hero">
                <div class="hero-texto">
                    <span class="hero-rotulo" id="heroRotulo">
                        <span class="led" aria-hidden="true"></span> Nenhuma reserva ativa
                    </span>
                    <h1 id="tituloInicio">
                        <span id="saudacao">Olá</span>, <?= htmlspecialchars($primeiroNome) ?>
                    </h1>
                    <p id="heroTexto">Escolha um armário disponível e reserve em poucos segundos.</p>

                    <div class="hero-acoes">
                        <button class="btn btn-claro" id="heroBotao" type="button">
                            <i class="fas fa-calendar-plus" aria-hidden="true"></i> Reservar armário
                        </button>
                        <button class="btn btn-fantasma" type="button" data-ir="historico">
                            <i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Ver histórico
                        </button>
                    </div>
                </div>

                <div class="hero-banco" aria-hidden="true">
                    <div class="banco" id="heroBanco"></div>
                    <small class="banco-legenda">Mapa dos armários</small>
                </div>
            </div>

            <div class="stats">
                <article class="stat">
                    <div class="stat-icone verde"><i class="fas fa-lock-open" aria-hidden="true"></i></div>
                    <div>
                        <p>Armários disponíveis</p>
                        <h3 id="contadorDisponiveis">0</h3>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone vermelho"><i class="fas fa-calendar-check" aria-hidden="true"></i></div>
                    <div>
                        <p>Reserva ativa</p>
                        <h3 id="contadorReserva">0</h3>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone cinza"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></div>
                    <div>
                        <p>Reservas no histórico</p>
                        <h3 id="contadorHistorico">0</h3>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone escuro"><i class="fas fa-qrcode" aria-hidden="true"></i></div>
                    <div>
                        <p>QR Code ativo</p>
                        <h3 id="contadorQr">0</h3>
                    </div>
                </article>
            </div>

            <div class="grade-inicio">
                <section class="painel" aria-labelledby="tituloArmariosInicio">
                    <div class="painel-topo">
                        <div>
                            <h2 id="tituloArmariosInicio">Armários</h2>
                            <p>Situação atual de cada compartimento</p>
                        </div>
                        <button class="btn-link" type="button" data-ir="armarios">
                            Ver todos <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="grade-armarios" id="gradeResumo"></div>
                </section>

                <div class="coluna-lateral">
                    <section class="painel" aria-labelledby="tituloOcupacao">
                        <div class="painel-topo">
                            <div>
                                <h2 id="tituloOcupacao">Ocupação</h2>
                                <p>Distribuição dos armários</p>
                            </div>
                        </div>
                        <div class="ocupacao">
                            <div class="rosca" id="rosca" role="img" aria-label="Ocupação dos armários">
                                <div class="rosca-centro">
                                    <strong id="roscaPercentual">0%</strong>
                                    <small>ocupados</small>
                                </div>
                            </div>
                            <ul class="rosca-legenda" id="roscaLegenda"></ul>
                        </div>
                    </section>

                    <section class="painel" aria-labelledby="tituloAtividade">
                        <div class="painel-topo">
                            <div>
                                <h2 id="tituloAtividade">Atividade recente</h2>
                                <p>Suas últimas reservas</p>
                            </div>
                        </div>
                        <ul class="atividade" id="listaAtividade"></ul>
                    </section>
                </div>
            </div>
        </section>


        <!-- ============ ARMÁRIOS ============ -->
        <section class="view" id="armarios" aria-labelledby="tituloArmarios">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloArmarios">Armários</h1>
                    <p>Escolha um armário disponível para realizar sua reserva.</p>
                </div>
            </div>

            <div class="aviso" id="avisoReserva" hidden>
                <i class="fas fa-circle-info" aria-hidden="true"></i>
                <span>Você já possui uma reserva ativa. Cancele-a para reservar outro armário.</span>
                <button class="btn-link" type="button" data-ir="reserva">Ver reserva</button>
            </div>

            <section class="painel" aria-label="Lista de armários">
                <div class="barra-ferramentas">
                    <div class="busca">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <label class="sr-only" for="buscaArmario">Buscar armário</label>
                        <input type="search" id="buscaArmario" placeholder="Buscar por número ou bloco" autocomplete="off">
                    </div>

                    <div class="segmentos" role="group" aria-label="Filtrar por situação" id="filtrosArmario">
                        <button type="button" class="segmento ativo" data-status="todos" aria-pressed="true">
                            Todos <span data-conta="todos">0</span>
                        </button>
                        <button type="button" class="segmento" data-status="livre" aria-pressed="false">
                            Disponíveis <span data-conta="livre">0</span>
                        </button>
                        <button type="button" class="segmento" data-status="ocupado" aria-pressed="false">
                            Ocupados <span data-conta="ocupado">0</span>
                        </button>
                    </div>
                </div>

                <div class="legenda">
                    <span><i class="ponto livre"></i> Disponível</span>
                    <span><i class="ponto minha"></i> Minha reserva</span>
                    <span><i class="ponto ocupado"></i> Ocupado</span>
                </div>

                <div class="grade-armarios" id="gradeCompleta" aria-live="polite"></div>
            </section>
        </section>


        <!-- ============ MINHA RESERVA ============ -->
        <section class="view" id="reserva" aria-labelledby="tituloReserva">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloReserva">Minha reserva</h1>
                    <p>Consulte e gerencie sua reserva atual.</p>
                </div>
            </div>

            <div class="painel vazio" id="semReserva">
                <div class="vazio-icone"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></div>
                <h2>Nenhuma reserva ativa</h2>
                <p>Você ainda não possui uma reserva. Escolha um armário para começar.</p>
                <button class="btn btn-primario" type="button" data-ir="armarios">
                    <i class="fas fa-calendar-plus" aria-hidden="true"></i> Escolher armário
                </button>
            </div>

            <div class="ticket" id="reservaAtiva" hidden>
                <div class="ticket-principal">
                    <div class="ticket-cabecalho">
                        <div class="ticket-numero" id="reservaNumero">00</div>
                        <div>
                            <h2 id="reservaTitulo">Armário</h2>
                            <p><i class="fas fa-location-dot" aria-hidden="true"></i> <span id="reservaLocal">Bloco</span></p>
                        </div>
                        <span class="badge ativa" id="reservaBadge">Ativa</span>
                    </div>

                    <div class="reserva-dados">
                        <div class="dado">
                            <span><i class="fas fa-calendar" aria-hidden="true"></i> Data</span>
                            <strong id="reservaData">-</strong>
                        </div>
                        <div class="dado">
                            <span><i class="fas fa-clock" aria-hidden="true"></i> Horário</span>
                            <strong id="reservaHorario">-</strong>
                        </div>
                        <div class="dado">
                            <span><i class="fas fa-hourglass-half" aria-hidden="true"></i> Duração</span>
                            <strong id="reservaDuracao">-</strong>
                        </div>
                    </div>

                    <div class="linha-tempo">
                        <div class="linha-tempo-topo">
                            <span id="reservaContagem">-</span>
                            <strong id="reservaPercentual">0%</strong>
                        </div>
                        <div class="progresso" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                            aria-valuenow="0" aria-labelledby="reservaContagem" id="reservaProgresso">
                            <span id="reservaBarra"></span>
                        </div>
                        <div class="linha-tempo-rodape">
                            <small id="reservaInicio">--:--</small>
                            <small id="reservaFim">--:--</small>
                        </div>
                    </div>
                </div>

                <div class="ticket-lateral">
                    <button class="btn btn-escuro btn-bloco" type="button" data-ir="qrcode">
                        <i class="fas fa-qrcode" aria-hidden="true"></i> Ver QR Code
                    </button>
                    <button class="btn btn-perigo btn-bloco" type="button" id="btnCancelar">
                        <i class="fas fa-calendar-xmark" aria-hidden="true"></i> Cancelar reserva
                    </button>
                    <small>Reserva feita em <span id="reservaCriada">-</span></small>
                </div>
            </div>
        </section>


        <!-- ============ HISTÓRICO ============ -->
        <section class="view" id="historico" aria-labelledby="tituloHistorico">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloHistorico">Histórico</h1>
                    <p>Consulte suas reservas anteriores.</p>
                </div>
            </div>

            <section class="painel" aria-label="Lista de reservas">
                <div class="barra-ferramentas">
                    <div class="segmentos" role="group" aria-label="Filtrar reservas" id="filtrosHistorico">
                        <button class="segmento ativo" type="button" data-filtro="todas" aria-pressed="true">
                            Todas <span data-conta-h="todas">0</span>
                        </button>
                        <button class="segmento" type="button" data-filtro="Ativa" aria-pressed="false">
                            Ativas <span data-conta-h="Ativa">0</span>
                        </button>
                        <button class="segmento" type="button" data-filtro="Concluída" aria-pressed="false">
                            Concluídas <span data-conta-h="Concluída">0</span>
                        </button>
                        <button class="segmento" type="button" data-filtro="Cancelada" aria-pressed="false">
                            Canceladas <span data-conta-h="Cancelada">0</span>
                        </button>
                    </div>
                </div>

                <div class="lista-historico" id="listaHistorico" aria-live="polite"></div>
            </section>
        </section>


        <!-- ============ QR CODE ============ -->
        <section class="view" id="qrcode" aria-labelledby="tituloQr">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloQr">Meu QR Code</h1>
                    <p>Código de identificação vinculado à sua reserva.</p>
                </div>
            </div>

            <div class="painel qr-painel">

                <div class="vazio" id="qrcodeSemReserva">
                    <div class="vazio-icone"><i class="fas fa-qrcode" aria-hidden="true"></i></div>
                    <h2>Nenhum QR Code ativo</h2>
                    <p>Faça uma reserva para gerar o seu QR Code.</p>
                    <button class="btn btn-primario" type="button" data-ir="armarios">
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i> Escolher armário
                    </button>
                </div>

                <div class="qr-ativo" id="qrcodeAtivo" hidden>
                    <div class="qr-scanner">
                        <span class="canto c1"></span><span class="canto c2"></span>
                        <span class="canto c3"></span><span class="canto c4"></span>
                        <div class="qr-moldura" id="qrMoldura" role="img" aria-label="QR Code da reserva"></div>
                    </div>

                    <div class="qr-detalhes">
                        <h2 id="qrArmario">Armário</h2>
                        <p id="qrQuando">-</p>
                        <div class="aviso aviso-neutro">
                            <i class="fas fa-microchip" aria-hidden="true"></i>
                            <span>A leitura no armário (QR Code ou RFID) ainda está em fase de integração.
                                Este código identifica sua reserva no sistema.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- ============ PERFIL ============ -->
        <section class="view" id="perfil" aria-labelledby="tituloPerfil">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloPerfil">Meu perfil</h1>
                    <p>Informações da sua conta.</p>
                </div>
            </div>

            <div class="painel">
                <div class="perfil-topo">
                    <div class="avatar grande" aria-hidden="true"><?= htmlspecialchars($inicial) ?></div>
                    <div>
                        <h2><?= htmlspecialchars($usuario) ?></h2>
                        <p>Cliente SmartLocker</p>
                    </div>
                </div>

                <div class="perfil-grade">
                    <div class="dado">
                        <span><i class="fas fa-user" aria-hidden="true"></i> Nome do cliente</span>
                        <strong><?= htmlspecialchars($usuario) ?></strong>
                    </div>
                    <div class="dado">
                        <span><i class="fas fa-user-tag" aria-hidden="true"></i> Tipo de usuário</span>
                        <strong>Cliente</strong>
                    </div>
                    <div class="dado">
                        <span><i class="fas fa-circle-check" aria-hidden="true"></i> Status da conta</span>
                        <strong class="texto-verde">Ativo</strong>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>


<!-- ================= MODAL: RESERVA ================= -->
<div class="modal" id="modalReserva" role="dialog" aria-modal="true" aria-labelledby="tituloModalReserva" hidden>
    <div class="modal-conteudo">

        <button class="btn-icone modal-fechar" type="button" data-fechar="modalReserva" aria-label="Fechar">
            <i class="fas fa-xmark"></i>
        </button>

        <h2 id="tituloModalReserva">Fazer reserva</h2>
        <p class="modal-sub">Escolha a data e o horário de uso.</p>

        <div class="modal-selecionado">
            <div class="ticket-numero pequeno" id="modalNumero">00</div>
            <div>
                <strong id="modalArmario">Armário</strong>
                <small><i class="fas fa-location-dot" aria-hidden="true"></i> <span id="modalLocal">Bloco</span></small>
            </div>
            <span class="badge livre">Disponível</span>
        </div>

        <div class="form-reserva">
            <div class="campo campo-full">
                <label for="dataReserva">Data da reserva</label>
                <input type="date" id="dataReserva">
            </div>
            <div class="campo">
                <label for="horaInicio">Início</label>
                <input type="time" id="horaInicio">
            </div>
            <div class="campo">
                <label for="horaFim">Término</label>
                <input type="time" id="horaFim">
            </div>
            <div class="campo campo-full">
                <span class="rotulo" id="rotuloDuracao">Duração rápida</span>
                <div class="duracoes" role="group" aria-labelledby="rotuloDuracao">
                    <button class="chip" type="button" data-duracao="60">1 hora</button>
                    <button class="chip" type="button" data-duracao="120">2 horas</button>
                    <button class="chip" type="button" data-duracao="240">4 horas</button>
                </div>
            </div>
        </div>

        <div class="resumo-reserva" id="resumoReserva" hidden>
            <i class="fas fa-hourglass-half" aria-hidden="true"></i>
            <span id="resumoTexto"></span>
        </div>

        <div class="mensagem" id="mensagemReserva" role="alert"></div>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalReserva">Cancelar</button>
            <button class="btn btn-primario" type="button" id="btnConfirmar">
                <i class="fas fa-check" aria-hidden="true"></i> Confirmar reserva
            </button>
        </div>
    </div>
</div>


<!-- ================= MODAL: CONFIRMAR CANCELAMENTO ================= -->
<div class="modal" id="modalCancelar" role="alertdialog" aria-modal="true"
    aria-labelledby="tituloCancelar" aria-describedby="textoCancelar" hidden>
    <div class="modal-conteudo pequeno">
        <div class="modal-icone"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></div>
        <h2 id="tituloCancelar">Cancelar reserva?</h2>
        <p class="modal-sub" id="textoCancelar"></p>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalCancelar">Manter reserva</button>
            <button class="btn btn-perigo-cheio" type="button" id="btnConfirmarCancelar">Sim, cancelar</button>
        </div>
    </div>
</div>

<div class="toasts" id="toasts" aria-live="polite" aria-atomic="false"></div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    /* =====================================================
       CONFIGURAÇÃO
       Os dados ainda ficam no localStorage (como antes).
       A troca para o banco MySQL será feita na próxima etapa.
    ===================================================== */

    var CHAVE_RESERVA   = 'smartlocker_reserva';
    var CHAVE_HISTORICO = 'smartlocker_historico';

    var ARMARIOS = [
        { id: '01', nome: 'Armário 01', local: 'Bloco A', ocupado: false },
        { id: '02', nome: 'Armário 02', local: 'Bloco A', ocupado: true  },
        { id: '03', nome: 'Armário 03', local: 'Bloco A', ocupado: false },
        { id: '04', nome: 'Armário 04', local: 'Bloco B', ocupado: false }
    ];

    var TITULOS = {
        inicio: 'Início',
        armarios: 'Armários',
        reserva: 'Minha reserva',
        historico: 'Histórico',
        qrcode: 'Meu QR Code',
        perfil: 'Meu perfil'
    };

    var ROTULOS = { livre: 'Disponível', ocupado: 'Ocupado', minha: 'Minha reserva' };

    var estado = {
        armarioSelecionado: null,
        filtroHistorico: 'todas',
        filtroArmario: 'todos',
        busca: '',
        view: 'inicio'
    };

    /* =====================================================
       UTILITÁRIOS
    ===================================================== */

    function $(id) { return document.getElementById(id); }

    function lerJSON(chave, padrao) {
        try {
            var bruto = localStorage.getItem(chave);
            if (!bruto) { return padrao; }
            var valor = JSON.parse(bruto);
            return valor === null ? padrao : valor;
        } catch (e) {
            return padrao;
        }
    }

    function salvarJSON(chave, valor) {
        try { localStorage.setItem(chave, JSON.stringify(valor)); } catch (e) {}
    }

    function removerChave(chave) {
        try { localStorage.removeItem(chave); } catch (e) {}
    }

    function esc(texto) {
        var div = document.createElement('div');
        div.textContent = String(texto === undefined || texto === null ? '' : texto);
        return div.innerHTML;
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function dataAtualISO() {
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function formatarData(data) {
        var p = String(data).split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : data;
    }

    function formatarDataHora(iso) {
        var d = new Date(iso);
        if (isNaN(d)) { return '-'; }
        return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + ' às ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function paraDate(data, hora) {
        var d = data.split('-').map(Number);
        var h = hora.split(':').map(Number);
        return new Date(d[0], d[1] - 1, d[2], h[0], h[1], 0, 0);
    }

    function minutosDe(hora) {
        var h = hora.split(':').map(Number);
        return h[0] * 60 + h[1];
    }

    function textoDuracao(inicio, fim) {
        var total = minutosDe(fim) - minutosDe(inicio);
        if (total <= 0) { return '-'; }
        var h = Math.floor(total / 60);
        var m = total % 60;
        if (h && m) { return h + 'h ' + m + 'min'; }
        if (h) { return h + (h === 1 ? ' hora' : ' horas'); }
        return m + ' min';
    }

    function textoTempo(ms) {
        var min = Math.max(0, Math.round(ms / 60000));
        var d = Math.floor(min / 1440);
        var h = Math.floor((min % 1440) / 60);
        var m = min % 60;
        var partes = [];
        if (d) { partes.push(d + (d === 1 ? ' dia' : ' dias')); }
        if (h) { partes.push(h + 'h'); }
        if (m || !partes.length) { partes.push(m + 'min'); }
        return partes.join(' ');
    }

    function numeroDe(nomeArmario) {
        var achado = ARMARIOS.filter(function (a) { return a.nome === nomeArmario; })[0];
        if (achado) { return achado.id; }
        var m = String(nomeArmario || '').match(/\d+/);
        return m ? pad(m[0]) : '--';
    }

    /* Só substitui o HTML quando algo mudou (evita perder foco e piscar). */
    function trocarHTML(el, html) {
        if (el.__html !== html) {
            el.innerHTML = html;
            el.__html = html;
        }
    }

    /* =====================================================
       DADOS (RESERVA ATIVA + HISTÓRICO)
    ===================================================== */

    function obterReserva() {
        var r = lerJSON(CHAVE_RESERVA, null);
        if (!r || typeof r !== 'object' || !r.data || !r.inicio || !r.fim) { return null; }
        return r;
    }

    function obterHistorico() {
        var h = lerJSON(CHAVE_HISTORICO, []);
        return Array.isArray(h) ? h : [];
    }

    function mesmaReserva(a, b) {
        if (a.criadaEm && b.criadaEm) { return a.criadaEm === b.criadaEm; }
        return a.armario === b.armario && a.data === b.data &&
               a.inicio === b.inicio && a.fim === b.fim;
    }

    function atualizarStatusNoHistorico(reserva, novoStatus) {
        var historico = obterHistorico().map(function (item) {
            if (item.status === 'Ativa' && mesmaReserva(item, reserva)) {
                item.status = novoStatus;
                if (novoStatus === 'Cancelada') { item.canceladaEm = new Date().toISOString(); }
                if (novoStatus === 'Concluída') { item.concluidaEm = new Date().toISOString(); }
            }
            return item;
        });
        salvarJSON(CHAVE_HISTORICO, historico);
    }

    /* Reserva cujo horário de término já passou vira "Concluída". */
    function verificarExpiracao() {
        var reserva = obterReserva();
        if (!reserva) { return false; }

        if (paraDate(reserva.data, reserva.fim) <= new Date()) {
            atualizarStatusNoHistorico(reserva, 'Concluída');
            removerChave(CHAVE_RESERVA);
            toast('Sua reserva do ' + reserva.armario + ' foi concluída.', 'info');
            return true;
        }
        return false;
    }

    function statusDoArmario(armario, reserva) {
        if (reserva && reserva.armario === armario.nome) { return 'minha'; }
        return armario.ocupado ? 'ocupado' : 'livre';
    }

    /* =====================================================
       NAVEGAÇÃO
    ===================================================== */

    var sidebar = $('sidebar');
    var overlay = $('overlay');
    var links = document.querySelectorAll('.nav-link');

    function abrirMenu() {
        sidebar.classList.add('aberta');
        overlay.classList.add('visivel');
        $('btnMenu').setAttribute('aria-expanded', 'true');
        $('btnFecharMenu').focus();
    }

    function fecharMenu() {
        if (!sidebar.classList.contains('aberta')) { return; }
        sidebar.classList.remove('aberta');
        overlay.classList.remove('visivel');
        $('btnMenu').setAttribute('aria-expanded', 'false');
    }

    function irPara(destino, atualizarHash) {
        if (!TITULOS[destino]) { destino = 'inicio'; }
        var mudou = estado.view !== destino;
        estado.view = destino;

        document.querySelectorAll('.view').forEach(function (v) {
            v.classList.toggle('ativa', v.id === destino);
        });

        links.forEach(function (l) {
            var ativo = l.getAttribute('data-view') === destino;
            l.classList.toggle('active', ativo);
            if (ativo) { l.setAttribute('aria-current', 'page'); }
            else { l.removeAttribute('aria-current'); }
        });

        $('tituloTopo').textContent = TITULOS[destino];
        document.title = TITULOS[destino] + ' · SmartLocker';

        if (atualizarHash !== false && location.hash !== '#' + destino) {
            history.replaceState(null, '', '#' + destino);
        }

        fecharMenu();
        fecharMenuUsuario();
        if (mudou) { window.scrollTo(0, 0); }
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            irPara(this.getAttribute('data-view'));
            $('conteudo').focus({ preventScroll: true });
        });
    });

    /* Qualquer botão com data-ir="secao" navega (inclusive os criados depois). */
    document.addEventListener('click', function (e) {
        var alvo = e.target.closest('[data-ir]');
        if (alvo) { irPara(alvo.getAttribute('data-ir')); }
    });

    $('btnMenu').addEventListener('click', abrirMenu);
    $('btnFecharMenu').addEventListener('click', fecharMenu);
    overlay.addEventListener('click', fecharMenu);

    window.addEventListener('hashchange', function () {
        irPara(location.hash.replace('#', ''), false);
    });

    /* =====================================================
       MENU DO USUÁRIO
    ===================================================== */

    var btnUsuario = $('btnUsuario');
    var menuUsuario = $('menuUsuario');

    function abrirMenuUsuario() {
        menuUsuario.hidden = false;
        btnUsuario.setAttribute('aria-expanded', 'true');
        var primeiro = menuUsuario.querySelector('[role="menuitem"]');
        if (primeiro) { primeiro.focus(); }
    }

    function fecharMenuUsuario(devolverFoco) {
        if (menuUsuario.hidden) { return; }
        menuUsuario.hidden = true;
        btnUsuario.setAttribute('aria-expanded', 'false');
        if (devolverFoco) { btnUsuario.focus(); }
    }

    btnUsuario.addEventListener('click', function (e) {
        e.stopPropagation();
        if (menuUsuario.hidden) { abrirMenuUsuario(); } else { fecharMenuUsuario(); }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.usuario')) { fecharMenuUsuario(); }
    });

    menuUsuario.addEventListener('keydown', function (e) {
        var itens = Array.prototype.slice.call(menuUsuario.querySelectorAll('[role="menuitem"]'));
        var i = itens.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); itens[(i + 1) % itens.length].focus(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); itens[(i - 1 + itens.length) % itens.length].focus(); }
        if (e.key === 'Tab')       { fecharMenuUsuario(); }
    });

    /* =====================================================
       TOASTS
    ===================================================== */

    function toast(texto, tipo) {
        tipo = tipo || 'info';
        var icones = {
            sucesso: 'fa-circle-check',
            erro: 'fa-circle-exclamation',
            info: 'fa-circle-info'
        };

        var el = document.createElement('div');
        el.className = 'toast ' + tipo;
        el.innerHTML = '<i class="fas ' + icones[tipo] + '" aria-hidden="true"></i><span>' + esc(texto) + '</span>';
        $('toasts').appendChild(el);

        setTimeout(function () {
            el.classList.add('saindo');
            setTimeout(function () { el.remove(); }, 320);
        }, 4000);
    }

    /* =====================================================
       MODAIS (com foco preso dentro do modal)
    ===================================================== */

    var focoAnterior = null;
    var SELETOR_FOCAVEL = 'button:not([disabled]), input:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])';

    function abrirModal(id, focarEm) {
        focoAnterior = document.activeElement;
        var modal = $(id);
        modal.hidden = false;
        void modal.offsetWidth; /* reinicia a animação */
        modal.classList.add('aberto');
        document.body.classList.add('travado');

        var alvo = focarEm ? $(focarEm) : modal.querySelector(SELETOR_FOCAVEL);
        if (alvo) { setTimeout(function () { alvo.focus(); }, 60); }
    }

    function fecharModal(id) {
        var modal = $(id);
        if (!modal.classList.contains('aberto')) { return; }
        modal.classList.remove('aberto');
        setTimeout(function () {
            if (!modal.classList.contains('aberto')) { modal.hidden = true; }
        }, 220);

        if (!document.querySelector('.modal.aberto')) {
            document.body.classList.remove('travado');
        }
        if (focoAnterior && document.contains(focoAnterior)) { focoAnterior.focus(); }
    }

    document.querySelectorAll('[data-fechar]').forEach(function (b) {
        b.addEventListener('click', function () { fecharModal(this.getAttribute('data-fechar')); });
    });

    document.querySelectorAll('.modal').forEach(function (m) {
        m.addEventListener('mousedown', function (e) {
            if (e.target === m) { fecharModal(m.id); }
        });

        m.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') { return; }
            var focaveis = m.querySelectorAll(SELETOR_FOCAVEL);
            if (!focaveis.length) { return; }
            var primeiro = focaveis[0];
            var ultimo = focaveis[focaveis.length - 1];
            if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
            else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        var aberto = document.querySelector('.modal.aberto');
        if (aberto) { fecharModal(aberto.id); return; }
        if (!menuUsuario.hidden) { fecharMenuUsuario(true); return; }
        fecharMenu();
    });

    /* =====================================================
       RESERVA - ABRIR / VALIDAR / CONFIRMAR
    ===================================================== */

    function abrirReserva(id) {
        if (obterReserva()) {
            toast('Você já possui uma reserva ativa.', 'erro');
            return;
        }

        var armario = ARMARIOS.filter(function (a) { return a.id === id; })[0];
        if (!armario || armario.ocupado) { return; }

        estado.armarioSelecionado = armario;

        $('modalArmario').textContent = armario.nome;
        $('modalLocal').textContent = armario.local;
        $('modalNumero').textContent = armario.id;

        var data = $('dataReserva');
        data.min = dataAtualISO();
        data.value = dataAtualISO();

        $('horaInicio').value = '';
        $('horaFim').value = '';
        limparMensagem();
        atualizarResumo();
        document.querySelectorAll('[data-duracao]').forEach(function (b) { b.classList.remove('ativo'); });

        $('btnConfirmar').disabled = false;

        abrirModal('modalReserva', 'horaInicio');
    }

    function limparMensagem() {
        var msg = $('mensagemReserva');
        msg.className = 'mensagem';
        msg.textContent = '';
    }

    function mostrarMensagem(texto, tipo) {
        var msg = $('mensagemReserva');
        msg.className = 'mensagem ' + tipo;
        msg.innerHTML = '<i class="fas ' +
            (tipo === 'erro' ? 'fa-circle-exclamation' : 'fa-circle-check') +
            '" aria-hidden="true"></i> ' + esc(texto);
    }

    function atualizarResumo() {
        var inicio = $('horaInicio').value;
        var fim = $('horaFim').value;
        var resumo = $('resumoReserva');

        if (inicio && fim && minutosDe(fim) > minutosDe(inicio)) {
            $('resumoTexto').textContent = 'Das ' + inicio + ' às ' + fim + ' · ' + textoDuracao(inicio, fim);
            resumo.hidden = false;
        } else {
            resumo.hidden = true;
        }
    }

    ['horaInicio', 'horaFim', 'dataReserva'].forEach(function (id) {
        $(id).addEventListener('input', function () {
            limparMensagem();
            atualizarResumo();
            if (id !== 'dataReserva') {
                document.querySelectorAll('[data-duracao]').forEach(function (b) { b.classList.remove('ativo'); });
            }
        });
    });

    function validarReserva(data, inicio, fim) {
        var agora = new Date();
        var inicioReserva = paraDate(data, inicio);
        var fimReserva = paraDate(data, fim);

        if (fimReserva <= inicioReserva) {
            return 'O horário de término precisa ser depois do horário de início.';
        }

        var hoje = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate());
        if (paraDate(data, '00:00') < hoje) {
            return 'Não é possível reservar uma data que já passou.';
        }

        if (inicioReserva <= agora) {
            return 'O horário de início precisa ser um horário futuro.';
        }

        return null;
    }

    function confirmarReserva() {
        var data = $('dataReserva').value;
        var inicio = $('horaInicio').value;
        var fim = $('horaFim').value;

        if (!data)   { return mostrarMensagem('Selecione uma data.', 'erro'); }
        if (!inicio) { return mostrarMensagem('Selecione o horário de início.', 'erro'); }
        if (!fim)    { return mostrarMensagem('Selecione o horário de término.', 'erro'); }

        var erro = validarReserva(data, inicio, fim);
        if (erro) { return mostrarMensagem(erro, 'erro'); }

        if (obterReserva()) {
            return mostrarMensagem('Você já possui uma reserva ativa.', 'erro');
        }

        var armario = estado.armarioSelecionado;
        if (!armario) { return; }

        var reserva = {
            armario: armario.nome,
            localizacao: armario.local,
            data: data,
            inicio: inicio,
            fim: fim,
            status: 'Ativa',
            criadaEm: new Date().toISOString()
        };

        salvarJSON(CHAVE_RESERVA, reserva);

        var historico = obterHistorico();
        historico.push(reserva);
        salvarJSON(CHAVE_HISTORICO, historico);

        var botao = $('btnConfirmar');
        botao.disabled = true;
        botao.classList.add('carregando');
        mostrarMensagem('Reserva realizada com sucesso!', 'sucesso');
        atualizarDashboard();

        setTimeout(function () {
            botao.classList.remove('carregando');
            fecharModal('modalReserva');
            irPara('reserva');
            toast('Reserva do ' + reserva.armario + ' confirmada.', 'sucesso');
        }, 800);
    }

    $('btnConfirmar').addEventListener('click', confirmarReserva);

    /* atalhos de duração */
    document.querySelectorAll('[data-duracao]').forEach(function (b) {
        b.addEventListener('click', function () {
            var inicio = $('horaInicio').value;

            if (!inicio) {
                var d = new Date(Date.now() + 5 * 60000);
                inicio = pad(d.getHours()) + ':' + pad(d.getMinutes());
                if (d.getDate() !== new Date().getDate()) { inicio = '23:00'; }
                $('horaInicio').value = inicio;
            }

            var total = minutosDe(inicio) + Number(this.getAttribute('data-duracao'));

            if (total >= 1440) {
                mostrarMensagem('Essa duração ultrapassa a meia-noite. Escolha um horário de término no mesmo dia.', 'erro');
                return;
            }

            $('horaFim').value = pad(Math.floor(total / 60)) + ':' + pad(total % 60);
            document.querySelectorAll('[data-duracao]').forEach(function (x) { x.classList.toggle('ativo', x === b); });
            limparMensagem();
            atualizarResumo();
        });
    });

    /* =====================================================
       CANCELAMENTO
    ===================================================== */

    $('btnCancelar').addEventListener('click', function () {
        var reserva = obterReserva();
        if (!reserva) {
            toast('Você não possui uma reserva ativa.', 'erro');
            return;
        }

        $('textoCancelar').textContent =
            'A reserva do ' + reserva.armario + ' em ' + formatarData(reserva.data) +
            ', das ' + reserva.inicio + ' às ' + reserva.fim + ', será cancelada.';

        abrirModal('modalCancelar');
    });

    $('btnConfirmarCancelar').addEventListener('click', function () {
        var reserva = obterReserva();
        fecharModal('modalCancelar');

        if (!reserva) { atualizarDashboard(); return; }

        atualizarStatusNoHistorico(reserva, 'Cancelada');
        removerChave(CHAVE_RESERVA);

        atualizarDashboard();
        toast('Reserva cancelada com sucesso.', 'sucesso');
        irPara('reserva');
    });

    /* =====================================================
       RENDERIZAÇÃO - ARMÁRIOS
    ===================================================== */

    function htmlArmario(a, reserva) {
        var status = statusDoArmario(a, reserva);
        var botao;

        if (status === 'livre') {
            botao = reserva
                ? '<button class="btn btn-secundario btn-bloco" type="button" disabled>' +
                  '<i class="fas fa-calendar-plus" aria-hidden="true"></i> Reservar</button>'
                : '<button class="btn btn-primario btn-bloco" type="button" data-reservar="' + esc(a.id) + '"' +
                  ' aria-label="Reservar ' + esc(a.nome) + '">' +
                  '<i class="fas fa-calendar-plus" aria-hidden="true"></i> Reservar</button>';
        } else if (status === 'minha') {
            botao = '<button class="btn btn-escuro btn-bloco" type="button" data-ir="reserva">' +
                    '<i class="fas fa-eye" aria-hidden="true"></i> Ver reserva</button>';
        } else {
            botao = '<button class="btn btn-secundario btn-bloco" type="button" disabled>' +
                    '<i class="fas fa-ban" aria-hidden="true"></i> Indisponível</button>';
        }

        return '<article class="armario" data-status="' + status + '">' +
            '<div class="armario-porta">' +
                '<div class="armario-topo">' +
                    '<span class="armario-num">' + esc(a.id) + '</span>' +
                    '<span class="led" aria-hidden="true"></span>' +
                '</div>' +
                '<div class="armario-ventilacao" aria-hidden="true"><i></i><i></i><i></i></div>' +
                '<span class="armario-macaneta" aria-hidden="true"></span>' +
                '<div class="armario-info">' +
                    '<h3>' + esc(a.nome) + '</h3>' +
                    '<span><i class="fas fa-location-dot" aria-hidden="true"></i> ' + esc(a.local) + '</span>' +
                '</div>' +
                '<span class="badge ' + status + '">' + ROTULOS[status] + '</span>' +
            '</div>' +
            botao +
        '</article>';
    }

    function renderArmarios() {
        var reserva = obterReserva();

        /* grade resumida do início: todos os armários */
        trocarHTML($('gradeResumo'), ARMARIOS.map(function (a) { return htmlArmario(a, reserva); }).join(''));

        /* grade completa com busca e filtro */
        var termo = estado.busca.trim().toLowerCase();
        var contas = { todos: 0, livre: 0, ocupado: 0 };

        var visiveis = ARMARIOS.filter(function (a) {
            var status = statusDoArmario(a, reserva);
            var grupo = status === 'livre' ? 'livre' : 'ocupado';
            var casaBusca = !termo ||
                (a.nome + ' ' + a.local + ' ' + a.id).toLowerCase().indexOf(termo) !== -1;

            if (casaBusca) { contas.todos++; contas[grupo]++; }
            return casaBusca && (estado.filtroArmario === 'todos' || estado.filtroArmario === grupo);
        });

        document.querySelectorAll('[data-conta]').forEach(function (s) {
            s.textContent = contas[s.getAttribute('data-conta')];
        });

        var html = visiveis.length
            ? visiveis.map(function (a) { return htmlArmario(a, reserva); }).join('')
            : '<div class="vazio vazio-grade">' +
                  '<div class="vazio-icone"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></div>' +
                  '<h3>Nenhum armário encontrado</h3>' +
                  '<p>Tente outro termo de busca ou outro filtro.</p>' +
                  '<button class="btn btn-secundario" type="button" data-limpar-filtros>Limpar filtros</button>' +
              '</div>';

        trocarHTML($('gradeCompleta'), html);

        $('avisoReserva').hidden = !reserva;

        var livres = ARMARIOS.filter(function (a) { return statusDoArmario(a, reserva) === 'livre'; }).length;
        $('contadorDisponiveis').textContent = livres;

        renderBanco(reserva);
        renderOcupacao(reserva);
    }

    /* mini mapa de armários do cartão principal */
    function renderBanco(reserva) {
        var html = ARMARIOS.map(function (a) {
            var status = statusDoArmario(a, reserva);
            return '<div class="porta" data-status="' + status + '">' +
                '<span>' + esc(a.id) + '</span><i></i></div>';
        }).join('');
        trocarHTML($('heroBanco'), html);
    }

    function renderOcupacao(reserva) {
        var contas = { livre: 0, minha: 0, ocupado: 0 };
        ARMARIOS.forEach(function (a) { contas[statusDoArmario(a, reserva)]++; });

        var total = ARMARIOS.length || 1;
        var pLivre = contas.livre / total * 100;
        var pMinha = contas.minha / total * 100;
        var ocupados = contas.ocupado + contas.minha;

        var rosca = $('rosca');
        rosca.style.setProperty('--p-livre', pLivre + '%');
        rosca.style.setProperty('--p-minha', (pLivre + pMinha) + '%');
        rosca.setAttribute('aria-label',
            contas.livre + ' disponíveis, ' + contas.minha + ' com sua reserva e ' +
            contas.ocupado + ' ocupados, de ' + ARMARIOS.length + ' armários');

        $('roscaPercentual').textContent = Math.round(ocupados / total * 100) + '%';

        trocarHTML($('roscaLegenda'),
            '<li><i class="ponto livre"></i> Disponíveis <strong>' + contas.livre + '</strong></li>' +
            '<li><i class="ponto minha"></i> Minha reserva <strong>' + contas.minha + '</strong></li>' +
            '<li><i class="ponto ocupado"></i> Ocupados <strong>' + contas.ocupado + '</strong></li>');
    }

    /* cliques nos armários (delegação: funciona mesmo após re-renderizar) */
    document.addEventListener('click', function (e) {
        var reservar = e.target.closest('[data-reservar]');
        if (reservar) { abrirReserva(reservar.getAttribute('data-reservar')); return; }

        if (e.target.closest('[data-limpar-filtros]')) {
            estado.busca = '';
            estado.filtroArmario = 'todos';
            $('buscaArmario').value = '';
            marcarSegmento('filtrosArmario', 'todos', 'data-status');
            renderArmarios();
        }
    });

    $('buscaArmario').addEventListener('input', function () {
        estado.busca = this.value;
        renderArmarios();
    });

    function marcarSegmento(grupoId, valor, atributo) {
        $(grupoId).querySelectorAll('.segmento').forEach(function (s) {
            var ativo = s.getAttribute(atributo) === valor;
            s.classList.toggle('ativo', ativo);
            s.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        });
    }

    $('filtrosArmario').addEventListener('click', function (e) {
        var b = e.target.closest('.segmento');
        if (!b) { return; }
        estado.filtroArmario = b.getAttribute('data-status');
        marcarSegmento('filtrosArmario', estado.filtroArmario, 'data-status');
        renderArmarios();
    });

    /* =====================================================
       RENDERIZAÇÃO - RESERVA, CABEÇALHO, QR, HISTÓRICO
    ===================================================== */

    function renderReserva() {
        var reserva = obterReserva();

        $('semReserva').hidden = !!reserva;
        $('reservaAtiva').hidden = !reserva;

        $('contadorReserva').textContent = reserva ? 1 : 0;
        $('contadorQr').textContent = reserva ? 1 : 0;
        $('badgeReserva').classList.toggle('visivel', !!reserva);

        if (!reserva) { return; }

        $('reservaNumero').textContent = numeroDe(reserva.armario);
        $('reservaTitulo').textContent = reserva.armario;
        $('reservaLocal').textContent = reserva.localizacao || '';
        $('reservaData').textContent = formatarData(reserva.data);
        $('reservaHorario').textContent = reserva.inicio + ' às ' + reserva.fim;
        $('reservaDuracao').textContent = textoDuracao(reserva.inicio, reserva.fim);
        $('reservaInicio').textContent = reserva.inicio;
        $('reservaFim').textContent = reserva.fim;
        $('reservaCriada').textContent = reserva.criadaEm ? formatarDataHora(reserva.criadaEm) : '-';

        atualizarContagem();
    }

    function atualizarContagem() {
        var reserva = obterReserva();
        if (!reserva) { return; }

        var agora = new Date();
        var inicio = paraDate(reserva.data, reserva.inicio);
        var fim = paraDate(reserva.data, reserva.fim);
        var emAndamento = agora >= inicio;

        var pct = emAndamento ? Math.min(100, Math.max(0, (agora - inicio) / (fim - inicio) * 100)) : 0;

        $('reservaContagem').textContent = emAndamento
            ? 'Em andamento · termina em ' + textoTempo(fim - agora)
            : 'Começa em ' + textoTempo(inicio - agora);

        $('reservaPercentual').textContent = Math.round(pct) + '%';
        $('reservaBarra').style.width = pct + '%';
        $('reservaProgresso').setAttribute('aria-valuenow', String(Math.round(pct)));

        var badge = $('reservaBadge');
        badge.textContent = emAndamento ? 'Em uso' : 'Agendada';
        badge.className = 'badge ' + (emAndamento ? 'ativa' : 'agendada');
    }

    function renderCabecalho() {
        var reserva = obterReserva();
        var heroBotao = $('heroBotao');
        var topo = $('btnTopoAcao');
        var rotulo = $('heroRotulo');

        if (reserva) {
            var emAndamento = new Date() >= paraDate(reserva.data, reserva.inicio);

            rotulo.innerHTML = '<span class="led ligado" aria-hidden="true"></span> ' +
                (emAndamento ? 'Reserva em andamento' : 'Reserva agendada');
            $('heroTexto').textContent =
                'Você tem o ' + reserva.armario + ' reservado em ' + formatarData(reserva.data) +
                ', das ' + reserva.inicio + ' às ' + reserva.fim + '.';
            heroBotao.innerHTML = '<i class="fas fa-calendar-check" aria-hidden="true"></i> Ver minha reserva';
            heroBotao.onclick = function () { irPara('reserva'); };

            topo.innerHTML = '<i class="fas fa-qrcode" aria-hidden="true"></i><span>Meu QR Code</span>';
            topo.onclick = function () { irPara('qrcode'); };

            $('sidebarStatus').classList.add('ativo');
            $('sidebarStatusTitulo').textContent = reserva.armario;
            $('sidebarStatusTexto').textContent = formatarData(reserva.data) + ' · ' + reserva.inicio + ' às ' + reserva.fim;
        } else {
            rotulo.innerHTML = '<span class="led" aria-hidden="true"></span> Nenhuma reserva ativa';
            $('heroTexto').textContent = 'Escolha um armário disponível e reserve em poucos segundos.';
            heroBotao.innerHTML = '<i class="fas fa-calendar-plus" aria-hidden="true"></i> Reservar armário';
            heroBotao.onclick = function () { irPara('armarios'); };

            topo.innerHTML = '<i class="fas fa-calendar-plus" aria-hidden="true"></i><span>Reservar</span>';
            topo.onclick = function () { irPara('armarios'); };

            $('sidebarStatus').classList.remove('ativo');
            $('sidebarStatusTitulo').textContent = 'Sem reserva ativa';
            $('sidebarStatusTexto').textContent = 'Escolha um armário disponível';
        }
    }

    function renderQRCode() {
        var reserva = obterReserva();

        $('qrcodeSemReserva').hidden = !!reserva;
        $('qrcodeAtivo').hidden = !reserva;

        var moldura = $('qrMoldura');

        if (!reserva) {
            moldura.innerHTML = '';
            moldura.removeAttribute('data-conteudo');
            return;
        }

        $('qrArmario').textContent = reserva.armario + (reserva.localizacao ? ' · ' + reserva.localizacao : '');
        $('qrQuando').textContent = formatarData(reserva.data) + ', das ' + reserva.inicio + ' às ' + reserva.fim;

        var conteudo = JSON.stringify({
            armario: reserva.armario,
            data: reserva.data,
            inicio: reserva.inicio,
            fim: reserva.fim,
            ref: reserva.criadaEm || ''
        }).replace(/[\u0080-￿]/g, function (c) {
            /* a biblioteca qrcodejs falha com acentos ("Armário");
               escapar como \uXXXX mantém o mesmo JSON e evita o erro */
            return '\\u' + ('000' + c.charCodeAt(0).toString(16)).slice(-4);
        });

        if (moldura.getAttribute('data-conteudo') === conteudo) { return; }

        moldura.innerHTML = '';
        moldura.setAttribute('data-conteudo', conteudo);

        try {
            if (typeof QRCode === 'undefined') { throw new Error('QRCode indisponível'); }
            new QRCode(moldura, {
                text: conteudo,
                width: 220,
                height: 220,
                colorDark: '#14161b',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            /* a biblioteca coloca um "title" com o conteúdo bruto; removemos */
            moldura.removeAttribute('title');
        } catch (e) {
            moldura.innerHTML = '<div class="qr-erro"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i>' +
                '<span>Não foi possível gerar o QR Code. Verifique sua conexão e recarregue a página.</span></div>';
        }
    }

    var CLASSE_STATUS = { 'Cancelada': 'cancelada', 'Concluída': 'concluida', 'Ativa': 'ativa' };
    var ICONE_STATUS = { cancelada: 'fa-calendar-xmark', concluida: 'fa-circle-check', ativa: 'fa-lock' };

    function renderHistorico() {
        var historico = obterHistorico();
        var lista = $('listaHistorico');

        $('contadorHistorico').textContent = historico.length;

        var contas = { todas: historico.length, 'Ativa': 0, 'Concluída': 0, 'Cancelada': 0 };
        historico.forEach(function (r) {
            var s = r.status || 'Ativa';
            if (contas[s] !== undefined) { contas[s]++; }
        });
        document.querySelectorAll('[data-conta-h]').forEach(function (s) {
            s.textContent = contas[s.getAttribute('data-conta-h')];
        });

        var itens = historico.slice().reverse().filter(function (r) {
            return estado.filtroHistorico === 'todas' || (r.status || 'Ativa') === estado.filtroHistorico;
        });

        if (!itens.length) {
            trocarHTML(lista,
                '<div class="vazio">' +
                    '<div class="vazio-icone"><i class="fas fa-clock" aria-hidden="true"></i></div>' +
                    '<h3>' + (historico.length ? 'Nenhuma reserva neste filtro' : 'Nenhuma reserva ainda') + '</h3>' +
                    '<p>' + (historico.length
                        ? 'Tente selecionar outro filtro.'
                        : 'Suas reservas aparecerão aqui.') + '</p>' +
                '</div>');
        } else {
            trocarHTML(lista, itens.map(function (r) {
                var status = CLASSE_STATUS[r.status] || 'ativa';
                return '<div class="item-historico ' + status + '">' +
                    '<div class="item-numero">' + esc(numeroDe(r.armario)) + '</div>' +
                    '<div class="col-nome"><strong>' + esc(r.armario) + '</strong><small>' + esc(r.localizacao || '') + '</small></div>' +
                    '<div class="col-data"><small>Data</small><strong>' + esc(formatarData(r.data)) + '</strong></div>' +
                    '<div class="col-hora"><small>Horário</small><strong>' + esc(r.inicio) + ' às ' + esc(r.fim) + '</strong></div>' +
                    '<span class="badge ' + status + '"><i class="fas ' + ICONE_STATUS[status] + '" aria-hidden="true"></i> ' +
                        esc(r.status || 'Ativa') + '</span>' +
                '</div>';
            }).join(''));
        }

        renderAtividade(historico);
    }

    function renderAtividade(historico) {
        var ultimos = historico.slice(-4).reverse();

        if (!ultimos.length) {
            trocarHTML($('listaAtividade'),
                '<li class="atividade-vazia"><i class="fas fa-clock" aria-hidden="true"></i>' +
                'Nenhuma atividade ainda. Suas reservas aparecerão aqui.</li>');
            return;
        }

        trocarHTML($('listaAtividade'), ultimos.map(function (r) {
            var status = CLASSE_STATUS[r.status] || 'ativa';
            var verbo = status === 'cancelada' ? 'Reserva cancelada'
                      : status === 'concluida' ? 'Reserva concluída' : 'Reserva ativa';
            return '<li class="' + status + '">' +
                '<span class="atividade-icone"><i class="fas ' + ICONE_STATUS[status] + '" aria-hidden="true"></i></span>' +
                '<div><strong>' + verbo + ' · ' + esc(r.armario) + '</strong>' +
                '<small>' + esc(formatarData(r.data)) + ', ' + esc(r.inicio) + ' às ' + esc(r.fim) + '</small></div>' +
            '</li>';
        }).join(''));
    }

    $('filtrosHistorico').addEventListener('click', function (e) {
        var b = e.target.closest('.segmento');
        if (!b) { return; }
        estado.filtroHistorico = b.getAttribute('data-filtro');
        marcarSegmento('filtrosHistorico', estado.filtroHistorico, 'data-filtro');
        renderHistorico();
    });

    /* =====================================================
       ATUALIZAÇÃO GERAL
    ===================================================== */

    function atualizarDashboard() {
        verificarExpiracao();
        renderArmarios();
        renderReserva();
        renderCabecalho();
        renderQRCode();
        renderHistorico();
    }

    /* sincroniza com outras abas */
    window.addEventListener('storage', function (e) {
        if (e.key === CHAVE_RESERVA || e.key === CHAVE_HISTORICO) { atualizarDashboard(); }
    });

    /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

    function atualizarTopo() {
        var agora = new Date();
        var h = agora.getHours();
        $('saudacao').textContent = h < 12 ? 'Bom dia' : h < 18 ? 'Boa tarde' : 'Boa noite';

        var data = agora.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' });
        $('dataTopo').textContent = data.charAt(0).toUpperCase() + data.slice(1);
    }

    atualizarTopo();
    atualizarDashboard();

    var inicial = location.hash.replace('#', '');
    estado.view = null;
    irPara(TITULOS[inicial] ? inicial : 'inicio', false);

    setInterval(function () {
        atualizarTopo();
        atualizarDashboard();
    }, 20000);

})();
</script>

</body>
</html>