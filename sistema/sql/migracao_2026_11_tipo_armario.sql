-- =====================================================================
--  SmartLocker · Migração: TIPO DE ABERTURA DO ARMÁRIO (novembro/2026)
--
--  Cada armário abre por UM método: QR Code ou chaveiro RFID.
--  O administrador escolhe no cadastro do armário; o usuário vê na hora de reservar.
--
--  Rode UMA vez em cada banco (MySQL Workbench ou phpMyAdmin > SQL),
--  DEPOIS das migrações migracao_2026_11.sql e migracao_2026_11_admin.sql.
--  Compatível com MySQL 8.x e MariaDB 10.x. Não apaga nenhum dado.
-- =====================================================================

-- 1) Novo campo: como o armário abre (padrão: QR Code)
ALTER TABLE lockers
    ADD COLUMN metodo_acesso ENUM('qrcode', 'rfid') NOT NULL DEFAULT 'qrcode' AFTER localizacao;

-- 2) O Armário 01 é o que tem o leitor RC522 (ESP32 com NUMERO_ARMARIO = 1)
UPDATE lockers SET metodo_acesso = 'rfid' WHERE numero = 1;

-- =====================================================================
--  CONFERIR
--     SELECT numero, localizacao, metodo_acesso, status FROM lockers ORDER BY numero;
--
--  TROCAR À MÃO (o painel admin também faz isso)
--     UPDATE lockers SET metodo_acesso = 'rfid' WHERE numero = 2;
-- =====================================================================
