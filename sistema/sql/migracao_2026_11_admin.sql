-- =====================================================================
--  SmartLocker · Migração do PAINEL ADMINISTRATIVO (novembro/2026)
--
--  Rode UMA vez em cada banco (MySQL Workbench ou phpMyAdmin > SQL):
--    - no banco de teste do grupo
--    - no banco da hospedagem (HostGator)
--  Rode DEPOIS da migracao_2026_11.sql.
--  Compatível com MySQL 8.x e MariaDB 10.x. Não apaga nenhum dado.
-- =====================================================================

-- 1) PERFIL: toda conta que não for 'admin' vira 'usuario'
--    (corrige cadastros antigos com perfil vazio ou nulo)
UPDATE usuarios
   SET perfil = 'usuario'
 WHERE perfil IS NULL OR perfil NOT IN ('admin', 'usuario');

-- 2) PERFIL só aceita 'usuario' ou 'admin', e o padrão de todo cadastro novo é 'usuario'
--    BLOQUEIO: o administrador pode bloquear uma conta sem apagar o histórico
ALTER TABLE usuarios
    MODIFY perfil ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    ADD COLUMN bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER perfil,
    ADD COLUMN bloqueado_em DATETIME NULL AFTER bloqueado;

-- =====================================================================
--  CRIAR O PRIMEIRO ADMINISTRADOR
--  1. Crie a conta normalmente pela tela de cadastro do site (senha forte).
--  2. Rode, trocando o e-mail:
--
--     UPDATE usuarios SET perfil = 'admin' WHERE email = 'admin@exemplo.com';
--
--  3. Saia e entre de novo no site: você cai direto no painel administrativo.
--
--  TIRAR O ACESSO DE ADMINISTRADOR
--     UPDATE usuarios SET perfil = 'usuario' WHERE email = 'admin@exemplo.com';
--
--  CONFERIR QUEM É ADMINISTRADOR
--     SELECT id, nome, email FROM usuarios WHERE perfil = 'admin';
-- =====================================================================
