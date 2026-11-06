<?php
/*
 * Controle de acesso das PÁGINAS do site (as rotas da API usam exigirLogin/exigirAdmin em api/_base.php).
 *
 * Uso no topo de uma página:
 *     require_once __DIR__ . '/../config/acesso.php';
 *     $conta = exigirAdminPagina($conn, '../');
 *
 * O perfil e o bloqueio são sempre lidos do banco, nunca da sessão ou do navegador:
 * se alguém virar admin, deixar de ser admin ou for bloqueado, vale na próxima página.
 */
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/conexao.php';

/* Conta logada (ou null). Conta bloqueada ou apagada encerra a sessão. */
function contaDaSessao(mysqli $conn, $raiz)
{
    iniciarSessao();
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    $id = (int) $_SESSION['usuario_id'];
    $stmt = $conn->prepare(
        'SELECT id, nome, email, perfil, bloqueado, rfid_uid, created_at FROM usuarios WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $conta = $stmt->get_result()->fetch_assoc();

    if (!$conta || !empty($conta['bloqueado'])) {
        encerrarSessao();
        header('Location: ' . $raiz . 'login.php' . ($conta ? '?bloqueado=1' : ''));
        exit;
    }
    return $conta;
}

function exigirLoginPagina(mysqli $conn, $raiz = '')
{
    $conta = contaDaSessao($conn, $raiz);
    if (!$conta) {
        header('Location: ' . $raiz . 'login.php');
        exit;
    }
    return $conta;
}

/*
 * Página só de administrador. Usuário comum que digitar a URL recebe HTTP 403
 * e é mandado de volta ao dashboard; nenhum dado administrativo é carregado.
 */
function exigirAdminPagina(mysqli $conn, $raiz = '')
{
    $conta = exigirLoginPagina($conn, $raiz);
    if ($conta['perfil'] !== 'admin') {
        http_response_code(403);
        header('Refresh: 0; url=' . $raiz . 'dashboard.php');
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Acesso negado</title>'
            . '<p style="font-family:sans-serif">Acesso restrito a administradores. '
            . '<a href="' . htmlspecialchars($raiz) . 'dashboard.php">Voltar ao dashboard</a></p>';
        exit;
    }
    return $conta;
}
