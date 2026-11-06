<?php
/*
 * POST api/cadastro.php   (usado pelo app)
 * Envia: { "nome", "email", "senha", "cpf", "telefone" (opcional) }
 * Recebe: o mesmo que o login (já entra na conta)
 */
require_once __DIR__ . '/_base.php';
require_once __DIR__ . '/../model/Usuario.php';

exigirMetodo('POST');

$nome = preg_replace('/\s+/', ' ', campo('nome'));
$email = strtolower(campo('email'));
$senha = campo('senha');
$cpf = preg_replace('/\D/', '', campo('cpf'));
$telefone = preg_replace('/[^\d()+\- ]/', '', campo('telefone'));

if (mb_strlen($nome) < 3) {
    falhar('Informe seu nome completo.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    falhar('Informe um e-mail válido.');
}
if (strlen($senha) < 6) {
    falhar('A senha precisa ter pelo menos 6 caracteres.');
}
if (strlen($cpf) !== 11) {
    falhar('O CPF precisa ter 11 números.');
}

$modelo = new Usuario($conn);

if ($modelo->emailExiste($email)) {
    falhar('Já existe uma conta com este e-mail. Entre com ela.', 409);
}

if (!$modelo->cadastrar($nome, $email, $senha, $cpf)) {
    falhar('Não foi possível criar a conta. Tente novamente.', 500);
}

$id = (int) $conn->insert_id;

if ($telefone !== '') {
    $stmt = $conn->prepare('UPDATE usuarios SET telefone = ? WHERE id = ?');
    $stmt->bind_param('si', $telefone, $id);
    $stmt->execute();
}

$token = bin2hex(random_bytes(32));
$hash = hash('sha256', $token);
$dispositivo = mb_substr(campo('dispositivo', 'Aplicativo'), 0, 100);
$expira = date('Y-m-d H:i:s', strtotime('+' . (int) APP_TOKEN_DIAS . ' days'));

$stmt = $conn->prepare('INSERT INTO api_tokens (usuario_id, token_hash, dispositivo, expira_em) VALUES (?, ?, ?, ?)');
$stmt->bind_param('isss', $id, $hash, $dispositivo, $expira);
$stmt->execute();

responder([
    'token'   => $token,
    'expira'  => date('c', strtotime($expira)),
    'usuario' => usuarioPublico(buscarUsuario($conn, $id)),
], 201);
