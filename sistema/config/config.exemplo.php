<?php
/*
 * MODELO de configuração. NÃO coloque senhas neste arquivo.
 *
 * 1. Copie este arquivo para  sistema/config/config.local.php
 * 2. Preencha com os dados do banco do lugar onde o site roda
 *    (cada computador / servidor tem o seu config.local.php)
 *
 * O config.local.php está no .gitignore e nunca vai para o GitHub.
 */

// Banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'usuario_do_banco');
define('DB_PASS', 'senha_do_banco');
define('DB_NAME', 'devsphar_smartlocker');
define('DB_PORT', 3306);

// Chave que o ESP32 manda no cabeçalho X-Device-Key (uma frase longa qualquer)
define('DEVICE_KEY', 'troque-esta-chave-do-esp32');

// Opcionais
define('APP_TIMEZONE', 'America/Sao_Paulo');
define('APP_TOKEN_DIAS', 30);   // validade do login do app, em dias
