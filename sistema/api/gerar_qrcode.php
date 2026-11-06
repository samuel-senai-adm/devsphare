<?php
/*
 * GET api/gerar_qrcode.php — conteúdo do QR Code da reserva ativa.
 * Recebe: { "qr": "SMARTLOCKER:<token>", "reserva": {...} }
 *
 * O QR leva só um código aleatório (não leva nome, data nem armário),
 * então não dá para falsificar um QR montando o texto à mão.
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('GET');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$reserva = reservaAtivaDoUsuario($conn, (int) $usuario['id']);
if (!$reserva) {
    falhar('Você não possui uma reserva ativa.', 404);
}

$dados = formatarReserva($reserva, true);
responder(['qr' => $dados['qr'], 'reserva' => $dados]);
