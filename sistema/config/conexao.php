<?php
/*
 * Conexão com o banco.
 * As senhas ficam em config.local.php (fora do GitHub).
 * Veja config.exemplo.php para criar o seu.
 */

$arquivoConfig = __DIR__ . '/config.local.php';

if (!file_exists($arquivoConfig)) {
    http_response_code(500);
    die('Configuração ausente: copie sistema/config/config.exemplo.php para config.local.php e preencha os dados do banco.');
}

require_once $arquivoConfig;

if (!defined('APP_TIMEZONE'))  { define('APP_TIMEZONE', 'America/Sao_Paulo'); }
if (!defined('DEVICE_KEY'))    { define('DEVICE_KEY', ''); }
if (!defined('APP_TOKEN_DIAS')) { define('APP_TOKEN_DIAS', 30); }

date_default_timezone_set(APP_TIMEZONE);

// Erros do MySQL viram exceções (padrão do PHP 8.1+; deixamos explícito para PHP 8.0)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('SmartLocker - erro de conexão: ' . $e->getMessage());
    http_response_code(500);

    if (defined('SMARTLOCKER_API')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'erro' => 'Não foi possível conectar ao banco de dados.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die('Erro na conexão com o banco de dados.');
}
