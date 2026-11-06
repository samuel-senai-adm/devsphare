<?php
/*
 * POST api/admin/usuarios_acao.php
 *
 *   { "acao": "salvar",      "id": 3, "nome", "email", "telefone", "cpf", "rfid_uid" }
 *   { "acao": "bloquear",    "id": 3 }
 *   { "acao": "desbloquear", "id": 3 }
 *   { "acao": "excluir",     "id": 3 }
 *
 * O PERFIL (usuario/admin) não pode ser alterado por aqui: mesmo que alguém
 * envie "perfil" ou "role", o campo é ignorado. Isso só se faz direto no banco.
 */
require_once __DIR__ . '/_admin.php';

exigirMetodo('POST');

$acao = campo('acao');
$id = inteiro('id');

if ($id <= 0) {
    falhar('Usuário não informado.');
}

$conta = buscarConta($conn, $id);
if (!$conta) {
    falhar('Usuário não encontrado.', 404);
}

$ehEu = $id === (int) $admin['id'];
$alvoAdmin = $conta['perfil'] === 'admin';

switch ($acao) {

    /* ---------------- EDITAR DADOS CADASTRAIS ---------------- */
    case 'salvar':
        $nome = preg_replace('/\s+/', ' ', campo('nome'));
        $email = strtolower(campo('email'));
        $telefone = preg_replace('/[^\d()+\- ]/', '', campo('telefone'));
        $cpf = preg_replace('/\D/', '', campo('cpf'));
        $uid = normalizarUid(campo('rfid_uid'));

        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) {
            falhar('Informe o nome completo (3 a 100 letras).');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            falhar('Informe um e-mail válido.');
        }
        if ($cpf !== '' && strlen($cpf) !== 11) {
            falhar('O CPF precisa ter 11 números.');
        }
        if (strlen($telefone) > 20) {
            falhar('Telefone muito longo.');
        }
        if ($uid !== '' && (strlen($uid) < 8 || strlen($uid) > 20)) {
            falhar('UID do chaveiro inválido. Use o código em hexadecimal (ex.: A1B2C3D4).');
        }

        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            falhar('Já existe outra conta com este e-mail.', 409);
        }

        if ($uid !== '') {
            $stmt = $conn->prepare('SELECT nome FROM usuarios WHERE rfid_uid = ? AND id <> ?');
            $stmt->bind_param('si', $uid, $id);
            $stmt->execute();
            $outro = $stmt->get_result()->fetch_assoc();
            if ($outro) {
                falhar('Este chaveiro já está vinculado a ' . $outro['nome'] . '.', 409);
            }
        }

        $uidSalvar = $uid !== '' ? $uid : null;
        $telSalvar = $telefone !== '' ? $telefone : null;

        if ($cpf !== '') {
            $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, cpf = ?, rfid_uid = ? WHERE id = ?');
            $stmt->bind_param('sssssi', $nome, $email, $telSalvar, $cpf, $uidSalvar, $id);
        } else {
            // CPF em branco no formulário = mantém o que já está no banco
            $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ?, rfid_uid = ? WHERE id = ?');
            $stmt->bind_param('ssssi', $nome, $email, $telSalvar, $uidSalvar, $id);
        }
        $stmt->execute();

        responder(['mensagem' => 'Dados de ' . $nome . ' atualizados.']);
        break;

    /* ---------------- BLOQUEAR ---------------- */
    case 'bloquear':
        if ($ehEu) {
            falhar('Você não pode bloquear a própria conta.', 409);
        }
        if ($alvoAdmin) {
            falhar('Administradores não podem ser bloqueados pelo painel. Tire o perfil admin no banco primeiro.', 409);
        }
        if ((int) $conta['bloqueado'] === 1) {
            falhar('Esta conta já está bloqueada.', 409);
        }

        $canceladas = bloquearConta($conn, $id);
        $texto = $conta['nome'] . ' foi bloqueado(a).';
        if ($canceladas > 0) {
            $texto .= ' ' . $canceladas . ($canceladas === 1 ? ' reserva ativa foi cancelada.' : ' reservas ativas foram canceladas.');
        }
        responder(['mensagem' => $texto]);
        break;

    /* ---------------- DESBLOQUEAR ---------------- */
    case 'desbloquear':
        $stmt = $conn->prepare('UPDATE usuarios SET bloqueado = 0, bloqueado_em = NULL WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        responder(['mensagem' => $conta['nome'] . ' foi desbloqueado(a).']);
        break;

    /* ---------------- EXCLUIR (só contas sem histórico) ---------------- */
    case 'excluir':
        if ($ehEu) {
            falhar('Você não pode excluir a própria conta.', 409);
        }
        if ($alvoAdmin) {
            falhar('Administradores não podem ser excluídos pelo painel.', 409);
        }

        $reservas = contar($conn, 'SELECT COUNT(*) FROM reservas WHERE usuario_id = ?', 'i', [$id]);
        $acessos = contar($conn, 'SELECT COUNT(*) FROM logs_acesso WHERE usuario_id = ?', 'i', [$id]);
        if ($reservas > 0 || $acessos > 0) {
            falhar('Esta conta tem histórico de reservas ou acessos e não pode ser apagada. Use "Bloquear".', 409);
        }

        $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        responder(['mensagem' => 'Conta de ' . $conta['nome'] . ' excluída.']);
        break;

    default:
        falhar('Ação inválida.');
}
