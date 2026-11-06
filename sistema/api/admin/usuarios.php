<?php
/*
 * GET api/admin/usuarios.php?busca=ana&filtro=todos|usuario|admin|bloqueado
 * Lista as contas (nunca a senha). Busca por nome, e-mail ou CPF.
 */
require_once __DIR__ . '/_admin.php';

exigirMetodo('GET');

$busca = mb_substr(campo('busca'), 0, 100);
$filtro = campo('filtro', 'todos');

$onde = [];
$tipos = '';
$valores = [];

if ($busca !== '') {
    $termo = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busca) . '%';
    $cpfBusca = preg_replace('/\D/', '', $busca);
    if ($cpfBusca !== '') {
        $onde[] = "(u.nome LIKE ? OR u.email LIKE ? OR REPLACE(REPLACE(u.cpf, '.', ''), '-', '') LIKE ?)";
        $tipos .= 'sss';
        array_push($valores, $termo, $termo, '%' . $cpfBusca . '%');
    } else {
        $onde[] = '(u.nome LIKE ? OR u.email LIKE ?)';
        $tipos .= 'ss';
        array_push($valores, $termo, $termo);
    }
}

if ($filtro === 'usuario' || $filtro === 'admin') {
    $onde[] = 'u.perfil = ?';
    $tipos .= 's';
    $valores[] = $filtro;
} elseif ($filtro === 'bloqueado') {
    $onde[] = 'u.bloqueado = 1';
}

$sql =
    "SELECT u.id, u.nome, u.email, u.telefone, u.cpf, u.perfil, u.bloqueado, u.bloqueado_em,
            u.rfid_uid, u.created_at,
            (SELECT COUNT(*) FROM reservas r WHERE r.usuario_id = u.id) AS total_reservas,
            (SELECT l.numero FROM reservas r JOIN lockers l ON l.id = r.locker_id
              WHERE r.usuario_id = u.id AND r.status = 'ativa' ORDER BY r.data_inicio LIMIT 1) AS armario_ativo,
            (SELECT COUNT(*) FROM logs_acesso a WHERE a.usuario_id = u.id) AS total_acessos
       FROM usuarios u" .
    ($onde ? ' WHERE ' . implode(' AND ', $onde) : '') .
    ' ORDER BY u.perfil = \'admin\' DESC, u.nome ASC LIMIT 500';

$stmt = $conn->prepare($sql);
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$valores);
}
$stmt->execute();
$resultado = $stmt->get_result();

$lista = [];
while ($u = $resultado->fetch_assoc()) {
    $temHistorico = (int) $u['total_reservas'] > 0 || (int) $u['total_acessos'] > 0;
    $lista[] = [
        'id'             => (int) $u['id'],
        'nome'           => $u['nome'],
        'email'          => $u['email'],
        'telefone'       => $u['telefone'] ?: '',
        'cpf'            => $u['cpf'] ?: '',
        'perfil'         => $u['perfil'] === 'admin' ? 'admin' : 'usuario',
        'bloqueado'      => (bool) $u['bloqueado'],
        'bloqueado_em'   => $u['bloqueado_em'] ? date('c', strtotime($u['bloqueado_em'])) : null,
        'rfid_uid'       => $u['rfid_uid'] ?: '',
        'cliente_desde'  => $u['created_at'] ? substr($u['created_at'], 0, 10) : null,
        'total_reservas' => (int) $u['total_reservas'],
        'armario_ativo'  => $u['armario_ativo'] !== null ? nomeArmario($u['armario_ativo']) : null,
        'eu'             => (int) $u['id'] === (int) $admin['id'],
        'pode_excluir'   => !$temHistorico && $u['perfil'] !== 'admin',
    ];
}

responder(['usuarios' => $lista]);
