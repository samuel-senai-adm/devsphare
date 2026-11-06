<?php
/*
 * GET api/perfil.php — dados da conta + números das reservas.
 */
require_once __DIR__ . '/_base.php';

exigirMetodo('GET');
$usuario = exigirLogin($conn);
atualizarSituacao($conn);

$id = (int) $usuario['id'];
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total,
            SUM(status = 'ativa') AS ativas,
            SUM(status = 'finalizada') AS concluidas,
            SUM(status = 'cancelada') AS canceladas
       FROM reservas WHERE usuario_id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$n = $stmt->get_result()->fetch_assoc();

responder([
    'usuario' => usuarioPublico($usuario),
    'numeros' => [
        'total'      => (int) $n['total'],
        'ativas'     => (int) $n['ativas'],
        'concluidas' => (int) $n['concluidas'],
        'canceladas' => (int) $n['canceladas'],
    ],
]);
