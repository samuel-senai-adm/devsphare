<?php
/*
 * GET api/armarios.php — lista os armários com a situação para quem está logado.
 * status: "livre" | "ocupado" | "manutencao" | "minha"
 * metodo_acesso: "qrcode" | "rfid"  (como o armário abre)
 * tem_chaveiro: se o usuário tem chaveiro RFID vinculado (armário RFID exige)
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('GET');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$id = (int) $usuario['id'];
$stmt = $conn->prepare(
    "SELECT l.id, l.numero, l.localizacao, l.status, l.metodo_acesso,
            (SELECT r.usuario_id FROM reservas r
              WHERE r.locker_id = l.id AND r.status = 'ativa'
              ORDER BY r.data_inicio LIMIT 1) AS reservado_por
       FROM lockers l
      ORDER BY l.numero"
);
$stmt->execute();
$resultado = $stmt->get_result();

$armarios = [];
$resumo = ['total' => 0, 'livre' => 0, 'ocupado' => 0, 'manutencao' => 0, 'minha' => 0];

while ($l = $resultado->fetch_assoc()) {
    if ($l['status'] === 'manutencao') {
        $status = 'manutencao';
    } elseif ($l['reservado_por'] !== null) {
        $status = ((int) $l['reservado_por'] === $id) ? 'minha' : 'ocupado';
    } else {
        $status = 'livre';
    }

    $resumo['total']++;
    $resumo[$status]++;

    $armarios[] = [
        'id'          => (int) $l['id'],
        'numero'      => str_pad((string) $l['numero'], 2, '0', STR_PAD_LEFT),
        'nome'        => nomeArmario($l['numero']),
        'localizacao' => $l['localizacao'] ?: '',
        'status'      => $status,
        'metodo_acesso' => metodoArmario($l['metodo_acesso']),
    ];
}

responder([
    'armarios'     => $armarios,
    'resumo'       => $resumo,
    'tem_chaveiro' => !empty($usuario['rfid_uid']),
]);
