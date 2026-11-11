<?php
/*
 * Base comum de toda a API do SmartLocker.
 *
 * A mesma API atende três clientes:
 *   - o dashboard web  -> autentica pela sessão PHP (cookie do login.php)
 *   - o app Flutter    -> autentica pelo token do api/login.php (cabeçalho X-Auth-Token)
 *   - o ESP32          -> autentica pela DEVICE_KEY (só em validar_acesso.php)
 *
 * Toda resposta é JSON:  { "ok": true, ... }  ou  { "ok": false, "erro": "mensagem" }
 */

define('SMARTLOCKER_API', true);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/sessao.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

/* Qualquer erro inesperado vira JSON (e não uma página HTML que quebra o app). */
set_exception_handler(function ($e) {
    error_log('SmartLocker API: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    $mensagem = 'Erro interno no servidor. Tente novamente.';

    /* 1054 = coluna não existe, 1146 = tabela não existe: o banco não recebeu a migração */
    if ($e instanceof mysqli_sql_exception && in_array($e->getCode(), [1054, 1146], true)) {
        $falta = preg_match("/'([^']+)'/", $e->getMessage(), $m) ? $m[1] : '';
        $mensagem = 'O banco de dados está desatualizado' . ($falta !== '' ? " (falta: $falta)" : '') .
            '. Rode as migrações da pasta sistema/sql no banco (veja o LEIA-ME).';
    }

    echo json_encode(['ok' => false, 'erro' => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
});

/* ===================== RESPOSTAS ===================== */

function responder(array $dados, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode(['ok' => $codigo < 400] + $dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function falhar($mensagem, $codigo = 400, array $extra = [])
{
    responder(['erro' => $mensagem] + $extra, $codigo);
}

function exigirMetodo($metodo)
{
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
        falhar('Método não permitido. Use ' . $metodo . '.', 405);
    }
}

/* Lê os dados enviados em JSON (app) ou formulário (ESP32 / HTML). */
function entrada()
{
    static $dados = null;
    if ($dados !== null) {
        return $dados;
    }
    $dados = $_POST;
    $bruto = file_get_contents('php://input');
    if ($bruto) {
        $json = json_decode($bruto, true);
        if (is_array($json)) {
            $dados = $json + $dados;
        }
    }
    foreach ($_GET as $chave => $valor) {
        if (!isset($dados[$chave])) {
            $dados[$chave] = $valor;
        }
    }
    return $dados;
}

function campo($nome, $padrao = '')
{
    $dados = entrada();
    if (!isset($dados[$nome]) || is_array($dados[$nome])) {
        return $padrao;
    }
    return trim((string) $dados[$nome]);
}

/* ===================== DATAS ===================== */

function agora()
{
    return date('Y-m-d H:i:s');
}

/* ===================== AUTENTICAÇÃO ===================== */

function tokenDaRequisicao()
{
    $token = isset($_SERVER['HTTP_X_AUTH_TOKEN']) ? $_SERVER['HTTP_X_AUTH_TOKEN'] : '';

    if ($token === '') {
        $auth = '';
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        if (stripos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
        }
    }
    return trim($token);
}

function buscarUsuario(mysqli $conn, $id)
{
    $stmt = $conn->prepare(
        'SELECT id, nome, email, telefone, cpf, perfil, bloqueado, rfid_uid, created_at FROM usuarios WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    return $usuario ?: null;
}

function ehAdmin(array $usuario)
{
    return isset($usuario['perfil']) && $usuario['perfil'] === 'admin';
}

/* 'token' (app), 'sessao' (site) ou null — definido por usuarioLogado(). */
$GLOBALS['ORIGEM_LOGIN'] = null;

/*
 * Usuário dono da requisição, sempre lido do banco (perfil e bloqueio atualizados).
 * Conta bloqueada é tratada como deslogada: o app e o site voltam para o login.
 */
function usuarioLogado(mysqli $conn)
{
    $token = tokenDaRequisicao();
    $usuario = null;

    if ($token !== '') {
        $hash = hash('sha256', $token);
        $agora = agora();
        $stmt = $conn->prepare('SELECT usuario_id FROM api_tokens WHERE token_hash = ? AND expira_em > ?');
        $stmt->bind_param('ss', $hash, $agora);
        $stmt->execute();
        $linha = $stmt->get_result()->fetch_assoc();
        $usuario = $linha ? buscarUsuario($conn, (int) $linha['usuario_id']) : null;
        $GLOBALS['ORIGEM_LOGIN'] = 'token';
    } else {
        iniciarSessao();
        if (!empty($_SESSION['usuario_id'])) {
            $usuario = buscarUsuario($conn, (int) $_SESSION['usuario_id']);
        }
        $GLOBALS['ORIGEM_LOGIN'] = 'sessao';
    }

    if ($usuario && !empty($usuario['bloqueado'])) {
        return null;
    }
    return $usuario;
}

/*
 * Login obrigatório. Pelo site (cookie de sessão), toda requisição que altera
 * dados precisa do cabeçalho X-CSRF-Token. O app usa token e não precisa.
 */
function exigirLogin(mysqli $conn)
{
    $usuario = usuarioLogado($conn);
    if (!$usuario) {
        falhar('Sua sessão expirou. Entre novamente.', 401);
    }

    if ($GLOBALS['ORIGEM_LOGIN'] === 'sessao' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        $csrf = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
        if (!csrfValido($csrf)) {
            falhar('Sessão desatualizada. Recarregue a página e tente de novo.', 403);
        }
    }
    return $usuario;
}

/* Só administradores, e só pelo site (o app não tem área de admin). */
function exigirAdmin(mysqli $conn)
{
    $usuario = exigirLogin($conn);
    if ($GLOBALS['ORIGEM_LOGIN'] !== 'sessao' || !ehAdmin($usuario)) {
        falhar('Acesso restrito a administradores.', 403);
    }
    return $usuario;
}

/* Dados do usuário que podem ir para o cliente (nunca a senha). */
function usuarioPublico(array $u)
{
    $cpf = preg_replace('/\D/', '', (string) $u['cpf']);
    return [
        'id'               => (int) $u['id'],
        'nome'             => $u['nome'],
        'email'            => $u['email'],
        'telefone'         => $u['telefone'],
        'cpf'              => strlen($cpf) === 11 ? '***.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-**' : '',
        'perfil'           => $u['perfil'] ?: 'usuario',
        'chaveiro_rfid'    => !empty($u['rfid_uid']),
        'cliente_desde'    => $u['created_at'] ? substr($u['created_at'], 0, 10) : null,
    ];
}

/* ===================== RESERVAS ===================== */

/*
 * Mantém o banco coerente a cada requisição:
 *  - reservas cujo horário de término passou viram "finalizada"
 *  - lockers.status acompanha as reservas (exceto armários em manutenção)
 */
function atualizarSituacao(mysqli $conn)
{
    $agora = agora();
    $stmt = $conn->prepare("UPDATE reservas SET status = 'finalizada' WHERE status = 'ativa' AND data_fim <= ?");
    $stmt->bind_param('s', $agora);
    $stmt->execute();

    $conn->query(
        "UPDATE lockers l
            SET l.status = CASE WHEN EXISTS (
                    SELECT 1 FROM reservas r WHERE r.locker_id = l.id AND r.status = 'ativa'
                ) THEN 'ocupado' ELSE 'livre' END
          WHERE l.status <> 'manutencao'"
    );
}

define('SQL_RESERVA',
    'SELECT r.id, r.usuario_id, r.locker_id, r.data_inicio, r.data_fim, r.status, r.qr_token,
            r.criada_em, r.cancelada_em, l.numero, l.localizacao, l.metodo_acesso
       FROM reservas r
       JOIN lockers l ON l.id = r.locker_id'
);

function nomeArmario($numero)
{
    return 'Armário ' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
}

/* Como o armário abre: 'qrcode' ou 'rfid' (coluna lockers.metodo_acesso) */
function metodoArmario($metodo)
{
    return $metodo === 'rfid' ? 'rfid' : 'qrcode';
}

function nomeMetodo($metodo)
{
    return metodoArmario($metodo) === 'rfid' ? 'chaveiro RFID' : 'QR Code';
}

function conteudoQr($token)
{
    return 'SMARTLOCKER:' . $token;
}

function formatarReserva(array $r, $incluirQr = false)
{
    $rotulos = ['ativa' => 'Ativa', 'finalizada' => 'Concluída', 'cancelada' => 'Cancelada'];
    $agora = time();
    $inicio = strtotime($r['data_inicio']);
    $fim = strtotime($r['data_fim']);

    $dados = [
        'id'           => (int) $r['id'],
        'locker_id'    => (int) $r['locker_id'],
        'numero'       => str_pad((string) $r['numero'], 2, '0', STR_PAD_LEFT),
        'armario'      => nomeArmario($r['numero']),
        'localizacao'  => $r['localizacao'] ?: '',
        'metodo_acesso'=> metodoArmario(isset($r['metodo_acesso']) ? $r['metodo_acesso'] : ''),
        'data'         => date('Y-m-d', $inicio),
        'inicio'       => date('H:i', $inicio),
        'fim'          => date('H:i', $fim),
        'data_inicio'  => date('c', $inicio),
        'data_fim'     => date('c', $fim),
        'status'       => isset($rotulos[$r['status']]) ? $rotulos[$r['status']] : $r['status'],
        'status_codigo'=> $r['status'],
        'em_andamento' => $r['status'] === 'ativa' && $agora >= $inicio,
        'criada_em'    => $r['criada_em'] ? date('c', strtotime($r['criada_em'])) : null,
    ];

    /* QR Code só existe para armário que abre por QR; armário RFID abre com o chaveiro */
    if ($incluirQr && $r['status'] === 'ativa' && $r['qr_token'] && $dados['metodo_acesso'] === 'qrcode') {
        $dados['qr'] = conteudoQr($r['qr_token']);
    }
    return $dados;
}

function reservaAtivaDoUsuario(mysqli $conn, $usuarioId)
{
    $stmt = $conn->prepare(SQL_RESERVA . " WHERE r.usuario_id = ? AND r.status = 'ativa' ORDER BY r.data_inicio LIMIT 1");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    return $linha ?: null;
}