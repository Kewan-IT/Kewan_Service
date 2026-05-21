-- =============================================================
--  KewanFarma — Módulo de Funcionários (compatível MariaDB)
-- =============================================================

USE kewanfarma;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================
-- 1. CARGOS
-- =============================================================
CREATE TABLE IF NOT EXISTS cargos (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nome         VARCHAR(100)  NOT NULL,
  descricao    TEXT          DEFAULT NULL,
  salario_base DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  ativo        TINYINT(1)    NOT NULL DEFAULT 1,
  criado_em    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cargos_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cargos e funções dos funcionários da farmácia';

-- =============================================================
-- 2. FUNCIONARIOS
-- =============================================================
CREATE TABLE IF NOT EXISTS funcionarios (
  id                      INT UNSIGNED  NOT NULL AUTO_INCREMENT,

  -- Dados pessoais
  nome_completo           VARCHAR(200)  NOT NULL,
  data_nascimento         DATE          NOT NULL,
  sexo                    ENUM('M','F','outro') NOT NULL,
  estado_civil            ENUM('solteiro','casado','divorciado','viuvo','uniao_de_facto') DEFAULT NULL,
  nacionalidade           VARCHAR(80)   NOT NULL DEFAULT 'Moçambicana',
  naturalidade            VARCHAR(100)  DEFAULT NULL,

  -- Identificação
  bi_numero               VARCHAR(30)   NOT NULL,
  bi_validade             DATE          DEFAULT NULL,
  nuit                    VARCHAR(20)   DEFAULT NULL,
  nrps                    VARCHAR(30)   DEFAULT NULL,

  -- Contactos
  telefone_principal      VARCHAR(20)   NOT NULL,
  telefone_alternativo    VARCHAR(20)   DEFAULT NULL,
  email_pessoal           VARCHAR(180)  DEFAULT NULL,
  endereco                VARCHAR(255)  NOT NULL,
  bairro                  VARCHAR(100)  DEFAULT NULL,
  cidade                  VARCHAR(80)   NOT NULL DEFAULT 'Quelimane',
  provincia               VARCHAR(80)   NOT NULL DEFAULT 'Zambézia',

  -- Contacto de emergência
  emergencia_nome         VARCHAR(150)  DEFAULT NULL,
  emergencia_parentesco   VARCHAR(60)   DEFAULT NULL,
  emergencia_telefone     VARCHAR(20)   DEFAULT NULL,

  -- Dados profissionais
  cargo_id                INT UNSIGNED  NOT NULL,
  data_admissao           DATE          NOT NULL,
  data_saida              DATE          DEFAULT NULL,
  tipo_contrato           ENUM('efectivo','temporario','estagio','prestacao_servicos') NOT NULL DEFAULT 'efectivo',
  salario                 DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  numero_funcionario      VARCHAR(20)   NOT NULL,

  -- Habilitações
  nivel_escolaridade      ENUM('primario','secundario','tecnico_medio','licenciatura','mestrado','doutoramento') DEFAULT NULL,
  curso                   VARCHAR(150)  DEFAULT NULL,
  instituicao             VARCHAR(150)  DEFAULT NULL,
  ano_conclusao           YEAR          DEFAULT NULL,

  -- Foto tipo passe
  foto_url                VARCHAR(500)  DEFAULT NULL,
  foto_mime               VARCHAR(30)   DEFAULT NULL,

  -- Documentos PDF
  doc_identificacao_url   VARCHAR(500)  DEFAULT NULL,
  doc_identificacao_nome  VARCHAR(150)  DEFAULT NULL,
  doc_identificacao_mime  VARCHAR(30)   DEFAULT NULL,

  doc_complementar_url    VARCHAR(500)  DEFAULT NULL,
  doc_complementar_nome   VARCHAR(150)  DEFAULT NULL,
  doc_complementar_mime   VARCHAR(30)   DEFAULT NULL,

  -- Estado
  status                  ENUM('activo','inactivo','suspenso','desligado') NOT NULL DEFAULT 'activo',
  motivo_saida            TEXT          DEFAULT NULL,
  observacoes             TEXT          DEFAULT NULL,

  -- Auditoria
  criado_por              INT UNSIGNED  DEFAULT NULL,
  criado_em               TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_funcionarios_bi (bi_numero),
  UNIQUE KEY uq_funcionarios_nuit (nuit),
  UNIQUE KEY uq_funcionarios_numero (numero_funcionario),
  KEY idx_funcionarios_cargo (cargo_id),
  KEY idx_funcionarios_status (status),
  KEY idx_funcionarios_admissao (data_admissao),
  KEY idx_funcionarios_nome (nome_completo),

  CONSTRAINT fk_funcionarios_cargo
    FOREIGN KEY (cargo_id) REFERENCES cargos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT fk_funcionarios_criado_por
    FOREIGN KEY (criado_por) REFERENCES usuarios (id)
    ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Dados completos dos funcionários da KewanFarma';

-- =============================================================
-- 3. ALTERAR TABELA USUARIOS (sem IF NOT EXISTS nas constraints)
-- =============================================================
ALTER TABLE usuarios
  ADD COLUMN funcionario_id   INT UNSIGNED     DEFAULT NULL
    COMMENT 'Funcionário associado a este utilizador'
    AFTER perfil,
  ADD COLUMN criado_por       INT UNSIGNED     DEFAULT NULL
    COMMENT 'Administrador que criou / atribuiu as credenciais'
    AFTER funcionario_id,
  ADD COLUMN token_reset      VARCHAR(100)     DEFAULT NULL
    COMMENT 'Token para redefinição de senha'
    AFTER criado_por,
  ADD COLUMN token_expira_em  TIMESTAMP        NULL DEFAULT NULL
    COMMENT 'Validade do token de redefinição'
    AFTER token_reset,
  ADD COLUMN tentativas_login TINYINT UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'Contador de tentativas de login falhadas'
    AFTER token_expira_em,
  ADD COLUMN bloqueado_ate    TIMESTAMP        NULL DEFAULT NULL
    COMMENT 'Conta bloqueada temporariamente até esta data'
    AFTER tentativas_login;

-- Índice e chaves estrangeiras em comandos separados
ALTER TABLE usuarios
  ADD KEY idx_usuarios_funcionario (funcionario_id);

ALTER TABLE usuarios
  ADD CONSTRAINT fk_usuarios_funcionario
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios (id)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE usuarios
  ADD CONSTRAINT fk_usuarios_criado_por
    FOREIGN KEY (criado_por) REFERENCES usuarios (id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- =============================================================
-- 4. FUNCIONARIOS_DOCUMENTOS
-- =============================================================
CREATE TABLE IF NOT EXISTS funcionarios_documentos (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  funcionario_id   INT UNSIGNED  NOT NULL,
  tipo             ENUM('cv','certificado','contrato','atestado','formacao','outro') NOT NULL DEFAULT 'outro',
  titulo           VARCHAR(150)  NOT NULL,
  ficheiro_url     VARCHAR(500)  NOT NULL,
  ficheiro_nome    VARCHAR(150)  NOT NULL,
  ficheiro_mime    VARCHAR(50)   NOT NULL DEFAULT 'application/pdf',
  ficheiro_tamanho INT UNSIGNED  DEFAULT NULL,
  carregado_por    INT UNSIGNED  DEFAULT NULL,
  criado_em        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_func_docs_funcionario (funcionario_id),
  KEY idx_func_docs_tipo (tipo),
  CONSTRAINT fk_func_docs_funcionario
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_func_docs_carregado_por
    FOREIGN KEY (carregado_por) REFERENCES usuarios (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Documentos adicionais dos funcionários';

-- =============================================================
-- 5. CREDENCIAIS_HISTORICO
-- =============================================================
CREATE TABLE IF NOT EXISTS credenciais_historico (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  usuario_id      INT UNSIGNED  NOT NULL,
  funcionario_id  INT UNSIGNED  NOT NULL,
  acao            ENUM('criacao','alteracao_perfil','alteracao_senha','bloqueio','desbloqueio','desactivacao','reactivacao') NOT NULL,
  perfil_anterior ENUM('admin','farmaceutico','caixa','tecnico') DEFAULT NULL,
  perfil_novo     ENUM('admin','farmaceutico','caixa','tecnico') DEFAULT NULL,
  executado_por   INT UNSIGNED  NOT NULL,
  ip_address      VARCHAR(45)   DEFAULT NULL,
  observacoes     VARCHAR(255)  DEFAULT NULL,
  criado_em       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cred_hist_usuario (usuario_id),
  KEY idx_cred_hist_funcionario (funcionario_id),
  KEY idx_cred_hist_executado (executado_por),
  KEY idx_cred_hist_data (criado_em),
  CONSTRAINT fk_cred_hist_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cred_hist_funcionario
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cred_hist_executado_por
    FOREIGN KEY (executado_por) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Auditoria completa de criação e alteração de credenciais';

-- =============================================================
-- 6. TRIGGERS
-- =============================================================
DELIMITER $$

CREATE TRIGGER trg_funcionario_status_sync
AFTER UPDATE ON funcionarios
FOR EACH ROW
BEGIN
  IF NEW.status IN ('inactivo','suspenso','desligado') AND OLD.status = 'activo' THEN
    UPDATE usuarios SET ativo = 0 WHERE funcionario_id = NEW.id;
  END IF;
  IF NEW.status = 'activo' AND OLD.status != 'activo' THEN
    UPDATE usuarios SET ativo = 1 WHERE funcionario_id = NEW.id;
  END IF;
END$$

CREATE TRIGGER trg_credenciais_hist_perfil
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
  IF (OLD.perfil != NEW.perfil OR OLD.ativo != NEW.ativo) AND NEW.funcionario_id IS NOT NULL THEN
    INSERT INTO credenciais_historico
      (usuario_id, funcionario_id, acao, perfil_anterior, perfil_novo, executado_por)
    VALUES (
      NEW.id,
      NEW.funcionario_id,
      CASE
        WHEN OLD.ativo = 1 AND NEW.ativo = 0 THEN 'desactivacao'
        WHEN OLD.ativo = 0 AND NEW.ativo = 1 THEN 'reactivacao'
        ELSE 'alteracao_perfil'
      END,
      OLD.perfil,
      NEW.perfil,
      COALESCE(NEW.criado_por, NEW.id)
    );
  END IF;
END$$

DELIMITER ;

-- =============================================================
-- 7. VIEWS
-- =============================================================
CREATE OR REPLACE VIEW vw_funcionarios_com_acesso AS
SELECT
  f.id                AS funcionario_id,
  f.numero_funcionario,
  f.nome_completo,
  f.foto_url,
  c.nome              AS cargo,
  f.status            AS status_funcionario,
  u.id                AS usuario_id,
  u.email,
  u.perfil,
  u.ativo             AS acesso_activo,
  u.ultimo_login,
  u.tentativas_login,
  u.bloqueado_ate,
  adm.nome            AS credenciais_criadas_por
FROM funcionarios f
JOIN cargos c          ON c.id = f.cargo_id
LEFT JOIN usuarios u   ON u.funcionario_id = f.id
LEFT JOIN usuarios adm ON adm.id = u.criado_por
ORDER BY f.nome_completo;

CREATE OR REPLACE VIEW vw_funcionarios_sem_acesso AS
SELECT
  f.id                AS funcionario_id,
  f.numero_funcionario,
  f.nome_completo,
  f.foto_url,
  c.nome              AS cargo,
  f.telefone_principal,
  f.email_pessoal,
  f.data_admissao,
  f.status
FROM funcionarios f
JOIN cargos c         ON c.id = f.cargo_id
LEFT JOIN usuarios u  ON u.funcionario_id = f.id
WHERE u.id IS NULL AND f.status = 'activo'
ORDER BY f.nome_completo;

-- =============================================================
-- 8. DADOS INICIAIS — Cargos
-- =============================================================
INSERT INTO cargos (nome, descricao, salario_base) VALUES
('Director Técnico',    'Responsável técnico e científico da farmácia', 80000.00),
('Farmacêutico',        'Dispensa medicamentos e orienta os clientes',   55000.00),
('Técnico de Farmácia', 'Apoio à dispensa sob supervisão farmacêutica',  35000.00),
('Operador de Caixa',   'Processamento de vendas e pagamentos',           28000.00),
('Administrativo',      'Gestão administrativa e de arquivo',             30000.00),
('Gestor de Stock',     'Controlo de entradas, saídas e validades',       32000.00),
('Auxiliar de Limpeza', 'Higiene e limpeza das instalações',              18000.00);

-- =============================================================
SET FOREIGN_KEY_CHECKS = 1;
-- FIM DO SCRIPT
-- =============================================================
