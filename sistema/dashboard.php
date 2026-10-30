<?php
session_start();

/*
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
*/

$usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$inicial = mb_strtoupper(mb_substr(trim($usuario) !== '' ? trim($usuario) : 'U', 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLocker - Área do Cliente</title>

    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<!-- ================= MENU LATERAL ================= -->
<aside class="sidebar" id="sidebar" aria-label="Menu principal">

    <div class="logo">
        <div class="logo-icone"><i class="fas fa-box"></i></div>
        <div class="logo-texto">
            <h2>SmartLocker</h2>
            <span>Área do cliente</span>
        </div>
    </div>

    <nav>
        <div class="nav-titulo">Geral</div>

        <a href="#inicio" class="nav-link active" data-view="inicio">
            <i class="fas fa-house"></i> Início
        </a>

        <a href="#armarios" class="nav-link" data-view="armarios">
            <i class="fas fa-box"></i> Armários
        </a>

        <div class="nav-titulo">Minha conta</div>

        <a href="#reserva" class="nav-link" data-view="reserva">
            <i class="fas fa-calendar-check"></i> Minha reserva
            <span class="nav-badge" id="badgeReserva">1</span>
        </a>

        <a href="#historico" class="nav-link" data-view="historico">
            <i class="fas fa-clock-rotate-left"></i> Histórico
        </a>

        <a href="#qrcode" class="nav-link" data-view="qrcode">
            <i class="fas fa-qrcode"></i> Meu QR Code
        </a>

        <a href="#perfil" class="nav-link" data-view="perfil">
            <i class="fas fa-user"></i> Meu perfil
        </a>
    </nav>

    <div class="sidebar-rodape">
        <a href="logout.php" class="logout">
            <i class="fas fa-right-from-bracket"></i> Sair
        </a>
    </div>

</aside>

<div class="overlay" id="overlay"></div>

<!-- ================= CONTEÚDO ================= -->
<main class="main">

    <!-- TOPO -->
    <div class="topbar">
        <button class="btn-menu" id="btnMenu" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-titulo">
            <small id="dataTopo"></small>
            <strong id="tituloTopo">Início</strong>
        </div>

        <div class="usuario-chip">
            <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
            <div>
                <strong><?= htmlspecialchars($usuario) ?></strong>
                <span>Cliente</span>
            </div>
        </div>
    </div>


    <!-- ============ INÍCIO ============ -->
    <section class="view ativa" id="inicio">

        <div class="hero">
            <div>
                <h1>Olá, <?= htmlspecialchars($usuario) ?> 👋</h1>
                <p id="heroTexto">Gerencie seus armários e reservas em um só lugar.</p>
            </div>
            <div class="hero-acao">
                <button class="btn-branco" id="heroBotao" type="button">
                    <i class="fas fa-calendar-plus"></i> Reservar armário
                </button>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icone verde"><i class="fas fa-lock-open"></i></div>
                <div>
                    <h3 id="contadorDisponiveis">0</h3>
                    <p>Armários disponíveis</p>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icone"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <h3 id="contadorReserva">0</h3>
                    <p>Reserva ativa</p>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icone cinza"><i class="fas fa-clock-rotate-left"></i></div>
                <div>
                    <h3 id="contadorHistorico">0</h3>
                    <p>Reservas no histórico</p>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icone"><i class="fas fa-qrcode"></i></div>
                <div>
                    <h3 id="contadorQr">0</h3>
                    <p>QR Code ativo</p>
                </div>
            </div>
        </div>

        <div class="painel">
            <div class="painel-topo">
                <h2>Armários</h2>
                <div class="legenda">
                    <span><i class="ponto"></i> Disponível</span>
                    <span><i class="ponto minha"></i> Minha reserva</span>
                    <span><i class="ponto ocupado"></i> Ocupado</span>
                </div>
            </div>
            <div class="grade-armarios" data-grade></div>
        </div>
    </section>


    <!-- ============ ARMÁRIOS ============ -->
    <section class="view" id="armarios">

        <div class="view-cabecalho">
            <h1>Armários</h1>
            <p>Escolha um armário disponível para realizar sua reserva.</p>
        </div>

        <div class="aviso" id="avisoReserva" hidden>
            <i class="fas fa-circle-info"></i>
            <span>Você já possui uma reserva ativa. Cancele-a para reservar outro armário.</span>
        </div>

        <div class="painel">
            <div class="painel-topo">
                <h2>Todos os armários</h2>
                <div class="legenda">
                    <span><i class="ponto"></i> Disponível</span>
                    <span><i class="ponto minha"></i> Minha reserva</span>
                    <span><i class="ponto ocupado"></i> Ocupado</span>
                </div>
            </div>
            <div class="grade-armarios" data-grade></div>
        </div>
    </section>


    <!-- ============ MINHA RESERVA ============ -->
    <section class="view" id="reserva">

        <div class="view-cabecalho">
            <h1>Minha reserva</h1>
            <p>Consulte e gerencie sua reserva atual.</p>
        </div>

        <!-- sem reserva -->
        <div class="painel vazio" id="semReserva">
            <div class="vazio-icone"><i class="fas fa-calendar-xmark"></i></div>
            <h2>Nenhuma reserva ativa</h2>
            <p>Você ainda não possui uma reserva. Escolha um armário para começar.</p>
            <button class="btn btn-primario" type="button" data-ir="armarios">
                <i class="fas fa-calendar-plus"></i> Escolher armário
            </button>
        </div>

        <!-- com reserva -->
        <div class="painel" id="reservaAtiva" hidden>

            <div class="reserva-cabecalho">
                <div class="armario-icone"><i class="fas fa-lock"></i></div>
                <div>
                    <h2 id="reservaTitulo">Armário</h2>
                    <p id="reservaLocal">Bloco</p>
                </div>
                <span class="badge ativa">Ativa</span>
            </div>

            <div class="reserva-dados">
                <div class="dado">
                    <span><i class="fas fa-box"></i> Armário</span>
                    <strong id="reservaArmario">-</strong>
                </div>
                <div class="dado">
                    <span><i class="fas fa-calendar"></i> Data</span>
                    <strong id="reservaData">-</strong>
                </div>
                <div class="dado">
                    <span><i class="fas fa-clock"></i> Horário</span>
                    <strong id="reservaHorario">-</strong>
                </div>
                <div class="dado">
                    <span><i class="fas fa-hourglass-half"></i> Duração</span>
                    <strong id="reservaDuracao">-</strong>
                </div>
            </div>

            <div class="contagem">
                <i class="fas fa-stopwatch"></i>
                <span id="reservaContagem">-</span>
            </div>

            <div class="acoes">
                <button class="btn btn-primario" type="button" data-ir="qrcode">
                    <i class="fas fa-qrcode"></i> Ver QR Code
                </button>
                <button class="btn btn-perigo" type="button" id="btnCancelar">
                    <i class="fas fa-calendar-xmark"></i> Cancelar reserva
                </button>
            </div>
        </div>
    </section>


    <!-- ============ HISTÓRICO ============ -->
    <section class="view" id="historico">

        <div class="view-cabecalho">
            <h1>Histórico</h1>
            <p>Consulte suas reservas anteriores.</p>
        </div>

        <div class="painel">
            <div class="painel-topo">
                <h2>Reservas</h2>
                <div class="filtros" id="filtros">
                    <button class="filtro ativo" type="button" data-filtro="todas">Todas</button>
                    <button class="filtro" type="button" data-filtro="Ativa">Ativas</button>
                    <button class="filtro" type="button" data-filtro="Concluída">Concluídas</button>
                    <button class="filtro" type="button" data-filtro="Cancelada">Canceladas</button>
                </div>
            </div>

            <div class="lista-historico" id="listaHistorico"></div>
        </div>
    </section>


    <!-- ============ QR CODE ============ -->
    <section class="view" id="qrcode">

        <div class="view-cabecalho">
            <h1>Meu QR Code</h1>
            <p>Use seu QR Code para acessar o armário reservado.</p>
        </div>

        <div class="painel qr-painel">

            <div class="vazio" id="qrcodeSemReserva">
                <div class="vazio-icone"><i class="fas fa-qrcode"></i></div>
                <h3>Nenhum QR Code ativo</h3>
                <p>Faça uma reserva para gerar o seu QR Code.</p>
                <button class="btn btn-primario" type="button" data-ir="armarios">
                    <i class="fas fa-calendar-plus"></i> Escolher armário
                </button>
            </div>

            <div id="qrcodeAtivo" hidden>
                <div class="qr-moldura" id="qrMoldura"></div>
                <p class="qr-info" id="qrArmario">Armário</p>
                <p class="qr-ajuda">Apresente este código no leitor do SmartLocker.</p>
            </div>
        </div>
    </section>


    <!-- ============ PERFIL ============ -->
    <section class="view" id="perfil">

        <div class="view-cabecalho">
            <h1>Meu perfil</h1>
            <p>Informações da sua conta.</p>
        </div>

        <div class="painel">
            <div class="perfil-topo">
                <div class="avatar grande"><?= htmlspecialchars($inicial) ?></div>
                <div>
                    <h2><?= htmlspecialchars($usuario) ?></h2>
                    <p>Cliente SmartLocker</p>
                </div>
            </div>

            <div class="perfil-grade">
                <div class="dado">
                    <span><i class="fas fa-user"></i> Nome do cliente</span>
                    <strong><?= htmlspecialchars($usuario) ?></strong>
                </div>
                <div class="dado">
                    <span><i class="fas fa-user-tag"></i> Tipo de usuário</span>
                    <strong>Cliente</strong>
                </div>
                <div class="dado">
                    <span><i class="fas fa-circle-check"></i> Status da conta</span>
                    <strong style="color: var(--verde);">Ativo</strong>
                </div>
            </div>
        </div>
    </section>

</main>


<!-- ================= MODAL: RESERVA ================= -->
<div class="modal" id="modalReserva" role="dialog" aria-modal="true" aria-labelledby="tituloReserva">
    <div class="modal-conteudo">

        <button class="modal-fechar" type="button" data-fechar="modalReserva" aria-label="Fechar">
            <i class="fas fa-xmark"></i>
        </button>

        <h2 id="tituloReserva">Fazer reserva</h2>
        <p class="modal-sub">Escolha a data e o horário.</p>

        <div class="modal-selecionado">
            <div class="armario-icone"><i class="fas fa-lock-open"></i></div>
            <div>
                <strong id="modalArmario">Armário</strong>
                <small id="modalLocal">Bloco</small>
            </div>
        </div>

        <div class="form-reserva">

            <div class="campo campo-full">
                <label for="dataReserva">Data da reserva</label>
                <input type="date" id="dataReserva">
            </div>

            <div class="campo">
                <label for="horaInicio">Horário de início</label>
                <input type="time" id="horaInicio">
            </div>

            <div class="campo">
                <label for="horaFim">Horário de término</label>
                <input type="time" id="horaFim">
            </div>

            <div class="campo campo-full">
                <label>Duração rápida</label>
                <div class="duracoes">
                    <button class="filtro" type="button" data-duracao="60">1 hora</button>
                    <button class="filtro" type="button" data-duracao="120">2 horas</button>
                    <button class="filtro" type="button" data-duracao="240">4 horas</button>
                </div>
            </div>
        </div>

        <div class="mensagem" id="mensagemReserva" role="alert"></div>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalReserva">Cancelar</button>
            <button class="btn btn-primario" type="button" id="btnConfirmar">
                <i class="fas fa-check"></i> Confirmar reserva
            </button>
        </div>
    </div>
</div>


<!-- ================= MODAL: CONFIRMAR CANCELAMENTO ================= -->
<div class="modal" id="modalCancelar" role="dialog" aria-modal="true" aria-labelledby="tituloCancelar">
    <div class="modal-conteudo pequeno">
        <div class="modal-icone"><i class="fas fa-calendar-xmark"></i></div>
        <h2 id="tituloCancelar">Cancelar reserva?</h2>
        <p class="modal-sub" id="textoCancelar"></p>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalCancelar">Manter reserva</button>
            <button class="btn btn-primario" type="button" id="btnConfirmarCancelar">
                Sim, cancelar
            </button>
        </div>
    </div>
</div>

<div class="toasts" id="toasts" aria-live="polite"></div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    /* =====================================================
       CONFIGURAÇÃO
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

    var armarioSelecionado = null;
    var filtroAtual = 'todas';
    var viewAtual = 'inicio';

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
        try {
            localStorage.setItem(chave, JSON.stringify(valor));
        } catch (e) { /* armazenamento indisponível */ }
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

    /* =====================================================
       NAVEGAÇÃO
    ===================================================== */

    var sidebar = $('sidebar');
    var overlay = $('overlay');
    var links = document.querySelectorAll('.nav-link');

    function abrirMenu() {
        sidebar.classList.add('aberta');
        overlay.classList.add('visivel');
    }

    function fecharMenu() {
        sidebar.classList.remove('aberta');
        overlay.classList.remove('visivel');
    }

    function irPara(destino, atualizarHash) {
        if (!TITULOS[destino]) { destino = 'inicio'; }
        viewAtual = destino;

        document.querySelectorAll('.view').forEach(function (v) {
            v.classList.toggle('ativa', v.id === destino);
        });

        links.forEach(function (l) {
            l.classList.toggle('active', l.getAttribute('data-view') === destino);
        });

        $('tituloTopo').textContent = TITULOS[destino];

        if (atualizarHash !== false && location.hash !== '#' + destino) {
            history.replaceState(null, '', '#' + destino);
        }

        fecharMenu();
        window.scrollTo(0, 0);
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            irPara(this.getAttribute('data-view'));
        });
    });

    document.querySelectorAll('[data-ir]').forEach(function (b) {
        b.addEventListener('click', function () { irPara(this.getAttribute('data-ir')); });
    });

    $('btnMenu').addEventListener('click', abrirMenu);
    overlay.addEventListener('click', fecharMenu);

    window.addEventListener('hashchange', function () {
        irPara(location.hash.replace('#', ''), false);
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
        el.innerHTML = '<i class="fas ' + icones[tipo] + '"></i><span>' + esc(texto) + '</span>';
        $('toasts').appendChild(el);

        setTimeout(function () {
            el.classList.add('saindo');
            setTimeout(function () { el.remove(); }, 320);
        }, 3800);
    }

    /* =====================================================
       MODAIS
    ===================================================== */

    function abrirModal(id) {
        $(id).classList.add('aberto');
        document.body.classList.add('travado');
    }

    function fecharModal(id) {
        $(id).classList.remove('aberto');
        if (!document.querySelector('.modal.aberto')) {
            document.body.classList.remove('travado');
        }
    }

    document.querySelectorAll('[data-fechar]').forEach(function (b) {
        b.addEventListener('click', function () { fecharModal(this.getAttribute('data-fechar')); });
    });

    document.querySelectorAll('.modal').forEach(function (m) {
        m.addEventListener('mousedown', function (e) {
            if (e.target === m) { fecharModal(m.id); }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.aberto').forEach(function (m) { fecharModal(m.id); });
            fecharMenu();
        }
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

        armarioSelecionado = armario;

        $('modalArmario').textContent = armario.nome;
        $('modalLocal').textContent = armario.local;

        var data = $('dataReserva');
        data.min = dataAtualISO();
        data.value = dataAtualISO();

        $('horaInicio').value = '';
        $('horaFim').value = '';

        var msg = $('mensagemReserva');
        msg.className = 'mensagem';
        msg.textContent = '';

        $('btnConfirmar').disabled = false;

        abrirModal('modalReserva');
    }

    function mostrarMensagem(texto, tipo) {
        var msg = $('mensagemReserva');
        msg.className = 'mensagem ' + tipo;
        msg.innerHTML = '<i class="fas ' +
            (tipo === 'erro' ? 'fa-circle-exclamation' : 'fa-circle-check') +
            '"></i> ' + esc(texto);
    }

    function validarReserva(data, inicio, fim) {
        var agora = new Date();
        var inicioReserva = paraDate(data, inicio);
        var fimReserva = paraDate(data, fim);

        if (fimReserva <= inicioReserva) {
            return 'O horário de término precisa ser depois do horário de início.';
        }

        var hoje = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate());
        var dataEscolhida = paraDate(data, '00:00');

        if (dataEscolhida < hoje) {
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

        if (!armarioSelecionado) { return; }

        var reserva = {
            armario: armarioSelecionado.nome,
            localizacao: armarioSelecionado.local,
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

        $('btnConfirmar').disabled = true;
        mostrarMensagem('Reserva realizada com sucesso!', 'sucesso');
        atualizarDashboard();

        setTimeout(function () {
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
            $('mensagemReserva').className = 'mensagem';
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
            'Deseja realmente cancelar a reserva do ' + reserva.armario + ' em ' +
            formatarData(reserva.data) + ' das ' + reserva.inicio + ' às ' + reserva.fim + '?';

        abrirModal('modalCancelar');
    });

    $('btnConfirmarCancelar').addEventListener('click', function () {
        var reserva = obterReserva();
        fecharModal('modalCancelar');

        if (!reserva) { atualizarDashboard(); return; }

        atualizarStatusNoHistorico(reserva, 'Cancelada');
        removerChave(CHAVE_RESERVA);

        atualizarDashboard();
        toast('Reserva cancelada com sucesso!', 'sucesso');
        irPara('reserva');
    });

    /* =====================================================
       RENDERIZAÇÃO
    ===================================================== */

    function statusDoArmario(armario, reserva) {
        if (reserva && reserva.armario === armario.nome) { return 'minha'; }
        return armario.ocupado ? 'ocupado' : 'livre';
    }

    var ROTULOS = { livre: 'Disponível', ocupado: 'Ocupado', minha: 'Minha reserva' };

    function renderArmarios() {
        var reserva = obterReserva();

        var html = ARMARIOS.map(function (a) {
            var status = statusDoArmario(a, reserva);
            var icone = status === 'livre' ? 'fa-lock-open' : 'fa-lock';
            var botao;

            if (status === 'livre') {
                botao = reserva
                    ? '<button class="btn btn-primario btn-bloco" type="button" disabled>' +
                      '<i class="fas fa-calendar-plus"></i> Reservar</button>'
                    : '<button class="btn btn-primario btn-bloco" type="button" data-reservar="' + a.id + '">' +
                      '<i class="fas fa-calendar-plus"></i> Reservar</button>';
            } else if (status === 'minha') {
                botao = '<button class="btn btn-secundario btn-bloco" type="button" data-ir-reserva>' +
                        '<i class="fas fa-eye"></i> Ver reserva</button>';
            } else {
                botao = '<button class="btn btn-secundario btn-bloco" type="button" disabled>' +
                        '<i class="fas fa-ban"></i> Indisponível</button>';
            }

            return '<article class="armario" data-status="' + status + '">' +
                '<div class="armario-topo">' +
                    '<div class="armario-icone"><i class="fas ' + icone + '"></i></div>' +
                    '<span class="badge ' + status + '">' + ROTULOS[status] + '</span>' +
                '</div>' +
                '<div>' +
                    '<h3>' + esc(a.nome) + '</h3>' +
                    '<div class="armario-local"><i class="fas fa-location-dot"></i> ' + esc(a.local) + '</div>' +
                '</div>' +
                botao +
            '</article>';
        }).join('');

        document.querySelectorAll('[data-grade]').forEach(function (g) { g.innerHTML = html; });

        document.querySelectorAll('[data-reservar]').forEach(function (b) {
            b.addEventListener('click', function () { abrirReserva(this.getAttribute('data-reservar')); });
        });

        document.querySelectorAll('[data-ir-reserva]').forEach(function (b) {
            b.addEventListener('click', function () { irPara('reserva'); });
        });

        $('avisoReserva').hidden = !reserva;

        var livres = ARMARIOS.filter(function (a) {
            return statusDoArmario(a, reserva) === 'livre';
        }).length;

        $('contadorDisponiveis').textContent = livres;
    }

    function renderReserva() {
        var reserva = obterReserva();

        $('semReserva').hidden = !!reserva;
        $('reservaAtiva').hidden = !reserva;

        $('contadorReserva').textContent = reserva ? 1 : 0;
        $('contadorQr').textContent = reserva ? 1 : 0;
        $('badgeReserva').classList.toggle('visivel', !!reserva);

        if (!reserva) { return; }

        $('reservaTitulo').textContent = reserva.armario;
        $('reservaLocal').textContent = reserva.localizacao || '';
        $('reservaArmario').textContent = reserva.armario;
        $('reservaData').textContent = formatarData(reserva.data);
        $('reservaHorario').textContent = reserva.inicio + ' às ' + reserva.fim;
        $('reservaDuracao').textContent = textoDuracao(reserva.inicio, reserva.fim);

        atualizarContagem();
    }

    function atualizarContagem() {
        var reserva = obterReserva();
        if (!reserva) { return; }

        var agora = new Date();
        var inicio = paraDate(reserva.data, reserva.inicio);
        var fim = paraDate(reserva.data, reserva.fim);

        $('reservaContagem').textContent = agora < inicio
            ? 'Sua reserva começa em ' + textoTempo(inicio - agora)
            : 'Em andamento — termina em ' + textoTempo(fim - agora);
    }

    function renderHero() {
        var reserva = obterReserva();
        var botao = $('heroBotao');

        if (reserva) {
            $('heroTexto').textContent =
                'Você tem o ' + reserva.armario + ' reservado em ' + formatarData(reserva.data) +
                ' das ' + reserva.inicio + ' às ' + reserva.fim + '.';
            botao.innerHTML = '<i class="fas fa-calendar-check"></i> Ver minha reserva';
            botao.onclick = function () { irPara('reserva'); };
        } else {
            $('heroTexto').textContent = 'Gerencie seus armários e reservas em um só lugar.';
            botao.innerHTML = '<i class="fas fa-calendar-plus"></i> Reservar armário';
            botao.onclick = function () { irPara('armarios'); };
        }
    }

    function renderQRCode() {
        var reserva = obterReserva();

        $('qrcodeSemReserva').hidden = !!reserva;
        $('qrcodeAtivo').hidden = !reserva;

        if (!reserva) { $('qrMoldura').innerHTML = ''; return; }

        $('qrArmario').textContent =
            reserva.armario + ' · ' + formatarData(reserva.data) + ' · ' + reserva.inicio;

        var moldura = $('qrMoldura');
        var conteudo = JSON.stringify({
            armario: reserva.armario,
            data: reserva.data,
            inicio: reserva.inicio,
            fim: reserva.fim,
            ref: reserva.criadaEm || ''
        });

        if (moldura.getAttribute('data-conteudo') === conteudo) { return; }

        moldura.innerHTML = '';
        moldura.setAttribute('data-conteudo', conteudo);

        try {
            if (typeof QRCode === 'undefined') { throw new Error('QRCode indisponível'); }
            new QRCode(moldura, {
                text: conteudo,
                width: 210,
                height: 210,
                colorDark: '#111827',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch (e) {
            moldura.innerHTML = '<i class="fas fa-qrcode qr-fallback"></i>';
        }
    }

    function renderHistorico() {
        var historico = obterHistorico();
        var lista = $('listaHistorico');

        $('contadorHistorico').textContent = historico.length;

        var itens = historico.slice().reverse().filter(function (r) {
            return filtroAtual === 'todas' || r.status === filtroAtual;
        });

        if (!itens.length) {
            lista.innerHTML =
                '<div class="vazio">' +
                    '<div class="vazio-icone"><i class="fas fa-clock"></i></div>' +
                    '<h3>' + (historico.length ? 'Nenhuma reserva neste filtro' : 'Nenhuma reserva ainda') + '</h3>' +
                    '<p>' + (historico.length
                        ? 'Tente selecionar outro filtro.'
                        : 'Ainda não existem reservas no histórico.') + '</p>' +
                '</div>';
            return;
        }

        lista.innerHTML = itens.map(function (r) {
            var status = r.status === 'Cancelada' ? 'cancelada'
                       : r.status === 'Concluída' ? 'concluida' : 'ativa';
            var icone = status === 'cancelada' ? 'fa-calendar-xmark'
                      : status === 'concluida' ? 'fa-circle-check' : 'fa-lock';

            return '<div class="item-historico ' + status + '">' +
                '<div class="armario-icone"><i class="fas ' + icone + '"></i></div>' +
                '<div><strong>' + esc(r.armario) + '</strong><small>' + esc(r.localizacao || '') + '</small></div>' +
                '<div class="col-data"><strong>' + esc(formatarData(r.data)) + '</strong><small>Data</small></div>' +
                '<div class="col-hora"><strong>' + esc(r.inicio) + ' às ' + esc(r.fim) + '</strong><small>Horário</small></div>' +
                '<span class="badge ' + status + '">' + esc(r.status || 'Ativa') + '</span>' +
            '</div>';
        }).join('');
    }

    document.querySelectorAll('.filtro[data-filtro]').forEach(function (b) {
        b.addEventListener('click', function () {
            filtroAtual = this.getAttribute('data-filtro');
            document.querySelectorAll('.filtro[data-filtro]').forEach(function (x) {
                x.classList.toggle('ativo', x === b);
            });
            renderHistorico();
        });
    });

    /* =====================================================
       ATUALIZAÇÃO GERAL
    ===================================================== */

    function atualizarDashboard() {
        verificarExpiracao();
        renderArmarios();
        renderReserva();
        renderHero();
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

    (function dataTopo() {
        var texto = new Date().toLocaleDateString('pt-BR', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
        $('dataTopo').textContent = texto;
    })();

    atualizarDashboard();

    var inicial = location.hash.replace('#', '');
    irPara(TITULOS[inicial] ? inicial : 'inicio', false);

    setInterval(function () {
        verificarExpiracao();
        renderArmarios();
        renderReserva();
        renderHero();
        renderQRCode();
        renderHistorico();
    }, 30000);

})();
</script>

</body>
</html>