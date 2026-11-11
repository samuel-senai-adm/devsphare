<?php
/*
 * POST api/login.php   (usado pelo app)
 * Envia: { "email": "...", "senha": "...", "dispositivo": "Tablet Samsung" }
 * Recebe: { "ok": true, "token": "...", "usuario": {...} }
 * Depois mande o token em todas as chamadas no cabeçalho  X-Auth-Token: <token>
 */
require_once __DIR__ . '/_base.php';
require_once __DIR__ . '/../model/Usuario.php';

exigirMetodo('POST');

$email = strtolower(campo('email'));
$senha = campo('senha');

if ($email === '' || $senha === '') {
    falhar('Informe e-mail e senha.');
}

$modelo = new Usuario($conn);
$usuario = $modelo->login($email, $senha);

if (!$usuario) {
    falhar('E-mail ou senha incorretos.', 401);
}

if (!empty($usuario['bloqueado'])) {
    falhar('Esta conta está bloqueada. Procure o administrador do SmartLocker.', 403);
}

$token = bin2hex(random_bytes(32));
$hash = hash('sha256', $token);
$dispositivo = mb_substr(campo('dispositivo', 'Aplicativo'), 0, 100);
$expira = date('Y-m-d H:i:s', strtotime('+' . (int) APP_TOKEN_DIAS . ' days'));
$id = (int) $usuario['id'];

// limpa tokens vencidos desse usuário
$agora = agora();
$stmt = $conn->prepare('DELETE FROM api_tokens WHERE usuario_id = ? AND expira_em <= ?');
$stmt->bind_param('is', $id, $agora);
$stmt->execute();

$stmt = $conn->prepare('INSERT INTO api_tokens (usuario_id, token_hash, dispositivo, expira_em) VALUES (?, ?, ?, ?)');
$stmt->bind_param('isss', $id, $hash, $dispositivo, $expira);
$stmt->execute();

responder([
    'token'   => $token,
    'expira'  => date('c', strtotime($expira)),
    'usuario' => usuarioPublico(buscarUsuario($conn, $id)),
]);
