<?php
/*
 * GET api/minha_reserva.php — a reserva ativa de quem está logado (ou null).
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('GET');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$reserva = reservaAtivaDoUsuario($conn, (int) $usuario['id']);

responder(['reserva' => $reserva ? formatarReserva($reserva, true) : null]);
