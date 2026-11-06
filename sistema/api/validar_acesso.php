<?php
/*
 * POST api/validar_acesso.php   — chamado pelo ESP32 ao ler um chaveiro RFID ou um QR Code.
 *
 * Cabeçalho:  X-Device-Key: <DEVICE_KEY do config.local.php>
 * Corpo (JSON ou formulário):
 *     armario = 1                (número do armário onde está o leitor)
 *     metodo  = rfid | qrcode
 *     codigo  = A1B2C3D4         (UID do chaveiro)  ou  SMARTLOCKER:<token> (QR)
 *
 * Resposta (sempre HTTP 200 quando a chave está certa, para facilitar no ESP32):
 *     { "ok": true, "liberado": true,  "tipo": "entrada", "mensagem": "...", "armario": "Armário 01" }
 *     { "ok": true, "liberado": false, "motivo": "sem_reserva", "mensagem": "..." }
 *
 * No ESP32 basta procurar  "liberado":true  na resposta para acionar a trava.
 */
require_once __DIR__ . '/_base.php';
require_once __DIR__ . '/_acesso.php';

exigirMetodo('POST');

$chave = isset($_SERVER['HTTP_X_DEVICE_KEY']) ? $_SERVER['HTTP_X_DEVICE_KEY'] : campo('chave');

if (DEVICE_KEY === '' || DEVICE_KEY === 'troque-esta-chave-do-esp32') {
    falhar('Configure a DEVICE_KEY em config.local.php antes de usar o leitor.', 503);
}
if (!is_string($chave) || !hash_equals(DEVICE_KEY, $chave)) {
    falhar('Dispositivo não autorizado.', 401);
}

$armario = campo('armario');
if ($armario === '') {
    falhar('Informe o número do armário.');
}

responder(validarAcesso($conn, $armario, campo('metodo'), campo('codigo')));
