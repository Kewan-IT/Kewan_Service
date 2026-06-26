-- Migração: flag para forçar troca de senha no próximo login
-- Aplicar: mysql -u user -p kewanfarma < migrations/trocar_senha_proximo.sql

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS trocar_senha_proximo TINYINT(1) NOT NULL DEFAULT 0
  AFTER token_expira_em;
