<?php
/*
 * GET api/admin/resumo.php — números do painel e últimas reservas.
 * Tudo calculado na hora a partir do banco.
 */
require_once __DIR__ . '/_admin.php';

exigirMetodo('GET');

$u = $conn->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(perfil = 'admin'), 0) AS admins,
            COALESCE(SUM(bloqueado = 1), 0) AS bloqueados
       FROM usuarios"
)->fetch_assoc();

$l = $conn->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(status = 'livre'), 0) AS livres,
            COALESCE(SUM(status = 'ocupado'), 0) AS ocupados,
            COALESCE(SUM(status = 'manutencao'), 0) AS manutencao
       FROM lockers"
)->fetch_assoc();

$r = $conn->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(status = 'ativa'), 0) AS ativas,
            COALESCE(SUM(status = 'finalizada'), 0) AS concluidas,
            COALESCE(SUM(status = 'cancelada'), 0) AS canceladas
       FROM reservas"
)->fetch_assoc();

$hoje = date('Y-m-d');
$acessosHoje = contar(
    $conn,
    "SELECT COUNT(*) FROM logs_acesso WHERE resultado = 'liberado' AND DATE(data_hora) = ?",
    's',
    [$hoje]
);

$resultado = $conn->query(
    "SELECT r.id, r.data_inicio, r.data_fim, r.status, l.numero, u.nome, u.email
       FROM reservas r
       JOIN lockers l ON l.id = r.locker_id
       JOIN usuarios u ON u.id = r.usuario_id
      ORDER BY COALESCE(r.criada_em, r.data_inicio) DESC, r.id DESC
      LIMIT 8"
);

$rotulos = ['ativa' => 'Ativa', 'finalizada' => 'Concluída', 'cancelada' => 'Cancelada'];
$ultimas = [];
while ($linha = $resultado->fetch_assoc()) {
    $inicio = strtotime($linha['data_inicio']);
    $ultimas[] = [
        'id'      => (int) $linha['id'],
        'armario' => nomeArmario($linha['numero']),
        'numero'  => str_pad((string) $linha['numero'], 2, '0', STR_PAD_LEFT),
        'usuario' => $linha['nome'],
        'email'   => $linha['email'],
        'data'    => date('Y-m-d', $inicio),
        'inicio'  => date('H:i', $inicio),
        'fim'     => date('H:i', strtotime($linha['data_fim'])),
        'status'  => isset($rotulos[$linha['status']]) ? $rotulos[$linha['status']] : $linha['status'],
    ];
}

responder([
    'usuarios' => [
        'total'      => (int) $u['total'],
        'admins'     => (int) $u['admins'],
        'comuns'     => (int) $u['total'] - (int) $u['admins'],
        'bloqueados' => (int) $u['bloqueados'],
    ],
    'armarios' => [
        'total'      => (int) $l['total'],
        'livres'     => (int) $l['livres'],
        'ocupados'   => (int) $l['ocupados'],
        'manutencao' => (int) $l['manutencao'],
    ],
    'reservas' => [
        'total'      => (int) $r['total'],
        'ativas'     => (int) $r['ativas'],
        'concluidas' => (int) $r['concluidas'],
        'canceladas' => (int) $r['canceladas'],
    ],
    'acessos_hoje'    => $acessosHoje,
    'ultimas_reservas' => $ultimas,
]);
