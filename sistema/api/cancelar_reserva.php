<?php
/*
 * POST api/cancelar_reserva.php
 * Envia: { "reserva_id": 12 }   (se não enviar, cancela a reserva ativa)
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('POST');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$usuarioId = (int) $usuario['id'];
$reservaId = (int) campo('reserva_id', '0');

if ($reservaId <= 0) {
    $ativa = reservaAtivaDoUsuario($conn, $usuarioId);
    if (!$ativa) {
        falhar('Você não possui uma reserva ativa.', 404);
    }
    $reservaId = (int) $ativa['id'];
}

$agora = agora();
$stmt = $conn->prepare(
    "UPDATE reservas SET status = 'cancelada', cancelada_em = ?
      WHERE id = ? AND usuario_id = ? AND status = 'ativa'"
);
$stmt->bind_param('sii', $agora, $reservaId, $usuarioId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    falhar('Esta reserva não está mais ativa.', 409);
}

atualizarSituacao($conn);

responder(['mensagem' => 'Reserva cancelada.']);
