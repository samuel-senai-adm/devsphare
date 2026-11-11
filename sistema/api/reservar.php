<?php
/*
 * POST api/reservar.php
 * Envia: { "locker_id": 5, "data": "2026-11-04", "inicio": "14:00", "fim": "15:30" }
 * Recebe: { "ok": true, "reserva": {...com "qr"} }
 *
 * Regras:
 *  - uma reserva ativa por usuário e uma por armário
 *  - início no futuro, término depois do início, no mesmo dia
 *  - duração entre 15 minutos e 12 horas, até 30 dias à frente
 *  - armário RFID só para quem tem chaveiro vinculado
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('POST');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$lockerId = (int) campo('locker_id', '0');
$data = campo('data');
$horaInicio = campo('inicio');
$horaFim = campo('fim');

if ($lockerId <= 0) {
    falhar('Escolha um armário.');
}

$inicio = DateTime::createFromFormat('Y-m-d H:i', $data . ' ' . $horaInicio);
$fim = DateTime::createFromFormat('Y-m-d H:i', $data . ' ' . $horaFim);

if (!$inicio || !$fim || $inicio->format('Y-m-d H:i') !== $data . ' ' . $horaInicio
    || $fim->format('Y-m-d H:i') !== $data . ' ' . $horaFim) {
    falhar('Data ou horário inválido. Use data AAAA-MM-DD e horas HH:MM.');
}

$agoraTs = time();
$duracaoMin = ($fim->getTimestamp() - $inicio->getTimestamp()) / 60;

if ($duracaoMin <= 0) {
    falhar('O horário de término precisa ser depois do horário de início.');
}
if ($inicio->getTimestamp() < $agoraTs - 60) {
    falhar('O horário de início precisa ser um horário futuro.');
}
if ($duracaoMin < 15) {
    falhar('A reserva precisa ter pelo menos 15 minutos.');
}
if ($duracaoMin > 12 * 60) {
    falhar('A reserva pode ter no máximo 12 horas.');
}
if ($inicio->getTimestamp() > strtotime('+30 days')) {
    falhar('Só é possível reservar com até 30 dias de antecedência.');
}

$usuarioId = (int) $usuario['id'];
$dataInicio = $inicio->format('Y-m-d H:i:s');
$dataFim = $fim->format('Y-m-d H:i:s');

/*
 * Transação com bloqueio de linha (FOR UPDATE): se duas pessoas clicarem no mesmo
 * armário ao mesmo tempo, a segunda espera a primeira terminar e recebe "ocupado".
 */
$conn->begin_transaction();

try {
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE id = ? FOR UPDATE');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $stmt->get_result();

    $stmt = $conn->prepare('SELECT id, numero, status, metodo_acesso FROM lockers WHERE id = ? FOR UPDATE');
    $stmt->bind_param('i', $lockerId);
    $stmt->execute();
    $locker = $stmt->get_result()->fetch_assoc();

    if (!$locker) {
        $conn->rollback();
        falhar('Armário não encontrado.', 404);
    }
    if ($locker['status'] === 'manutencao') {
        $conn->rollback();
        falhar(nomeArmario($locker['numero']) . ' está em manutenção.', 409);
    }
    if (metodoArmario($locker['metodo_acesso']) === 'rfid' && empty($usuario['rfid_uid'])) {
        $conn->rollback();
        falhar(nomeArmario($locker['numero']) . ' abre só com chaveiro RFID, e sua conta não tem chaveiro vinculado. ' .
            'Peça um chaveiro ao administrador ou escolha um armário com QR Code.', 409);
    }

    $stmt = $conn->prepare("SELECT id FROM reservas WHERE usuario_id = ? AND status = 'ativa' LIMIT 1");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $conn->rollback();
        falhar('Você já possui uma reserva ativa. Cancele ou aguarde ela terminar.', 409);
    }

    $stmt = $conn->prepare("SELECT id FROM reservas WHERE locker_id = ? AND status = 'ativa' LIMIT 1");
    $stmt->bind_param('i', $lockerId);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $conn->rollback();
        falhar(nomeArmario($locker['numero']) . ' acabou de ser reservado por outra pessoa. Escolha outro.', 409);
    }

    $qrToken = bin2hex(random_bytes(16));
    $criadaEm = agora();

    $stmt = $conn->prepare(
        "INSERT INTO reservas (usuario_id, locker_id, data_inicio, data_fim, status, qr_token, criada_em)
         VALUES (?, ?, ?, ?, 'ativa', ?, ?)"
    );
    $stmt->bind_param('iissss', $usuarioId, $lockerId, $dataInicio, $dataFim, $qrToken, $criadaEm);
    $stmt->execute();
    $reservaId = (int) $conn->insert_id;

    $stmt = $conn->prepare("UPDATE lockers SET status = 'ocupado' WHERE id = ?");
    $stmt->bind_param('i', $lockerId);
    $stmt->execute();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    throw $e;
}

$stmt = $conn->prepare(SQL_RESERVA . ' WHERE r.id = ?');
$stmt->bind_param('i', $reservaId);
$stmt->execute();

responder([
    'mensagem' => 'Reserva confirmada.',
    'reserva'  => formatarReserva($stmt->get_result()->fetch_assoc(), true),
], 201);
