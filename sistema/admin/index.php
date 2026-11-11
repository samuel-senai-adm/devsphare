<?php
/*
 * Painel administrativo do SmartLocker.
 * Só abre para contas com perfil 'admin' (verificado no banco a cada acesso).
 * Os dados vêm de ../api/admin/*.php, que também exigem administrador.
 */
require_once __DIR__ . '/../config/acesso.php';

$conta = exigirAdminPagina($conn, '../');
$csrf = tokenCsrf();

$nome = trim($conta['nome']) !== '' ? trim($conta['nome']) : 'Administrador';
$inicial = mb_strtoupper(mb_substr($nome, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14161b">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>">
    <title>SmartLocker - Painel administrativo</title>

    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23e11d2e' d='M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

<a class="pular" href="#conteudo">Pular para o conteúdo</a>

<!-- ================= MENU LATERAL ================= -->
<aside class="sidebar" id="sidebar" aria-label="Menu administrativo">

    <div class="logo">
        <div class="logo-marca" aria-hidden="true"><i class="fas fa-lock"></i></div>
        <div class="logo-texto">
            <strong>SmartLocker</strong>
            <span class="selo-admin"><i class="fas fa-shield-halved" aria-hidden="true"></i> Administração</span>
        </div>
        <button class="btn-icone sidebar-fechar" id="btnFecharMenu" type="button" aria-label="Fechar menu">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    <nav aria-label="Seções">
        <p class="nav-titulo">Painel</p>

        <a href="#inicio" class="nav-link" data-view="inicio">
            <i class="fas fa-chart-simple" aria-hidden="true"></i><span>Dashboard</span>
        </a>

        <p class="nav-titulo">Gerenciar</p>

        <a href="#usuarios" class="nav-link" data-view="usuarios">
            <i class="fas fa-users" aria-hidden="true"></i><span>Usuários</span>
        </a>
        <a href="#armarios" class="nav-link" data-view="armarios">
            <i class="fas fa-table-cells-large" aria-hidden="true"></i><span>Armários</span>
        </a>
    </nav>

    <div class="sidebar-rodape">
        <div class="sidebar-status ativo">
            <span class="led" aria-hidden="true"></span>
            <div>
                <strong>Modo administrador</strong>
                <small>Alterações valem na hora</small>
            </div>
        </div>

        <a href="../logout.php" class="logout">
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
            <strong id="tituloTopo">Dashboard</strong>
        </div>

        <button class="btn btn-secundario btn-topo" id="btnAtualizar" type="button">
            <i class="fas fa-rotate" aria-hidden="true"></i><span>Atualizar</span>
        </button>

        <div class="usuario">
            <button class="usuario-chip" id="btnUsuario" type="button"
                aria-haspopup="menu" aria-expanded="false" aria-controls="menuUsuario">
                <span class="avatar" aria-hidden="true"><?= htmlspecialchars($inicial) ?></span>
                <span class="usuario-texto">
                    <strong><?= htmlspecialchars($nome) ?></strong>
                    <small>Administrador</small>
                </span>
                <i class="fas fa-chevron-down seta" aria-hidden="true"></i>
            </button>

            <div class="menu-usuario" id="menuUsuario" role="menu" hidden>
                <div class="menu-usuario-cabecalho">
                    <strong><?= htmlspecialchars($nome) ?></strong>
                    <small><?= htmlspecialchars($conta['email']) ?></small>
                </div>
                <a href="../logout.php" role="menuitem" class="sair">
                    <i class="fas fa-right-from-bracket" aria-hidden="true"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <main class="main" id="conteudo" tabindex="-1">

        <!-- ============ DASHBOARD ============ -->
        <section class="view" id="inicio" aria-labelledby="tituloInicio">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloInicio">Visão geral</h1>
                    <p>Números do sistema, direto do banco de dados.</p>
                </div>
            </div>

            <div class="stats">
                <article class="stat">
                    <div class="stat-icone escuro"><i class="fas fa-users" aria-hidden="true"></i></div>
                    <div>
                        <p>Usuários cadastrados</p>
                        <h3 id="nUsuarios">–</h3>
                        <small id="nUsuariosSub"></small>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone vermelho"><i class="fas fa-shield-halved" aria-hidden="true"></i></div>
                    <div>
                        <p>Administradores</p>
                        <h3 id="nAdmins">–</h3>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone cinza"><i class="fas fa-table-cells-large" aria-hidden="true"></i></div>
                    <div>
                        <p>Armários</p>
                        <h3 id="nArmarios">–</h3>
                        <small id="nArmariosSub"></small>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone verde"><i class="fas fa-lock-open" aria-hidden="true"></i></div>
                    <div>
                        <p>Armários disponíveis</p>
                        <h3 id="nLivres">–</h3>
                    </div>
                </article>
            </div>

            <div class="stats">
                <article class="stat">
                    <div class="stat-icone vermelho"><i class="fas fa-lock" aria-hidden="true"></i></div>
                    <div>
                        <p>Armários ocupados</p>
                        <h3 id="nOcupados">–</h3>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone escuro"><i class="fas fa-calendar-check" aria-hidden="true"></i></div>
                    <div>
                        <p>Reservas ativas</p>
                        <h3 id="nAtivas">–</h3>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone cinza"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></div>
                    <div>
                        <p>Total de reservas</p>
                        <h3 id="nReservas">–</h3>
                        <small id="nReservasSub"></small>
                    </div>
                </article>
                <article class="stat">
                    <div class="stat-icone verde"><i class="fas fa-door-open" aria-hidden="true"></i></div>
                    <div>
                        <p>Acessos liberados hoje</p>
                        <h3 id="nAcessos">–</h3>
                    </div>
                </article>
            </div>

            <section class="painel" aria-labelledby="tituloUltimas">
                <div class="painel-topo">
                    <div>
                        <h2 id="tituloUltimas">Últimas reservas</h2>
                        <p>As 8 reservas mais recentes de todos os usuários</p>
                    </div>
                </div>
                <div class="tabela-rolagem">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th scope="col">Armário</th>
                                <th scope="col">Usuário</th>
                                <th scope="col" class="col-opcional">Data</th>
                                <th scope="col">Horário</th>
                                <th scope="col">Situação</th>
                            </tr>
                        </thead>
                        <tbody id="corpoUltimas">
                            <tr class="tabela-vazia"><td colspan="5">Carregando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

        <!-- ============ USUÁRIOS ============ -->
        <section class="view" id="usuarios" aria-labelledby="tituloUsuarios">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloUsuarios">Usuários</h1>
                    <p>Edite dados, vincule chaveiros RFID e bloqueie contas.</p>
                </div>
            </div>

            <section class="painel" aria-label="Lista de usuários">
                <div class="barra-ferramentas">
                    <div class="busca">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <label class="sr-only" for="buscaUsuario">Buscar usuário</label>
                        <input type="search" id="buscaUsuario" placeholder="Buscar por nome, e-mail ou CPF" autocomplete="off">
                    </div>

                    <div class="segmentos" role="group" aria-label="Filtrar contas" id="filtrosUsuario">
                        <button type="button" class="segmento ativo" data-filtro="todos" aria-pressed="true">Todos</button>
                        <button type="button" class="segmento" data-filtro="usuario" aria-pressed="false">Usuários</button>
                        <button type="button" class="segmento" data-filtro="admin" aria-pressed="false">Admins</button>
                        <button type="button" class="segmento" data-filtro="bloqueado" aria-pressed="false">Bloqueados</button>
                    </div>
                </div>

                <div class="tabela-rolagem">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th scope="col">Usuário</th>
                                <th scope="col" class="col-opcional">Perfil</th>
                                <th scope="col" class="col-opcional">Chaveiro RFID</th>
                                <th scope="col" class="col-opcional">Reservas</th>
                                <th scope="col"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody id="corpoUsuarios">
                            <tr class="tabela-vazia"><td colspan="5">Carregando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

        <!-- ============ ARMÁRIOS ============ -->
        <section class="view" id="armarios" aria-labelledby="tituloArmarios">

            <div class="view-cabecalho">
                <div>
                    <h1 id="tituloArmarios">Armários</h1>
                    <p>O número do armário deve ser o mesmo configurado no ESP32 da porta.</p>
                </div>
                <button class="btn btn-primario" type="button" id="btnNovoArmario">
                    <i class="fas fa-plus" aria-hidden="true"></i> Novo armário
                </button>
            </div>

            <section class="painel so-tabela" aria-label="Lista de armários">
                <div class="tabela-rolagem">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th scope="col">Armário</th>
                                <th scope="col">Situação</th>
                                <th scope="col" class="col-opcional">Abertura</th>
                                <th scope="col" class="col-opcional">Em uso por</th>
                                <th scope="col" class="col-opcional">Reservas</th>
                                <th scope="col"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody id="corpoArmarios">
                            <tr class="tabela-vazia"><td colspan="6">Carregando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

    </main>
</div>


<!-- ================= MODAL: EDITAR USUÁRIO ================= -->
<div class="modal" id="modalUsuario" role="dialog" aria-modal="true" aria-labelledby="tituloModalUsuario" hidden>
    <div class="modal-conteudo">
        <button class="btn-icone modal-fechar" type="button" data-fechar="modalUsuario" aria-label="Fechar">
            <i class="fas fa-xmark"></i>
        </button>

        <h2 id="tituloModalUsuario">Editar usuário</h2>
        <p class="modal-sub" id="subModalUsuario"></p>

        <form class="form-admin" id="formUsuario" novalidate>
            <input type="hidden" id="usuarioId">
            <div class="campo campo-full">
                <label for="usuarioNome">Nome completo</label>
                <input type="text" id="usuarioNome" maxlength="100" required>
            </div>
            <div class="campo campo-full">
                <label for="usuarioEmail">E-mail</label>
                <input type="email" id="usuarioEmail" maxlength="150" required>
            </div>
            <div class="campo">
                <label for="usuarioCpf">CPF</label>
                <input type="text" id="usuarioCpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00">
            </div>
            <div class="campo">
                <label for="usuarioTelefone">Telefone</label>
                <input type="tel" id="usuarioTelefone" maxlength="20" placeholder="(19) 99999-9999">
            </div>
            <div class="campo campo-full">
                <label for="usuarioRfid">Chaveiro RFID (UID)</label>
                <input type="text" id="usuarioRfid" class="mono" maxlength="20" placeholder="Ex.: A1B2C3D4" autocomplete="off">
                <span class="dica">Deixe em branco para desvincular. O UID aparece no leitor ou em logs_acesso.</span>
            </div>
        </form>

        <div class="aviso aviso-neutro aviso-perfil">
            <i class="fas fa-shield-halved" aria-hidden="true"></i>
            <span>O perfil (usuário/administrador) não é alterado por aqui. Isso só pode ser feito direto no banco de dados.</span>
        </div>

        <div class="mensagem" id="mensagemUsuario" role="alert"></div>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalUsuario">Cancelar</button>
            <button class="btn btn-primario" type="submit" form="formUsuario" id="btnSalvarUsuario">
                <i class="fas fa-check" aria-hidden="true"></i> Salvar
            </button>
        </div>
    </div>
</div>


<!-- ================= MODAL: ARMÁRIO ================= -->
<div class="modal" id="modalArmario" role="dialog" aria-modal="true" aria-labelledby="tituloModalArmario" hidden>
    <div class="modal-conteudo">
        <button class="btn-icone modal-fechar" type="button" data-fechar="modalArmario" aria-label="Fechar">
            <i class="fas fa-xmark"></i>
        </button>

        <h2 id="tituloModalArmario">Novo armário</h2>
        <p class="modal-sub">“Ocupado” é calculado pelas reservas; aqui você escolhe entre disponível e manutenção.</p>

        <form class="form-admin" id="formArmario" novalidate>
            <input type="hidden" id="armarioId" value="0">
            <div class="campo">
                <label for="armarioNumero">Número</label>
                <input type="number" id="armarioNumero" min="1" max="999" required>
            </div>
            <div class="campo">
                <label for="armarioStatus">Situação</label>
                <select id="armarioStatus">
                    <option value="livre">Disponível</option>
                    <option value="manutencao">Manutenção</option>
                </select>
            </div>
            <div class="campo campo-full">
                <label for="armarioLocal">Localização</label>
                <input type="text" id="armarioLocal" maxlength="100" placeholder="Ex.: Bloco A, térreo">
            </div>
            <fieldset class="campo campo-full opcoes-metodo">
                <legend>Como o armário abre</legend>
                <label class="opcao-metodo">
                    <input type="radio" name="armarioMetodo" value="qrcode" checked>
                    <span class="opcao-metodo-corpo">
                        <i class="fas fa-qrcode" aria-hidden="true"></i>
                        <strong>QR Code</strong>
                        <small>O usuário mostra o QR da reserva no leitor.</small>
                    </span>
                </label>
                <label class="opcao-metodo">
                    <input type="radio" name="armarioMetodo" value="rfid">
                    <span class="opcao-metodo-corpo">
                        <i class="fas fa-id-card" aria-hidden="true"></i>
                        <strong>Chaveiro RFID</strong>
                        <small>Só quem tem chaveiro vinculado pode reservar.</small>
                    </span>
                </label>
                <span class="dica" id="dicaMetodo">Não dá para trocar enquanto houver reserva ativa.</span>
            </fieldset>
        </form>

        <div class="mensagem" id="mensagemArmario" role="alert"></div>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalArmario">Cancelar</button>
            <button class="btn btn-primario" type="submit" form="formArmario" id="btnSalvarArmario">
                <i class="fas fa-check" aria-hidden="true"></i> Salvar
            </button>
        </div>
    </div>
</div>


<!-- ================= MODAL: CONFIRMAR ================= -->
<div class="modal" id="modalConfirmar" role="alertdialog" aria-modal="true"
    aria-labelledby="tituloConfirmar" aria-describedby="textoConfirmar" hidden>
    <div class="modal-conteudo pequeno">
        <div class="modal-icone"><i class="fas fa-triangle-exclamation" aria-hidden="true" id="iconeConfirmar"></i></div>
        <h2 id="tituloConfirmar">Confirmar</h2>
        <p class="modal-sub" id="textoConfirmar"></p>

        <div class="acoes-modal">
            <button class="btn btn-secundario" type="button" data-fechar="modalConfirmar">Voltar</button>
            <button class="btn btn-perigo-cheio" type="button" id="btnConfirmar">Confirmar</button>
        </div>
    </div>
</div>

<div class="toasts" id="toasts" aria-live="polite" aria-atomic="false"></div>

<script>
(function () {
    'use strict';

    var API = '../api/admin/';
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var TITULOS = { inicio: 'Dashboard', usuarios: 'Usuários', armarios: 'Armários' };

    var estado = { view: null, filtro: 'todos', busca: '', usuarios: [], armarios: [] };

    function $(id) { return document.getElementById(id); }

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function dataBR(iso) {
        if (!iso) { return '-'; }
        var p = String(iso).substr(0, 10).split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function cpfFormatado(cpf) {
        var d = String(cpf || '').replace(/\D/g, '');
        return d.length === 11 ? d.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : (cpf || '');
    }

    function classeStatus(s) {
        return { 'Ativa': 'ativa', 'Concluída': 'concluida', 'Cancelada': 'cancelada' }[s] || '';
    }

    /* =====================================================
       API — GET sem corpo, POST com JSON + token CSRF
    ===================================================== */
    function api(caminho, corpo) {
        var opcoes = { credentials: 'same-origin', headers: { 'Accept': 'application/json' } };
        if (corpo !== undefined) {
            opcoes.method = 'POST';
            opcoes.headers['Content-Type'] = 'application/json';
            opcoes.headers['X-CSRF-Token'] = CSRF;
            opcoes.body = JSON.stringify(corpo);
        }

        return fetch(API + caminho, opcoes).then(function (resp) {
            if (resp.status === 401) {
                location.href = '../login.php?expirou=1';
                throw new Error('sessao');
            }
            return resp.json().catch(function () {
                return { ok: false, erro: 'Resposta inválida do servidor.' };
            }).then(function (json) {
                if (!json.ok) {
                    var erro = new Error(json.erro || 'Não foi possível concluir a ação.');
                    erro.daApi = true;
                    throw erro;
                }
                return json;
            });
        });
    }

    function mensagemDeErro(e) {
        return e && e.daApi ? e.message : 'Sem conexão com o servidor. Verifique a internet e tente de novo.';
    }

    /* =====================================================
       TOASTS
    ===================================================== */
    function toast(texto, tipo) {
        tipo = tipo || 'info';
        var icones = { sucesso: 'fa-circle-check', erro: 'fa-circle-exclamation', info: 'fa-circle-info' };
        var el = document.createElement('div');
        el.className = 'toast ' + tipo;
        el.innerHTML = '<i class="fas ' + icones[tipo] + '" aria-hidden="true"></i><span>' + esc(texto) + '</span>';
        $('toasts').appendChild(el);
        setTimeout(function () {
            el.classList.add('saindo');
            setTimeout(function () { el.remove(); }, 320);
        }, 4500);
    }

    /* =====================================================
       MODAIS
    ===================================================== */
    var focoAnterior = null;
    var SELETOR_FOCAVEL = 'button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, a[href], [tabindex]:not([tabindex="-1"])';

    function abrirModal(id, focarEm) {
        focoAnterior = document.activeElement;
        var modal = $(id);
        modal.hidden = false;
        void modal.offsetWidth;
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
        if (!document.querySelector('.modal.aberto')) { document.body.classList.remove('travado'); }
        if (focoAnterior && document.contains(focoAnterior)) { focoAnterior.focus(); }
    }

    document.querySelectorAll('[data-fechar]').forEach(function (b) {
        b.addEventListener('click', function () { fecharModal(this.getAttribute('data-fechar')); });
    });

    document.querySelectorAll('.modal').forEach(function (m) {
        m.addEventListener('mousedown', function (e) { if (e.target === m) { fecharModal(m.id); } });
        m.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { fecharModal(m.id); return; }
            if (e.key !== 'Tab') { return; }
            var focaveis = Array.prototype.filter.call(m.querySelectorAll(SELETOR_FOCAVEL), function (el) {
                return el.offsetParent !== null;
            });
            if (!focaveis.length) { return; }
            var primeiro = focaveis[0], ultimo = focaveis[focaveis.length - 1];
            if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
            else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
        });
    });

    function mensagem(id, texto) {
        var el = $(id);
        el.textContent = texto || '';
        el.className = 'mensagem' + (texto ? ' erro' : '');
    }

    function carregandoBotao(btn, sim) {
        btn.disabled = sim;
        btn.classList.toggle('carregando', sim);
    }

    /* Modal de confirmação genérico: devolve uma Promise que resolve no "Confirmar". */
    var acaoConfirmada = null;
    function confirmar(titulo, texto, botao, icone) {
        $('tituloConfirmar').textContent = titulo;
        $('textoConfirmar').textContent = texto;
        $('btnConfirmar').textContent = botao;
        $('iconeConfirmar').className = 'fas ' + (icone || 'fa-triangle-exclamation');
        abrirModal('modalConfirmar', 'btnConfirmar');
        return new Promise(function (resolve) { acaoConfirmada = resolve; });
    }

    $('btnConfirmar').addEventListener('click', function () {
        var resolver = acaoConfirmada;
        acaoConfirmada = null;
        fecharModal('modalConfirmar');
        if (resolver) { resolver(); }
    });

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

        document.querySelectorAll('.view').forEach(function (v) { v.classList.toggle('ativa', v.id === destino); });
        links.forEach(function (l) {
            var ativo = l.getAttribute('data-view') === destino;
            l.classList.toggle('active', ativo);
            if (ativo) { l.setAttribute('aria-current', 'page'); } else { l.removeAttribute('aria-current'); }
        });

        $('tituloTopo').textContent = TITULOS[destino];
        document.title = TITULOS[destino] + ' · SmartLocker Admin';
        if (atualizarHash !== false && location.hash !== '#' + destino) {
            history.replaceState(null, '', '#' + destino);
        }
        fecharMenu();
        fecharMenuUsuario();
        if (mudou) {
            window.scrollTo(0, 0);
            carregarView(destino);
        }
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            irPara(this.getAttribute('data-view'));
            $('conteudo').focus({ preventScroll: true });
        });
    });

    $('btnMenu').addEventListener('click', abrirMenu);
    $('btnFecharMenu').addEventListener('click', fecharMenu);
    overlay.addEventListener('click', fecharMenu);
    window.addEventListener('hashchange', function () { irPara(location.hash.replace('#', ''), false); });

    /* Menu do usuário (topo) */
    var btnUsuario = $('btnUsuario');
    var menuUsuario = $('menuUsuario');
    function fecharMenuUsuario() {
        if (menuUsuario.hidden) { return; }
        menuUsuario.hidden = true;
        btnUsuario.setAttribute('aria-expanded', 'false');
    }
    btnUsuario.addEventListener('click', function (e) {
        e.stopPropagation();
        var abrir = menuUsuario.hidden;
        menuUsuario.hidden = !abrir;
        btnUsuario.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) { if (!e.target.closest('.usuario')) { fecharMenuUsuario(); } });

    $('btnAtualizar').addEventListener('click', function () {
        carregarView(estado.view).then(function (ok) { if (ok) { toast('Dados atualizados.', 'info'); } });
    });

    function carregarView(view) {
        if (view === 'usuarios') { return carregarUsuarios(); }
        if (view === 'armarios') { return carregarArmarios(); }
        return carregarResumo();
    }

    function falhaAoCarregar(corpoId, colunas) {
        return function (e) {
            if (e.message === 'sessao') { return false; }
            $(corpoId).innerHTML = '<tr class="tabela-vazia"><td colspan="' + colunas + '">' + esc(mensagemDeErro(e)) + '</td></tr>';
            toast(mensagemDeErro(e), 'erro');
            return false;
        };
    }

    /* =====================================================
       DASHBOARD
    ===================================================== */
    function carregarResumo() {
        return api('resumo.php').then(function (d) {
            $('nUsuarios').textContent = d.usuarios.total;
            $('nUsuariosSub').textContent = d.usuarios.comuns + ' comuns · ' + d.usuarios.bloqueados + ' bloqueados';
            $('nAdmins').textContent = d.usuarios.admins;
            $('nArmarios').textContent = d.armarios.total;
            $('nArmariosSub').textContent = d.armarios.manutencao + ' em manutenção';
            $('nLivres').textContent = d.armarios.livres;
            $('nOcupados').textContent = d.armarios.ocupados;
            $('nAtivas').textContent = d.reservas.ativas;
            $('nReservas').textContent = d.reservas.total;
            $('nReservasSub').textContent = d.reservas.concluidas + ' concluídas · ' + d.reservas.canceladas + ' canceladas';
            $('nAcessos').textContent = d.acessos_hoje;

            $('corpoUltimas').innerHTML = d.ultimas_reservas.length ? d.ultimas_reservas.map(function (r) {
                return '<tr>' +
                    '<td><strong>' + esc(r.armario) + '</strong></td>' +
                    '<td>' + esc(r.usuario) + '<span class="sub">' + esc(r.email) + '</span></td>' +
                    '<td class="col-opcional">' + dataBR(r.data) + '</td>' +
                    '<td class="mono">' + esc(r.inicio) + ' – ' + esc(r.fim) + '</td>' +
                    '<td><span class="badge ' + classeStatus(r.status) + '">' + esc(r.status) + '</span></td>' +
                    '</tr>';
            }).join('') : '<tr class="tabela-vazia"><td colspan="5">Nenhuma reserva feita ainda.</td></tr>';
            return true;
        }).catch(falhaAoCarregar('corpoUltimas', 5));
    }

    /* =====================================================
       USUÁRIOS
    ===================================================== */
    var timerBusca = null;

    function carregarUsuarios() {
        var q = 'usuarios.php?filtro=' + encodeURIComponent(estado.filtro) + '&busca=' + encodeURIComponent(estado.busca);
        return api(q).then(function (d) {
            estado.usuarios = d.usuarios;
            desenharUsuarios();
            return true;
        }).catch(falhaAoCarregar('corpoUsuarios', 5));
    }

    function desenharUsuarios() {
        var lista = estado.usuarios;
        if (!lista.length) {
            $('corpoUsuarios').innerHTML = '<tr class="tabela-vazia"><td colspan="5">Nenhum usuário encontrado.</td></tr>';
            return;
        }

        $('corpoUsuarios').innerHTML = lista.map(function (u) {
            var selos = '<span class="badge ' + u.perfil + '">' + (u.perfil === 'admin' ? 'Administrador' : 'Usuário') + '</span>';
            if (u.bloqueado) { selos += '<span class="badge bloqueado"><i class="fas fa-ban" aria-hidden="true"></i>Bloqueado</span>'; }
            if (u.eu) { selos += '<span class="badge voce">Você</span>'; }

            var protegido = u.eu || u.perfil === 'admin';
            var botaoBloqueio = u.bloqueado
                ? '<button class="btn-icone" type="button" data-acao="desbloquear" data-id="' + u.id + '" title="Desbloquear" aria-label="Desbloquear ' + esc(u.nome) + '"><i class="fas fa-lock-open"></i></button>'
                : '<button class="btn-icone perigo" type="button" data-acao="bloquear" data-id="' + u.id + '" title="' + (protegido ? 'Não é possível bloquear esta conta' : 'Bloquear') + '" aria-label="Bloquear ' + esc(u.nome) + '"' + (protegido ? ' disabled' : '') + '><i class="fas fa-ban"></i></button>';

            return '<tr' + (u.bloqueado ? ' class="linha-bloqueada"' : '') + '>' +
                '<td><div class="pessoa"><span class="avatar" aria-hidden="true">' + esc(u.nome.charAt(0).toUpperCase()) + '</span><div>' +
                    '<strong>' + esc(u.nome) + '</strong><small>' + esc(u.email) + '</small>' +
                    (u.cpf ? '<small>CPF ' + esc(cpfFormatado(u.cpf)) + '</small>' : '') +
                    '<div class="selos so-celular">' + selos + '</div>' +
                '</div></div></td>' +
                '<td class="col-opcional"><div class="selos">' + selos + '</div></td>' +
                '<td class="col-opcional">' + (u.rfid_uid ? '<span class="mono">' + esc(u.rfid_uid) + '</span>' : '<span class="sub">Sem chaveiro</span>') + '</td>' +
                '<td class="col-opcional">' + u.total_reservas +
                    (u.armario_ativo ? '<span class="sub">Ativa: ' + esc(u.armario_ativo) + '</span>' : '') +
                    '<span class="sub">Desde ' + dataBR(u.cliente_desde) + '</span></td>' +
                '<td><div class="acoes">' +
                    '<button class="btn-icone" type="button" data-acao="editar" data-id="' + u.id + '" title="Editar" aria-label="Editar ' + esc(u.nome) + '"><i class="fas fa-pen"></i></button>' +
                    botaoBloqueio +
                    '<button class="btn-icone perigo" type="button" data-acao="excluir" data-id="' + u.id + '" title="' + (u.pode_excluir && !u.eu ? 'Excluir' : 'Contas com histórico não podem ser excluídas') + '" aria-label="Excluir ' + esc(u.nome) + '"' + (u.pode_excluir && !u.eu ? '' : ' disabled') + '><i class="fas fa-trash"></i></button>' +
                '</div></td>' +
                '</tr>';
        }).join('');
    }

    $('buscaUsuario').addEventListener('input', function () {
        var valor = this.value.trim();
        clearTimeout(timerBusca);
        timerBusca = setTimeout(function () {
            estado.busca = valor;
            carregarUsuarios();
        }, 300);
    });

    document.querySelectorAll('#filtrosUsuario .segmento').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('#filtrosUsuario .segmento').forEach(function (x) {
                x.classList.toggle('ativo', x === b);
                x.setAttribute('aria-pressed', x === b ? 'true' : 'false');
            });
            estado.filtro = b.getAttribute('data-filtro');
            carregarUsuarios();
        });
    });

    function acharUsuario(id) {
        return estado.usuarios.filter(function (u) { return u.id === id; })[0];
    }

    function acaoUsuario(corpo) {
        return api('usuarios_acao.php', corpo).then(function (d) {
            toast(d.mensagem, 'sucesso');
            carregarUsuarios();
            return d;
        });
    }

    $('corpoUsuarios').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-acao]');
        if (!b || b.disabled) { return; }
        var u = acharUsuario(Number(b.getAttribute('data-id')));
        if (!u) { return; }
        var acao = b.getAttribute('data-acao');

        if (acao === 'editar') { abrirEdicaoUsuario(u); return; }

        if (acao === 'bloquear') {
            confirmar('Bloquear ' + u.nome + '?',
                'A pessoa sai do site e do app, não consegue mais entrar e o chaveiro deixa de abrir as portas.' +
                (u.armario_ativo ? ' A reserva ativa no ' + u.armario_ativo + ' será cancelada.' : ''),
                'Sim, bloquear', 'fa-ban'
            ).then(function () {
                acaoUsuario({ acao: 'bloquear', id: u.id }).catch(function (er) { toast(mensagemDeErro(er), 'erro'); });
            });
        }

        if (acao === 'desbloquear') {
            acaoUsuario({ acao: 'desbloquear', id: u.id }).catch(function (er) { toast(mensagemDeErro(er), 'erro'); });
        }

        if (acao === 'excluir') {
            confirmar('Excluir ' + u.nome + '?', 'A conta será apagada de vez. Esta ação não pode ser desfeita.', 'Sim, excluir', 'fa-trash')
                .then(function () {
                    acaoUsuario({ acao: 'excluir', id: u.id }).catch(function (er) { toast(mensagemDeErro(er), 'erro'); });
                });
        }
    });

    function abrirEdicaoUsuario(u) {
        $('usuarioId').value = u.id;
        $('usuarioNome').value = u.nome;
        $('usuarioEmail').value = u.email;
        $('usuarioCpf').value = cpfFormatado(u.cpf);
        $('usuarioTelefone').value = u.telefone;
        $('usuarioRfid').value = u.rfid_uid;
        $('subModalUsuario').textContent = (u.perfil === 'admin' ? 'Administrador' : 'Usuário') + ' · cliente desde ' + dataBR(u.cliente_desde);
        mensagem('mensagemUsuario', '');
        abrirModal('modalUsuario', 'usuarioNome');
    }

    $('formUsuario').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = $('btnSalvarUsuario');
        mensagem('mensagemUsuario', '');
        carregandoBotao(btn, true);

        api('usuarios_acao.php', {
            acao: 'salvar',
            id: Number($('usuarioId').value),
            nome: $('usuarioNome').value,
            email: $('usuarioEmail').value,
            cpf: $('usuarioCpf').value,
            telefone: $('usuarioTelefone').value,
            rfid_uid: $('usuarioRfid').value
        }).then(function (d) {
            fecharModal('modalUsuario');
            toast(d.mensagem, 'sucesso');
            carregarUsuarios();
        }).catch(function (er) {
            mensagem('mensagemUsuario', mensagemDeErro(er));
        }).then(function () {
            carregandoBotao(btn, false);
        });
    });

    /* =====================================================
       ARMÁRIOS
    ===================================================== */
    function carregarArmarios() {
        return api('armarios.php').then(function (d) {
            estado.armarios = d.armarios;
            desenharArmarios();
            return true;
        }).catch(falhaAoCarregar('corpoArmarios', 6));
    }

    function seloArmario(s) {
        if (s === 'manutencao') { return '<span class="badge manutencao"><i class="fas fa-screwdriver-wrench" aria-hidden="true"></i>Manutenção</span>'; }
        if (s === 'ocupado') { return '<span class="badge ativa"><i class="fas fa-lock" aria-hidden="true"></i>Ocupado</span>'; }
        return '<span class="badge livre"><i class="fas fa-lock-open" aria-hidden="true"></i>Disponível</span>';
    }

    function seloMetodo(m) {
        return m === 'rfid'
            ? '<span class="badge metodo"><i class="fas fa-id-card" aria-hidden="true"></i>Chaveiro RFID</span>'
            : '<span class="badge metodo"><i class="fas fa-qrcode" aria-hidden="true"></i>QR Code</span>';
    }

    function desenharArmarios() {
        var lista = estado.armarios;
        if (!lista.length) {
            $('corpoArmarios').innerHTML = '<tr class="tabela-vazia"><td colspan="6">Nenhum armário cadastrado. Clique em “Novo armário”.</td></tr>';
            return;
        }

        $('corpoArmarios').innerHTML = lista.map(function (a) {
            var emManutencao = a.status === 'manutencao';
            var uso = a.ocupante
                ? esc(a.ocupante) + '<span class="sub">' + dataBR(a.reserva.data) + ' · ' + esc(a.reserva.inicio) + ' – ' + esc(a.reserva.fim) + '</span>'
                : '<span class="sub">—</span>';

            return '<tr>' +
                '<td><strong>' + esc(a.nome) + '</strong><span class="sub">' + esc(a.localizacao || 'Sem localização') + '</span></td>' +
                '<td>' + seloArmario(a.status) + '<span class="so-celular">' + seloMetodo(a.metodo_acesso) + '</span></td>' +
                '<td class="col-opcional">' + seloMetodo(a.metodo_acesso) + '</td>' +
                '<td class="col-opcional">' + uso + '</td>' +
                '<td class="col-opcional">' + a.total_reservas + '</td>' +
                '<td><div class="acoes">' +
                    '<button class="btn-icone" type="button" data-acao="editar" data-id="' + a.id + '" title="Editar" aria-label="Editar ' + esc(a.nome) + '"><i class="fas fa-pen"></i></button>' +
                    (emManutencao
                        ? '<button class="btn-icone" type="button" data-acao="liberar" data-id="' + a.id + '" title="Liberar para reservas" aria-label="Liberar ' + esc(a.nome) + '"><i class="fas fa-circle-check"></i></button>'
                        : '<button class="btn-icone" type="button" data-acao="manutencao" data-id="' + a.id + '" title="' + (a.ocupante ? 'Ocupado: aguarde a reserva terminar' : 'Colocar em manutenção') + '" aria-label="Colocar ' + esc(a.nome) + ' em manutenção"' + (a.ocupante ? ' disabled' : '') + '><i class="fas fa-screwdriver-wrench"></i></button>') +
                    '<button class="btn-icone perigo" type="button" data-acao="excluir" data-id="' + a.id + '" title="' + (a.pode_excluir ? 'Remover' : 'Armários com histórico não podem ser removidos') + '" aria-label="Remover ' + esc(a.nome) + '"' + (a.pode_excluir ? '' : ' disabled') + '><i class="fas fa-trash"></i></button>' +
                '</div></td>' +
                '</tr>';
        }).join('');
    }

    function acharArmario(id) {
        return estado.armarios.filter(function (a) { return a.id === id; })[0];
    }

    function acaoArmario(corpo) {
        return api('armarios_acao.php', corpo).then(function (d) {
            toast(d.mensagem, 'sucesso');
            carregarArmarios();
            return d;
        }).catch(function (er) { toast(mensagemDeErro(er), 'erro'); });
    }

    function abrirArmario(a) {
        $('armarioId').value = a ? a.id : 0;
        $('armarioNumero').value = a ? a.numero : (estado.armarios.reduce(function (m, x) { return Math.max(m, x.numero); }, 0) + 1);
        $('armarioLocal').value = a ? a.localizacao : '';
        $('armarioStatus').value = a && a.status === 'manutencao' ? 'manutencao' : 'livre';
        var metodo = a ? a.metodo_acesso : 'qrcode';
        var travado = !!(a && a.ocupante);
        document.querySelectorAll('input[name="armarioMetodo"]').forEach(function (r) {
            r.checked = r.value === metodo;
            r.disabled = travado && r.value !== metodo;
        });
        $('dicaMetodo').textContent = travado
            ? 'Em uso por ' + a.ocupante + ': o tipo só pode mudar quando a reserva terminar.'
            : 'Não dá para trocar enquanto houver reserva ativa.';
        $('tituloModalArmario').textContent = a ? 'Editar ' + a.nome : 'Novo armário';
        mensagem('mensagemArmario', '');
        abrirModal('modalArmario', 'armarioNumero');
    }

    $('btnNovoArmario').addEventListener('click', function () { abrirArmario(null); });

    $('corpoArmarios').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-acao]');
        if (!b || b.disabled) { return; }
        var a = acharArmario(Number(b.getAttribute('data-id')));
        if (!a) { return; }
        var acao = b.getAttribute('data-acao');

        if (acao === 'editar') { abrirArmario(a); }
        if (acao === 'liberar') { acaoArmario({ acao: 'status', id: a.id, status: 'livre' }); }
        if (acao === 'manutencao') {
            confirmar('Colocar ' + a.nome + ' em manutenção?', 'Ninguém consegue reservar nem abrir a porta até você liberar de novo.', 'Sim, manutenção', 'fa-screwdriver-wrench')
                .then(function () { acaoArmario({ acao: 'status', id: a.id, status: 'manutencao' }); });
        }
        if (acao === 'excluir') {
            confirmar('Remover ' + a.nome + '?', 'O armário sai do sistema de vez. Esta ação não pode ser desfeita.', 'Sim, remover', 'fa-trash')
                .then(function () { acaoArmario({ acao: 'excluir', id: a.id }); });
        }
    });

    $('formArmario').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = $('btnSalvarArmario');
        mensagem('mensagemArmario', '');
        carregandoBotao(btn, true);

        api('armarios_acao.php', {
            acao: 'salvar',
            id: Number($('armarioId').value),
            numero: Number($('armarioNumero').value),
            localizacao: $('armarioLocal').value,
            status: $('armarioStatus').value,
            metodo_acesso: document.querySelector('input[name="armarioMetodo"]:checked').value
        }).then(function (d) {
            fecharModal('modalArmario');
            toast(d.mensagem, 'sucesso');
            carregarArmarios();
        }).catch(function (er) {
            mensagem('mensagemArmario', mensagemDeErro(er));
        }).then(function () {
            carregandoBotao(btn, false);
        });
    });

    /* =====================================================
       INÍCIO
    ===================================================== */
    var hoje = new Date();
    $('dataTopo').textContent = hoje.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long' });

    irPara(location.hash.replace('#', '') || 'inicio', false);

    /* Atualiza os números sozinho a cada 30 s enquanto o painel está aberto */
    setInterval(function () {
        if (document.hidden || document.querySelector('.modal.aberto')) { return; }
        carregarView(estado.view);
    }, 30000);
})();
</script>
</body>
</html>
