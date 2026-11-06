<?php
/*
 * POST api/logout.php  — encerra o login do app (apaga o token).
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('POST');

$token = tokenDaRequisicao();
if ($token !== '') {
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare('DELETE FROM api_tokens WHERE token_hash = ?');
    $stmt->bind_param('s', $hash);
    $stmt->execute();
}

responder(['mensagem' => 'Você saiu da conta.']);
