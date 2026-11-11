<?php
/*
 * GET api/admin/armarios.php — todos os armários com quem está usando agora.
 * status: livre | ocupado | manutencao
 * metodo_acesso: qrcode | rfid
 */
require_once __DIR__ . '/_admin.php';

exigirMetodo('GET');

$resultado = $conn->query(
    "SELECT l.id, l.numero, l.localizacao, l.status, l.metodo_acesso,
            r.id AS reserva_id, r.data_inicio, r.data_fim, u.nome AS ocupante,
            (SELECT COUNT(*) FROM reservas x WHERE x.locker_id = l.id) AS total_reservas,
            (SELECT COUNT(*) FROM logs_acesso a WHERE a.locker_id = l.id) AS total_acessos
       FROM lockers l
       LEFT JOIN reservas r ON r.id = (
            SELECT r2.id FROM reservas r2
             WHERE r2.locker_id = l.id AND r2.status = 'ativa'
             ORDER BY r2.data_inicio LIMIT 1)
       LEFT JOIN usuarios u ON u.id = r.usuario_id
      ORDER BY l.numero"
);

$lista = [];
while ($l = $resultado->fetch_assoc()) {
    $lista[] = [
        'id'             => (int) $l['id'],
        'numero'         => (int) $l['numero'],
        'nome'           => nomeArmario($l['numero']),
        'localizacao'    => $l['localizacao'] ?: '',
        'status'         => $l['status'],
        'metodo_acesso'  => metodoArmario($l['metodo_acesso']),
        'ocupante'       => $l['ocupante'],
        'reserva'        => $l['reserva_id'] ? [
            'id'     => (int) $l['reserva_id'],
            'data'   => date('Y-m-d', strtotime($l['data_inicio'])),
            'inicio' => date('H:i', strtotime($l['data_inicio'])),
            'fim'    => date('H:i', strtotime($l['data_fim'])),
        ] : null,
        'total_reservas' => (int) $l['total_reservas'],
        'pode_excluir'   => (int) $l['total_reservas'] === 0 && (int) $l['total_acessos'] === 0,
    ];
}

responder(['armarios' => $lista]);
