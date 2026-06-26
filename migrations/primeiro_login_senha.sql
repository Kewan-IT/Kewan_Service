-- Migração: forçar troca de senha no primeiro login após atribuição de credenciais
-- Aplicar: mysql -u utilizador -p kewanfarma < migrations/primeiro_login_senha.sql

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS trocar_senha_proximo TINYINT(1)  NOT NULL DEFAULT 0
    COMMENT 'Se 1, força troca de senha no próximo login'
    AFTER ativo,

  ADD COLUMN IF NOT EXISTS tentativas_login     TINYINT     NOT NULL DEFAULT 0
    AFTER trocar_senha_proximo,

  ADD COLUMN IF NOT EXISTS bloqueado_ate        TIMESTAMP   NULL DEFAULT NULL
    AFTER tentativas_login,

  ADD COLUMN IF NOT EXISTS token_reset          VARCHAR(255) NULL DEFAULT NULL
    AFTER bloqueado_ate,

  ADD COLUMN IF NOT EXISTS token_expira_em      TIMESTAMP   NULL DEFAULT NULL
    AFTER token_reset,

  ADD COLUMN IF NOT EXISTS foto_url             VARCHAR(255) NULL DEFAULT NULL
    AFTER token_expira_em,

  ADD COLUMN IF NOT EXISTS funcionario_id       INT UNSIGNED NULL DEFAULT NULL
    AFTER foto_url,

  ADD COLUMN IF NOT EXISTS criado_por           INT UNSIGNED NULL DEFAULT NULL
    AFTER funcionario_id;
