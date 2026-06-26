-- Migração: Configurações SMTP para recuperação de senha
-- Aplicar: mysql -u user -p kewanfarma < migrations/smtp_configuracoes.sql

INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES
  ('smtp_host',       '',        'Servidor SMTP (ex: smtp.gmail.com)'),
  ('smtp_porta',      '587',     'Porta SMTP (587 = TLS, 465 = SSL, 25 = sem criptografia)'),
  ('smtp_usuario',    '',        'Utilizador SMTP (normalmente o email)'),
  ('smtp_senha',      '',        'Senha do email SMTP'),
  ('smtp_criptografia','tls',    'Criptografia SMTP: tls | ssl | vazio'),
  ('smtp_de_nome',    '',        'Nome remetente dos emails (ex: KewanFarma)'),
  ('smtp_de_email',   '',        'Email remetente (ex: noreply@farmacia.mz)');
