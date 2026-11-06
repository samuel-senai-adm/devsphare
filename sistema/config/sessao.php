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
