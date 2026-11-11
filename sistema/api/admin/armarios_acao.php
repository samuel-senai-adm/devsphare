<?php
/*
 * POST api/admin/armarios_acao.php
 *
 *   { "acao": "salvar",  "id": 0, "numero": 5, "localizacao": "Bloco B", "status": "livre",
 *     "metodo_acesso": "qrcode" | "rfid" }   (id 0 = novo)
 *   { "acao": "status",  "id": 5, "status": "manutencao" | "livre" }
 *   { "acao": "excluir", "id": 5 }
 *
 * "ocupado" não é escolhido à mão: o sistema calcula pelas reservas ativas.
 * O NÚMERO é o que o ESP32 manda em validar_acesso.php, então deve bater com a placa.
 */
require_once __DIR__ . '/_admin.php';

exigirMetodo('POST');

$acao = campo('acao');
$id = inteiro('id');

function buscarArmario(mysqli $conn, $id)
{
    $stmt = $conn->prepare('SELECT id, numero, localizacao, status, metodo_acesso FROM lockers WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    return $linha ?: null;
}

function temReservaAtiva(mysqli $conn, $id)
{
    return contar($conn, "SELECT COUNT(*) FROM reservas WHERE locker_id = ? AND status = 'ativa'", 'i', [$id]) > 0;
}

/* livre <-> manutenção (ocupado é calculado) */
function validarStatus($status)
{
    if ($status !== 'livre' && $status !== 'manutencao') {
        falhar('Situação inválida. Use "Disponível" ou "Manutenção".');
    }
}

$armario = null;
if ($acao !== 'salvar' || $id > 0) {
    $armario = buscarArmario($conn, $id);
    if (!$armario) {
        falhar('Armário não encontrado.', 404);
    }
}

switch ($acao) {

    /* ---------------- CADASTRAR / EDITAR ---------------- */
    case 'salvar':
        $numero = inteiro('numero');
        $local = preg_replace('/\s+/', ' ', campo('localizacao'));
        $status = campo('status', $armario ? ($armario['status'] === 'manutencao' ? 'manutencao' : 'livre') : 'livre');

        if ($numero < 1 || $numero > 999) {
            falhar('O número do armário deve estar entre 1 e 999.');
        }
        if (mb_strlen($local) > 100) {
            falhar('Localização muito longa (máximo de 100 caracteres).');
        }
        validarStatus($status);

        $metodo = campo('metodo_acesso', $armario ? $armario['metodo_acesso'] : '');
        if ($metodo !== 'qrcode' && $metodo !== 'rfid') {
            falhar('Escolha como o armário abre: QR Code ou chaveiro RFID.');
        }

        /* trocar o tipo com alguém usando deixaria a pessoa sem conseguir abrir */
        if ($armario && $metodo !== metodoArmario($armario['metodo_acesso']) && temReservaAtiva($conn, $id)) {
            falhar(nomeArmario($armario['numero']) . ' tem uma reserva ativa. Aguarde terminar ou cancele a reserva para trocar o tipo de abertura.', 409);
        }

        $stmt = $conn->prepare('SELECT id FROM lockers WHERE numero = ? AND id <> ?');
        $stmt->bind_param('ii', $numero, $id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            falhar('Já existe o ' . nomeArmario($numero) . '.', 409);
        }

        if ($armario && $status === 'manutencao' && $armario['status'] !== 'manutencao' && temReservaAtiva($conn, $id)) {
            falhar(nomeArmario($armario['numero']) . ' tem uma reserva ativa. Aguarde terminar ou cancele a reserva antes da manutenção.', 409);
        }

        if ($armario) {
            $stmt = $conn->prepare('UPDATE lockers SET numero = ?, localizacao = ?, status = ?, metodo_acesso = ? WHERE id = ?');
            $stmt->bind_param('isssi', $numero, $local, $status, $metodo, $id);
            $stmt->execute();
            $mensagem = nomeArmario($numero) . ' atualizado.';
        } else {
            $stmt = $conn->prepare('INSERT INTO lockers (numero, localizacao, status, metodo_acesso) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $numero, $local, $status, $metodo);
            $stmt->execute();
            $mensagem = nomeArmario($numero) . ' cadastrado.';
        }

        atualizarSituacao($conn);
        responder(['mensagem' => $mensagem], $armario ? 200 : 201);
        break;

    /* ---------------- MANUTENÇÃO / LIBERAR ---------------- */
    case 'status':
        $status = campo('status');
        validarStatus($status);

        if ($status === 'manutencao' && temReservaAtiva($conn, $id)) {
            falhar(nomeArmario($armario['numero']) . ' tem uma reserva ativa. Aguarde terminar ou cancele a reserva antes da manutenção.', 409);
        }

        $stmt = $conn->prepare('UPDATE lockers SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        atualizarSituacao($conn);

        responder(['mensagem' => nomeArmario($armario['numero']) .
            ($status === 'manutencao' ? ' em manutenção.' : ' liberado para reservas.')]);
        break;

    /* ---------------- REMOVER (só sem histórico) ---------------- */
    case 'excluir':
        $reservas = contar($conn, 'SELECT COUNT(*) FROM reservas WHERE locker_id = ?', 'i', [$id]);
        $acessos = contar($conn, 'SELECT COUNT(*) FROM logs_acesso WHERE locker_id = ?', 'i', [$id]);
        if ($reservas > 0 || $acessos > 0) {
            falhar(nomeArmario($armario['numero']) . ' tem histórico e não pode ser apagado. Coloque em manutenção.', 409);
        }

        $stmt = $conn->prepare('DELETE FROM lockers WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        responder(['mensagem' => nomeArmario($armario['numero']) . ' removido.']);
        break;

    default:
        falhar('Ação inválida.');
}
