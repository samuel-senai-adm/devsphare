-- =====================================================================
--  SmartLocker · Migração de novembro/2026
--  Rode UMA vez no banco devsphar_smartlocker (phpMyAdmin > SQL > colar > Executar).
--  Rode no banco local (XAMPP / servidor do SENAI) e também no de produção.
--  Compatível com MySQL 8.x e MariaDB 10.x.
--  Não apaga nenhum dado existente.
-- =====================================================================

-- 1) USUÁRIOS: código (UID) do chaveiro RFID de cada pessoa
ALTER TABLE usuarios
    ADD COLUMN rfid_uid VARCHAR(32) NULL AFTER cpf,
    ADD UNIQUE KEY uq_usuarios_rfid (rfid_uid);

-- 2) RESERVAS: token do QR Code, data de criação e de cancelamento
ALTER TABLE reservas
    ADD COLUMN qr_token CHAR(32) NULL AFTER status,
    ADD COLUMN criada_em DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER qr_token,
    ADD COLUMN cancelada_em DATETIME NULL AFTER criada_em,
    ADD UNIQUE KEY uq_reservas_qr (qr_token),
    ADD KEY idx_reservas_locker_status (locker_id, status),
    ADD KEY idx_reservas_usuario_status (usuario_id, status);

-- reservas antigas também ganham um token de QR
UPDATE reservas SET qr_token = MD5(CONCAT(id, '-', RAND(), '-', NOW())) WHERE qr_token IS NULL;

-- 3) LOGS DE ACESSO: como foi a tentativa (QR/RFID) e se liberou ou negou
--    usuario_id passa a aceitar NULL para registrar chaveiros desconhecidos
ALTER TABLE logs_acesso
    MODIFY usuario_id INT NULL,
    ADD COLUMN reserva_id INT NULL AFTER locker_id,
    ADD COLUMN metodo ENUM('qrcode','rfid','senha','app') NOT NULL DEFAULT 'qrcode' AFTER tipo_acesso,
    ADD COLUMN resultado ENUM('liberado','negado') NOT NULL DEFAULT 'liberado' AFTER metodo,
    ADD COLUMN detalhe VARCHAR(150) NULL AFTER resultado,
    ADD KEY idx_logs_reserva (reserva_id);

-- 4) TOKENS DO APLICATIVO (login do app Flutter)
CREATE TABLE IF NOT EXISTS api_tokens (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    dispositivo VARCHAR(100) NULL,
    criado_em DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    expira_em DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tokens_hash (token_hash),
    KEY idx_tokens_usuario (usuario_id),
    CONSTRAINT fk_tokens_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- 5) ARMÁRIOS iniciais (não duplica se já existirem com o mesmo número)
INSERT IGNORE INTO lockers (numero, localizacao, status) VALUES
    (1, 'Bloco A', 'livre'),
    (2, 'Bloco A', 'livre'),
    (3, 'Bloco A', 'livre'),
    (4, 'Bloco B', 'livre');

-- 6) CHAVES ESTRANGEIRAS de reservas (garantem que reserva aponta para usuário e armário reais)
--    Se der erro aqui, existe reserva "órfã" (usuário ou armário apagado). Os passos acima já foram aplicados;
--    apague as reservas órfãs e rode só este bloco de novo.
ALTER TABLE reservas
    ADD CONSTRAINT fk_reservas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    ADD CONSTRAINT fk_reservas_locker  FOREIGN KEY (locker_id)  REFERENCES lockers (id);

-- =====================================================================
--  COMO VINCULAR UM CHAVEIRO RFID A UM USUÁRIO (até existir o painel de admin)
--  1. Passe o chaveiro no leitor. O UID desconhecido aparece em logs_acesso, coluna "detalhe".
--  2. Rode, trocando o UID e o e-mail:
--     UPDATE usuarios SET rfid_uid = 'A1B2C3D4' WHERE email = 'aluno@exemplo.com';
--
--  COMO TORNAR ALGUÉM ADMINISTRADOR
--     UPDATE usuarios SET perfil = 'admin' WHERE email = 'aluno@exemplo.com';
-- =====================================================================
