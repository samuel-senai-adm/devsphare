<?php
/*
 * Regra de liberação do armário — usada pelo ESP32 (validar_acesso.php)
 * e pelo simulador do navegador (sistema/validar_qr.php).
 *
 * Libera quando:
 *  - o método usado (rfid / qrcode) é o do armário (lockers.metodo_acesso)
 *  - a credencial (UID do chaveiro RFID ou QR Code) pertence a uma reserva ATIVA
 *  - a reserva é DESTE armário
 *  - o horário atual está entre (início - 10 min) e o término
 *
 * Toda tentativa, liberada ou negada, é gravada em logs_acesso.
 * A cada liberação alterna "entrada" (guardar) e "saida" (retirar).
 */

define('MINUTOS_ANTES_DO_INICIO', 10);

function normalizarUid($uid)
{
    return strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', (string) $uid));
}

function registrarAcesso(mysqli $conn, $usuarioId, $lockerId, $reservaId, $tipo, $metodo, $resultado, $detalhe)
{
    $agora = agora();
    $detalhe = ($detalhe === null || $detalhe === '') ? null : mb_substr((string) $detalhe, 0, 150);
    $stmt = $conn->prepare(
        'INSERT INTO logs_acesso (usuario_id, locker_id, reserva_id, tipo_acesso, metodo, resultado, detalhe, data_hora)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('iiisssss', $usuarioId, $lockerId, $reservaId, $tipo, $metodo, $resultado, $detalhe, $agora);
    $stmt->execute();
}

function validarAcesso(mysqli $conn, $numeroArmario, $metodo, $codigo)
{
    /* Não chama atualizarSituacao() aqui: são 2 consultas a mais em toda leitura
       e deixavam o leitor lento. O fim da reserva é conferido pela data_fim abaixo. */
    $agora = agora();

    $metodo = strtolower(trim($metodo));
    if ($metodo !== 'rfid' && $metodo !== 'qrcode') {
        return ['liberado' => false, 'motivo' => 'metodo_invalido', 'mensagem' => 'Método deve ser rfid ou qrcode.'];
    }

    $numero = (int) $numeroArmario;
    $stmt = $conn->prepare('SELECT id, numero, status, metodo_acesso FROM lockers WHERE numero = ?');
    $stmt->bind_param('i', $numero);
    $stmt->execute();
    $locker = $stmt->get_result()->fetch_assoc();

    if (!$locker) {
        return ['liberado' => false, 'motivo' => 'armario_invalido', 'mensagem' => 'Armário ' . $numero . ' não cadastrado.'];
    }

    $lockerId = (int) $locker['id'];
    $nome = nomeArmario($locker['numero']);
    $negar = function ($motivo, $mensagem, $usuarioId = null, $reservaId = null, $detalhe = null) use ($conn, $lockerId, $metodo, $nome) {
        registrarAcesso($conn, $usuarioId, $lockerId, $reservaId, null, $metodo, 'negado', $detalhe ?: $mensagem);
        return ['liberado' => false, 'motivo' => $motivo, 'mensagem' => $mensagem, 'armario' => $nome];
    };

    if ($locker['status'] === 'manutencao') {
        return $negar('manutencao', $nome . ' está em manutenção.');
    }

    // cada armário abre por um método só (definido pelo administrador)
    $metodoDoArmario = metodoArmario($locker['metodo_acesso']);
    if ($metodo !== $metodoDoArmario) {
        return $negar('metodo_errado', $nome . ' abre só com ' . nomeMetodo($metodoDoArmario) . '.');
    }

    $reserva = null;
    $usuarioId = null;

    if ($metodo === 'rfid') {
        $uid = normalizarUid($codigo);
        if ($uid === '') {
            return $negar('codigo_vazio', 'Nenhum UID recebido.');
        }

        $stmt = $conn->prepare('SELECT id, nome, bloqueado FROM usuarios WHERE rfid_uid = ?');
        $stmt->bind_param('s', $uid);
        $stmt->execute();
        $dono = $stmt->get_result()->fetch_assoc();
        if (!$dono) {
            return $negar('chaveiro_desconhecido', 'Chaveiro não cadastrado.', null, null, 'Chaveiro não cadastrado (UID ' . $uid . ')');
        }
        $usuarioId = (int) $dono['id'];
        if (!empty($dono['bloqueado'])) {
            return $negar('conta_bloqueada', 'Conta bloqueada. Procure o administrador.', $usuarioId);
        }

        $stmt = $conn->prepare(SQL_RESERVA . " WHERE r.usuario_id = ? AND r.locker_id = ? AND r.status = 'ativa' AND r.data_fim > ? LIMIT 1");
        $stmt->bind_param('iis', $usuarioId, $lockerId, $agora);
        $stmt->execute();
        $reserva = $stmt->get_result()->fetch_assoc();
        if (!$reserva) {
            return $negar('sem_reserva', 'Você não tem reserva ativa neste armário.', $usuarioId);
        }
    } else {
        $token = strtolower(trim((string) $codigo));
        if (stripos($token, 'smartlocker:') === 0) {
            $token = substr($token, 12);
        }
        if (!preg_match('/^[0-9a-f]{32}$/', $token)) {
            return $negar('qr_invalido', 'QR Code inválido.');
        }

        $stmt = $conn->prepare(SQL_RESERVA . ' WHERE r.qr_token = ? LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $reserva = $stmt->get_result()->fetch_assoc();
        if (!$reserva) {
            return $negar('qr_invalido', 'QR Code não reconhecido.');
        }
        $usuarioId = (int) $reserva['usuario_id'];

        $dono = buscarUsuario($conn, $usuarioId);
        if ($dono && !empty($dono['bloqueado'])) {
            return $negar('conta_bloqueada', 'Conta bloqueada. Procure o administrador.', $usuarioId, (int) $reserva['id']);
        }

        if ((int) $reserva['locker_id'] !== $lockerId) {
            return $negar('outro_armario', 'Este QR Code é do ' . nomeArmario($reserva['numero']) . '.', $usuarioId, (int) $reserva['id']);
        }
        if ($reserva['status'] !== 'ativa' || strtotime($reserva['data_fim']) <= time()) {
            $texto = $reserva['status'] === 'cancelada' ? 'Esta reserva foi cancelada.' : 'Esta reserva já terminou.';
            return $negar('reserva_encerrada', $texto, $usuarioId, (int) $reserva['id']);
        }
    }

    $reservaId = (int) $reserva['id'];
    $liberaEm = strtotime($reserva['data_inicio']) - MINUTOS_ANTES_DO_INICIO * 60;

    if (time() < $liberaEm) {
        return $negar('cedo_demais', 'Sua reserva começa às ' . date('H:i', strtotime($reserva['data_inicio'])) . '.', $usuarioId, $reservaId);
    }

    // alterna entrada / saída conforme o último acesso liberado desta reserva
    $stmt = $conn->prepare(
        "SELECT tipo_acesso FROM logs_acesso WHERE reserva_id = ? AND resultado = 'liberado' ORDER BY id DESC LIMIT 1"
    );
    $stmt->bind_param('i', $reservaId);
    $stmt->execute();
    $ultimo = $stmt->get_result()->fetch_assoc();
    $tipo = ($ultimo && $ultimo['tipo_acesso'] === 'entrada') ? 'saida' : 'entrada';

    registrarAcesso($conn, $usuarioId, $lockerId, $reservaId, $tipo, $metodo, 'liberado', null);

    $primeiroNome = !empty($dono['nome']) ? explode(' ', trim($dono['nome']))[0] : '';

    return [
        'liberado'   => true,
        'motivo'     => 'ok',
        'tipo'       => $tipo,
        'mensagem'   => ($tipo === 'entrada' ? 'Acesso liberado. Bem-vindo, ' : 'Acesso liberado. Até logo, ') . $primeiroNome . '!',
        'usuario'    => $primeiroNome,
        'armario'    => $nome,
        'reserva_id' => $reservaId,
    ];
}
