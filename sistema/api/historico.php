<?php
/*
 * GET api/historico.php — reservas do usuário (mais recentes primeiro)
 * e os últimos acessos nos armários (QR / RFID).
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('GET');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$usuarioId = (int) $usuario['id'];

$stmt = $conn->prepare(SQL_RESERVA . ' WHERE r.usuario_id = ? ORDER BY r.data_inicio DESC, r.id DESC LIMIT 100');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();

$reservas = [];
$contagem = ['todas' => 0, 'Ativa' => 0, 'Concluída' => 0, 'Cancelada' => 0];
while ($linha = $resultado->fetch_assoc()) {
    $r = formatarReserva($linha);
    $reservas[] = $r;
    $contagem['todas']++;
    if (isset($contagem[$r['status']])) {
        $contagem[$r['status']]++;
    }
}

$stmt = $conn->prepare(
    'SELECT a.tipo_acesso, a.metodo, a.resultado, a.data_hora, l.numero
       FROM logs_acesso a JOIN lockers l ON l.id = a.locker_id
      WHERE a.usuario_id = ?
      ORDER BY a.data_hora DESC, a.id DESC LIMIT 10'
);
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();

$acessos = [];
while ($a = $resultado->fetch_assoc()) {
    $acessos[] = [
        'armario'   => nomeArmario($a['numero']),
        'tipo'      => $a['tipo_acesso'],
        'metodo'    => $a['metodo'],
        'resultado' => $a['resultado'],
        'data_hora' => date('c', strtotime($a['data_hora'])),
    ];
}

responder(['reservas' => $reservas, 'contagem' => $contagem, 'acessos' => $acessos]);
