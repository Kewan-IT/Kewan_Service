-- =============================================================
--  KewanFarma — Script Completo de Banco de Dados
--  Sistema de Gestão de Farmácia
--  MySQL 8.0+
--  Gerado para uso profissional
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -------------------------------------------------------------
-- Criar e seleccionar o banco de dados
-- -------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS kewanfarma
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kewanfarma;


-- =============================================================
-- 1. USUARIOS
-- Controla o acesso ao sistema por perfil
-- =============================================================
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(120)      NOT NULL,
  email         VARCHAR(180)      NOT NULL,
  senha_hash    VARCHAR(255)      NOT NULL,
  perfil        ENUM('admin','farmaceutico','caixa','tecnico') NOT NULL DEFAULT 'caixa',
  telefone      VARCHAR(20)       DEFAULT NULL,
  ativo         TINYINT(1)        NOT NULL DEFAULT 1,
  ultimo_login  TIMESTAMP         NULL DEFAULT NULL,
  criado_em     TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_perfil (perfil),
  KEY idx_usuarios_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Utilizadores do sistema com controlo de acesso por perfil';


-- =============================================================
-- 2. CATEGORIAS
-- Suporta hierarquia (subcategorias) via categoria_pai_id
-- =============================================================
CREATE TABLE IF NOT EXISTS categorias (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nome             VARCHAR(100)  NOT NULL,
  descricao        TEXT          DEFAULT NULL,
  categoria_pai_id INT UNSIGNED  DEFAULT NULL,
  ativo            TINYINT(1)    NOT NULL DEFAULT 1,
  criado_em        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nome (nome),
  KEY idx_categorias_pai (categoria_pai_id),
  CONSTRAINT fk_categorias_pai
    FOREIGN KEY (categoria_pai_id) REFERENCES categorias (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorias de produtos com suporte a hierarquia';


-- =============================================================
-- 3. FORNECEDORES
-- =============================================================
CREATE TABLE IF NOT EXISTS fornecedores (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nome        VARCHAR(150)  NOT NULL,
  nuit        VARCHAR(20)   DEFAULT NULL,
  telefone    VARCHAR(20)   DEFAULT NULL,
  email       VARCHAR(180)  DEFAULT NULL,
  endereco    VARCHAR(255)  DEFAULT NULL,
  cidade      VARCHAR(80)   DEFAULT NULL,
  pais        VARCHAR(80)   NOT NULL DEFAULT 'Moçambique',
  ativo       TINYINT(1)    NOT NULL DEFAULT 1,
  criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_fornecedores_nuit (nuit),
  KEY idx_fornecedores_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Fornecedores e distribuidores de produtos farmacêuticos';


-- =============================================================
-- 4. PRODUTOS
-- Núcleo do catálogo farmacêutico
-- =============================================================
CREATE TABLE IF NOT EXISTS produtos (
  id               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  nome             VARCHAR(200)      NOT NULL,
  codigo_barras    VARCHAR(50)       DEFAULT NULL,
  principio_ativo  VARCHAR(200)      DEFAULT NULL,
  descricao        TEXT              DEFAULT NULL,
  categoria_id     INT UNSIGNED      NOT NULL,
  fornecedor_id    INT UNSIGNED      DEFAULT NULL,
  unidade_medida   VARCHAR(30)       NOT NULL DEFAULT 'unidade',  -- ex: unidade, caixa, frasco, ml, g
  preco_compra     DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
  preco_venda      DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
  margem_lucro     DECIMAL(5,2)      GENERATED ALWAYS AS (
                     CASE WHEN preco_compra > 0
                     THEN ((preco_venda - preco_compra) / preco_compra) * 100
                     ELSE 0 END
                   ) VIRTUAL,
  estoque_actual   INT               NOT NULL DEFAULT 0,
  estoque_min      INT               NOT NULL DEFAULT 5,
  requer_receita   TINYINT(1)        NOT NULL DEFAULT 0,
  controlado       TINYINT(1)        NOT NULL DEFAULT 0,  -- medicamento sujeito a controlo especial
  imagem_url       VARCHAR(500)      DEFAULT NULL,
  ativo            TINYINT(1)        NOT NULL DEFAULT 1,
  criado_em        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_produtos_codigo_barras (codigo_barras),
  KEY idx_produtos_categoria (categoria_id),
  KEY idx_produtos_fornecedor (fornecedor_id),
  KEY idx_produtos_ativo (ativo),
  KEY idx_produtos_requer_receita (requer_receita),
  KEY idx_produtos_estoque (estoque_actual, estoque_min),
  CONSTRAINT fk_produtos_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_produtos_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de produtos farmacêuticos';


-- =============================================================
-- 5. LOTES
-- Rastreabilidade por número de lote e validade
-- =============================================================
CREATE TABLE IF NOT EXISTS lotes (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  produto_id    INT UNSIGNED  NOT NULL,
  numero_lote   VARCHAR(60)   NOT NULL,
  quantidade    INT           NOT NULL DEFAULT 0,
  validade      DATE          NOT NULL,
  data_entrada  DATE          NOT NULL DEFAULT (CURRENT_DATE),
  observacoes   TEXT          DEFAULT NULL,
  criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_lotes_produto_lote (produto_id, numero_lote),
  KEY idx_lotes_validade (validade),
  KEY idx_lotes_produto (produto_id),
  CONSTRAINT fk_lotes_produto
    FOREIGN KEY (produto_id) REFERENCES produtos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Lotes de produtos com rastreabilidade de validade';


-- =============================================================
-- 6. CLIENTES
-- =============================================================
CREATE TABLE IF NOT EXISTS clientes (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nome            VARCHAR(150)  NOT NULL,
  nuit            VARCHAR(20)   DEFAULT NULL,
  bi              VARCHAR(20)   DEFAULT NULL,
  telefone        VARCHAR(20)   DEFAULT NULL,
  email           VARCHAR(180)  DEFAULT NULL,
  endereco        VARCHAR(255)  DEFAULT NULL,
  data_nascimento DATE          DEFAULT NULL,
  sexo            ENUM('M','F','outro') DEFAULT NULL,
  observacoes     TEXT          DEFAULT NULL,
  ativo           TINYINT(1)    NOT NULL DEFAULT 1,
  criado_em       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_clientes_nuit (nuit),
  KEY idx_clientes_nome (nome),
  KEY idx_clientes_telefone (telefone),
  KEY idx_clientes_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Clientes da farmácia';


-- =============================================================
-- 7. RECEITAS_MEDICAS
-- Receitas associadas a clientes, exigidas para produtos controlados
-- =============================================================
CREATE TABLE IF NOT EXISTS receitas_medicas (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  cliente_id     INT UNSIGNED  NOT NULL,
  medico_nome    VARCHAR(150)  NOT NULL,
  medico_ordem   VARCHAR(40)   DEFAULT NULL,  -- número de ordem do médico
  especialidade  VARCHAR(100)  DEFAULT NULL,
  data_emissao   DATE          NOT NULL,
  validade       DATE          NOT NULL,
  imagem_url     VARCHAR(500)  DEFAULT NULL,
  status         ENUM('pendente','usada','expirada','cancelada') NOT NULL DEFAULT 'pendente',
  observacoes    TEXT          DEFAULT NULL,
  criado_por     INT UNSIGNED  DEFAULT NULL,
  criado_em      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_receitas_cliente (cliente_id),
  KEY idx_receitas_status (status),
  KEY idx_receitas_validade (validade),
  CONSTRAINT fk_receitas_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_receitas_usuario
    FOREIGN KEY (criado_por) REFERENCES usuarios (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Receitas médicas para controlo de produtos que exigem prescrição';


-- =============================================================
-- 8. VENDAS (cabeçalho)
-- =============================================================
CREATE TABLE IF NOT EXISTS vendas (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  numero_venda    VARCHAR(20)   NOT NULL,  -- ex: VD-2025-00001
  cliente_id      INT UNSIGNED  DEFAULT NULL,  -- NULL = venda balcão
  usuario_id      INT UNSIGNED  NOT NULL,
  receita_id      INT UNSIGNED  DEFAULT NULL,
  forma_pagamento ENUM('dinheiro','mpesa','emola','cartao_debito','cartao_credito','transferencia','credito') NOT NULL DEFAULT 'dinheiro',
  desconto        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  valor_pago      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  troco           DECIMAL(12,2) GENERATED ALWAYS AS (
                    CASE WHEN valor_pago >= total THEN valor_pago - total ELSE 0 END
                  ) VIRTUAL,
  status          ENUM('pendente','concluida','cancelada','devolvida') NOT NULL DEFAULT 'concluida',
  observacoes     TEXT          DEFAULT NULL,
  criado_em       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vendas_numero (numero_venda),
  KEY idx_vendas_cliente (cliente_id),
  KEY idx_vendas_usuario (usuario_id),
  KEY idx_vendas_receita (receita_id),
  KEY idx_vendas_status (status),
  KEY idx_vendas_data (criado_em),
  CONSTRAINT fk_vendas_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_vendas_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_vendas_receita
    FOREIGN KEY (receita_id) REFERENCES receitas_medicas (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cabeçalho das vendas realizadas na farmácia';


-- =============================================================
-- 9. ITENS_VENDA (linhas da venda)
-- =============================================================
CREATE TABLE IF NOT EXISTS itens_venda (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  venda_id        INT UNSIGNED  NOT NULL,
  produto_id      INT UNSIGNED  NOT NULL,
  lote_id         INT UNSIGNED  DEFAULT NULL,
  quantidade      INT           NOT NULL DEFAULT 1,
  preco_unitario  DECIMAL(12,2) NOT NULL,
  desconto_item   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  subtotal        DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_itens_venda_venda (venda_id),
  KEY idx_itens_venda_produto (produto_id),
  KEY idx_itens_venda_lote (lote_id),
  CONSTRAINT fk_itens_venda_venda
    FOREIGN KEY (venda_id) REFERENCES vendas (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_itens_venda_produto
    FOREIGN KEY (produto_id) REFERENCES produtos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_itens_venda_lote
    FOREIGN KEY (lote_id) REFERENCES lotes (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens (linhas) de cada venda';


-- =============================================================
-- 10. COMPRAS (cabeçalho)
-- Entradas de mercadoria dos fornecedores
-- =============================================================
CREATE TABLE IF NOT EXISTS compras (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  numero_compra   VARCHAR(20)   NOT NULL,  -- ex: CP-2025-00001
  fornecedor_id   INT UNSIGNED  NOT NULL,
  usuario_id      INT UNSIGNED  NOT NULL,
  numero_fatura   VARCHAR(60)   DEFAULT NULL,  -- fatura do fornecedor
  subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  desconto        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status          ENUM('rascunho','enviada','parcialmente_recebida','recebida','cancelada') NOT NULL DEFAULT 'rascunho',
  data_pedido     DATE          NOT NULL DEFAULT (CURRENT_DATE),
  data_entrega    DATE          DEFAULT NULL,
  observacoes     TEXT          DEFAULT NULL,
  criado_em       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_em  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_compras_numero (numero_compra),
  KEY idx_compras_fornecedor (fornecedor_id),
  KEY idx_compras_usuario (usuario_id),
  KEY idx_compras_status (status),
  KEY idx_compras_data (data_pedido),
  CONSTRAINT fk_compras_fornecedor
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_compras_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cabeçalho das ordens de compra a fornecedores';


-- =============================================================
-- 11. ITENS_COMPRA (linhas da compra)
-- =============================================================
CREATE TABLE IF NOT EXISTS itens_compra (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  compra_id        INT UNSIGNED  NOT NULL,
  produto_id       INT UNSIGNED  NOT NULL,
  quantidade       INT           NOT NULL,
  preco_unitario   DECIMAL(12,2) NOT NULL,
  subtotal         DECIMAL(12,2) NOT NULL,
  numero_lote      VARCHAR(60)   DEFAULT NULL,
  validade_lote    DATE          DEFAULT NULL,
  quantidade_recebida INT        NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_itens_compra_compra (compra_id),
  KEY idx_itens_compra_produto (produto_id),
  CONSTRAINT fk_itens_compra_compra
    FOREIGN KEY (compra_id) REFERENCES compras (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_itens_compra_produto
    FOREIGN KEY (produto_id) REFERENCES produtos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens (linhas) de cada ordem de compra';


-- =============================================================
-- 12. CAIXA
-- Sessões de abertura e fecho de caixa
-- =============================================================
CREATE TABLE IF NOT EXISTS caixa (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  usuario_id     INT UNSIGNED  NOT NULL,
  saldo_inicial  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  saldo_final    DECIMAL(12,2) DEFAULT NULL,
  total_vendas   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total_entradas DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total_saidas   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status         ENUM('aberto','fechado') NOT NULL DEFAULT 'aberto',
  observacoes    TEXT          DEFAULT NULL,
  aberto_em      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fechado_em     TIMESTAMP     NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_caixa_usuario (usuario_id),
  KEY idx_caixa_status (status),
  KEY idx_caixa_aberto_em (aberto_em),
  CONSTRAINT fk_caixa_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sessões de abertura e fecho de caixa';


-- =============================================================
-- 13. MOVIMENTOS_CAIXA
-- Cada transacção financeira registada na sessão de caixa
-- =============================================================
CREATE TABLE IF NOT EXISTS movimentos_caixa (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  caixa_id    INT UNSIGNED  NOT NULL,
  venda_id    INT UNSIGNED  DEFAULT NULL,
  tipo        ENUM('venda','entrada','saida','sangria','suprimento','devolucao') NOT NULL,
  valor       DECIMAL(12,2) NOT NULL,
  descricao   VARCHAR(255)  DEFAULT NULL,
  usuario_id  INT UNSIGNED  NOT NULL,
  criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mov_caixa_caixa (caixa_id),
  KEY idx_mov_caixa_venda (venda_id),
  KEY idx_mov_caixa_tipo (tipo),
  KEY idx_mov_caixa_data (criado_em),
  CONSTRAINT fk_mov_caixa_caixa
    FOREIGN KEY (caixa_id) REFERENCES caixa (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_mov_caixa_venda
    FOREIGN KEY (venda_id) REFERENCES vendas (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_mov_caixa_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Movimentos financeiros por sessão de caixa';


-- =============================================================
-- 14. ESTOQUE_MOVIMENTOS
-- Auditoria completa de todas as entradas e saídas de stock
-- =============================================================
CREATE TABLE IF NOT EXISTS estoque_movimentos (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  produto_id    INT UNSIGNED  NOT NULL,
  lote_id       INT UNSIGNED  DEFAULT NULL,
  tipo          ENUM('entrada','saida','ajuste_positivo','ajuste_negativo','devolucao_cliente','devolucao_fornecedor','perda','vencimento') NOT NULL,
  quantidade    INT           NOT NULL,
  quantidade_anterior INT     NOT NULL DEFAULT 0,
  quantidade_posterior INT    NOT NULL DEFAULT 0,
  referencia    VARCHAR(60)   DEFAULT NULL,  -- ex: VD-2025-00001 ou CP-2025-00001
  usuario_id    INT UNSIGNED  DEFAULT NULL,
  observacoes   TEXT          DEFAULT NULL,
  criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_estoque_mov_produto (produto_id),
  KEY idx_estoque_mov_lote (lote_id),
  KEY idx_estoque_mov_tipo (tipo),
  KEY idx_estoque_mov_data (criado_em),
  CONSTRAINT fk_estoque_mov_produto
    FOREIGN KEY (produto_id) REFERENCES produtos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_estoque_mov_lote
    FOREIGN KEY (lote_id) REFERENCES lotes (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_estoque_mov_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico completo de movimentos de stock';


-- =============================================================
-- 15. CONFIGURACOES
-- Parâmetros globais do sistema
-- =============================================================
CREATE TABLE IF NOT EXISTS configuracoes (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  chave       VARCHAR(80)   NOT NULL,
  valor       TEXT          DEFAULT NULL,
  descricao   VARCHAR(255)  DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_configuracoes_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Configurações e parâmetros globais do sistema';


-- =============================================================
-- TRIGGERS
-- Automação de stock e numeração de documentos
-- =============================================================

DELIMITER $$

-- Actualiza o stock do produto após inserção de item de venda
CREATE TRIGGER trg_after_item_venda_insert
AFTER INSERT ON itens_venda
FOR EACH ROW
BEGIN
  UPDATE produtos
    SET estoque_actual = estoque_actual - NEW.quantidade
  WHERE id = NEW.produto_id;

  -- actualiza quantidade do lote se especificado
  IF NEW.lote_id IS NOT NULL THEN
    UPDATE lotes
      SET quantidade = quantidade - NEW.quantidade
    WHERE id = NEW.lote_id;
  END IF;

  -- regista movimento de stock
  INSERT INTO estoque_movimentos
    (produto_id, lote_id, tipo, quantidade, quantidade_anterior, quantidade_posterior, referencia)
  SELECT
    NEW.produto_id,
    NEW.lote_id,
    'saida',
    NEW.quantidade,
    estoque_actual + NEW.quantidade,
    estoque_actual,
    (SELECT numero_venda FROM vendas WHERE id = NEW.venda_id)
  FROM produtos WHERE id = NEW.produto_id;
END$$


-- Repõe o stock ao cancelar uma venda (actualiza cada item)
CREATE TRIGGER trg_after_venda_cancel
AFTER UPDATE ON vendas
FOR EACH ROW
BEGIN
  IF NEW.status = 'cancelada' AND OLD.status != 'cancelada' THEN
    UPDATE produtos p
      JOIN itens_venda iv ON iv.produto_id = p.id
      SET p.estoque_actual = p.estoque_actual + iv.quantidade
    WHERE iv.venda_id = NEW.id;

    UPDATE lotes l
      JOIN itens_venda iv ON iv.lote_id = l.id
      SET l.quantidade = l.quantidade + iv.quantidade
    WHERE iv.venda_id = NEW.id;
  END IF;
END$$


-- Actualiza stock após recepção de compra (itens_compra)
CREATE TRIGGER trg_after_item_compra_update
AFTER UPDATE ON itens_compra
FOR EACH ROW
BEGIN
  DECLARE diff INT;
  SET diff = NEW.quantidade_recebida - OLD.quantidade_recebida;

  IF diff > 0 THEN
    UPDATE produtos
      SET estoque_actual = estoque_actual + diff
    WHERE id = NEW.produto_id;

    -- regista movimento de stock
    INSERT INTO estoque_movimentos
      (produto_id, tipo, quantidade, quantidade_anterior, quantidade_posterior, referencia)
    SELECT
      NEW.produto_id,
      'entrada',
      diff,
      estoque_actual - diff,
      estoque_actual,
      (SELECT numero_compra FROM compras WHERE id = NEW.compra_id)
    FROM produtos WHERE id = NEW.produto_id;
  END IF;
END$$


DELIMITER ;


-- =============================================================
-- DADOS INICIAIS
-- =============================================================

-- Utilizador administrador padrão
-- Senha: Admin@2025  (hash bcrypt — substituir antes de produção)
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
('Administrador', 'admin@kewanfarma.mz', '$2y$12$exampleHashSubstituirEmProducao123456789', 'admin');


-- Categorias principais
INSERT INTO categorias (nome, descricao) VALUES
('Medicamentos',         'Produtos farmacêuticos em geral'),
('Dermocosméticos',      'Produtos para cuidado da pele e higiene'),
('Suplementos',          'Vitaminas, minerais e suplementos alimentares'),
('Equipamentos',         'Aparelhos e equipamentos de saúde'),
('Higiene e Bebé',       'Produtos de higiene pessoal e para bebé'),
('Primeiros Socorros',   'Material de penso e primeiros socorros');

-- Subcategorias de Medicamentos (pai = 1)
INSERT INTO categorias (nome, descricao, categoria_pai_id) VALUES
('Antibióticos',         'Medicamentos antibacterianos', 1),
('Anti-inflamatórios',   'Medicamentos anti-inflamatórios e analgésicos', 1),
('Antiparasitários',     'Medicamentos contra parasitas', 1),
('Antimaláricos',        'Medicamentos para tratamento e prevenção da malária', 1),
('Cardiovasculares',     'Medicamentos para o sistema cardiovascular', 1),
('Antidiabéticos',       'Insulinas e hipoglicemiantes orais', 1);


-- Configurações iniciais do sistema
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('nome_farmacia',        'KewanFarma',                           'Nome da farmácia'),
('nuit_farmacia',        '',                                     'NUIT da farmácia'),
('endereco_farmacia',    '',                                     'Endereço completo'),
('telefone_farmacia',    '',                                     'Telefone de contacto'),
('email_farmacia',       '',                                     'Email de contacto'),
('moeda',                'MZN',                                  'Moeda utilizada no sistema'),
('iva_percentagem',      '16',                                   'Percentagem de IVA aplicada'),
('prefixo_venda',        'VD',                                   'Prefixo para numeração de vendas'),
('prefixo_compra',       'CP',                                   'Prefixo para numeração de compras'),
('dias_alerta_validade', '90',                                   'Dias de antecedência para alerta de validade'),
('versao_sistema',       '1.0.0',                                'Versão actual do sistema');


-- =============================================================
-- VIEWS ÚTEIS
-- =============================================================

-- Produtos com stock abaixo do mínimo (alerta de ruptura)
CREATE OR REPLACE VIEW vw_produtos_stock_baixo AS
SELECT
  p.id,
  p.nome,
  p.codigo_barras,
  c.nome        AS categoria,
  p.estoque_actual,
  p.estoque_min,
  (p.estoque_min - p.estoque_actual) AS deficit,
  f.nome        AS fornecedor
FROM produtos p
JOIN categorias c ON c.id = p.categoria_id
LEFT JOIN fornecedores f ON f.id = p.fornecedor_id
WHERE p.estoque_actual < p.estoque_min
  AND p.ativo = 1;


-- Lotes a vencer nos próximos N dias (usa configuração de alerta)
CREATE OR REPLACE VIEW vw_lotes_a_vencer AS
SELECT
  l.id,
  p.nome        AS produto,
  l.numero_lote,
  l.quantidade,
  l.validade,
  DATEDIFF(l.validade, CURRENT_DATE) AS dias_restantes
FROM lotes l
JOIN produtos p ON p.id = l.produto_id
WHERE l.validade >= CURRENT_DATE
  AND DATEDIFF(l.validade, CURRENT_DATE) <= (
        SELECT COALESCE(CAST(valor AS UNSIGNED), 90)
        FROM configuracoes WHERE chave = 'dias_alerta_validade'
      )
  AND l.quantidade > 0
ORDER BY l.validade ASC;


-- Resumo de vendas por dia
CREATE OR REPLACE VIEW vw_vendas_por_dia AS
SELECT
  DATE(v.criado_em)          AS data,
  COUNT(v.id)                AS total_vendas,
  SUM(v.total)               AS valor_total,
  SUM(v.desconto)            AS descontos_concedidos,
  AVG(v.total)               AS ticket_medio
FROM vendas v
WHERE v.status = 'concluida'
GROUP BY DATE(v.criado_em)
ORDER BY data DESC;


-- Top 10 produtos mais vendidos (últimos 30 dias)
CREATE OR REPLACE VIEW vw_top_produtos_30d AS
SELECT
  p.id,
  p.nome,
  SUM(iv.quantidade)         AS unidades_vendidas,
  SUM(iv.subtotal)           AS valor_total,
  COUNT(DISTINCT iv.venda_id) AS num_vendas
FROM itens_venda iv
JOIN produtos p  ON p.id  = iv.produto_id
JOIN vendas v    ON v.id  = iv.venda_id
WHERE v.status = 'concluida'
  AND v.criado_em >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
GROUP BY p.id, p.nome
ORDER BY unidades_vendidas DESC
LIMIT 10;


-- =============================================================
SET FOREIGN_KEY_CHECKS = 1;
-- =============================================================
-- FIM DO SCRIPT — KewanFarma v1.0.0
-- =============================================================
