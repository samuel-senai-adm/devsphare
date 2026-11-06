<?php
/*
 * Base da API administrativa (sistema/api/admin/*.php).
 *
 * Toda rota daqui inclui este arquivo, que:
 *  - carrega a base comum da API (conexão, JSON, login)
 *  - EXIGE um administrador logado pelo site; qualquer outro recebe HTTP 403
 *  - exige o cabeçalho X-CSRF-Token em toda requisição que altera dados
 *
 * Este arquivo começa com "_" e por isso não pode ser aberto pelo navegador (.htaccess).
 */
require_once __DIR__ . '/../_base.php';
require_once __DIR__ . '/../_acesso.php';

$admin = exigirAdmin($conn);
atualizarSituacao($conn);

function inteiro($nome)
{
    return (int) campo($nome, '0');
}

/* Converte "true", "1", "sim" em true. */
function booleano($nome)
{
    $v = entrada();
    if (!isset($v[$nome])) {
        return false;
    }
    return in_array(strtolower(trim((string) $v[$nome])), ['1', 'true', 'sim', 'on'], true);
}

function buscarConta(mysqli $conn, $id)
{
    $stmt = $conn->prepare('SELECT id, nome, email, perfil, bloqueado FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    return $linha ?: null;
}

/*
 * Bloqueia uma conta:
 *  - cancela as reservas ativas (o armário volta a ficar livre)
 *  - apaga os logins do app (o celular volta para a tela de login)
 * A sessão do site cai sozinha: toda página relê o bloqueio no banco.
 * O chaveiro RFID para de abrir porque validarAcesso() recusa conta bloqueada.
 */
function bloquearConta(mysqli $conn, $id)
{
    $agora = agora();
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('UPDATE usuarios SET bloqueado = 1, bloqueado_em = ? WHERE id = ?');
        $stmt->bind_param('si', $agora, $id);
        $stmt->execute();

        $stmt = $conn->prepare(
            "UPDATE reservas SET status = 'cancelada', cancelada_em = ? WHERE usuario_id = ? AND status = 'ativa'"
        );
        $stmt->bind_param('si', $agora, $id);
        $stmt->execute();
        $canceladas = $stmt->affected_rows;

        $stmt = $conn->prepare('DELETE FROM api_tokens WHERE usuario_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    atualizarSituacao($conn);
    return $canceladas;
}

function contar(mysqli $conn, $sql, $tipos = '', array $valores = [])
{
    $stmt = $conn->prepare($sql);
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$valores);
    }
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_row();
    return (int) $linha[0];
}
