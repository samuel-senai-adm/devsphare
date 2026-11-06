<?php
/*
 * Inicia a sessão PHP com segurança.
 *
 * Na hospedagem apareciam erros "session_start(): Failed to read session data"
 * (a pasta padrão de sessões do servidor não existia). Quando isso acontece,
 * usamos a pasta sistema/.sessoes, criada automaticamente.
 */

function iniciarSessao()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $caminho = session_save_path();
    $pasta = $caminho;
    if (strpos($pasta, ';') !== false) {           // formato "N;/caminho"
        $partes = explode(';', $pasta);
        $pasta = end($partes);
    }

    if ($pasta === '' || !is_dir($pasta) || !is_writable($pasta)) {
        $local = dirname(__DIR__) . '/.sessoes';
        if (!is_dir($local)) {
            @mkdir($local, 0700, true);
            @file_put_contents($local . '/.htaccess', "Require all denied\nDeny from all\n");
        }
        if (is_dir($local) && is_writable($local)) {
            session_save_path($local);
        }
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);

    session_start();
}

/*
 * Token CSRF da sessão. Vai numa <meta> das páginas e volta no cabeçalho
 * X-CSRF-Token de toda requisição que altera dados; um site de fora não
 * consegue ler esse valor, então não consegue forjar a requisição.
 */
function tokenCsrf()
{
    iniciarSessao();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfValido($token)
{
    iniciarSessao();
    return is_string($token) && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* Encerra a sessão por completo (variáveis, arquivo no servidor e cookie). */
function encerrarSessao()
{
    iniciarSessao();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => isset($p['samesite']) ? $p['samesite'] : 'Lax',
        ]);
    }
    session_destroy();
}
