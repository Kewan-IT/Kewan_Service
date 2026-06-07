/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: kewanfarma
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `caixa`
--

DROP TABLE IF EXISTS `caixa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `caixa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `saldo_inicial` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_final` decimal(12,2) DEFAULT NULL,
  `total_vendas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_entradas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_saidas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('aberto','fechado') NOT NULL DEFAULT 'aberto',
  `observacoes` text DEFAULT NULL,
  `aberto_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `fechado_em` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_caixa_usuario` (`usuario_id`),
  KEY `idx_caixa_status` (`status`),
  KEY `idx_caixa_aberto_em` (`aberto_em`),
  CONSTRAINT `fk_caixa_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sessões de abertura e fecho de caixa';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caixa`
--

LOCK TABLES `caixa` WRITE;
/*!40000 ALTER TABLE `caixa` DISABLE KEYS */;
INSERT INTO `caixa` VALUES
(1,2,3658.50,6700.00,50.00,3658.50,120.00,'fechado','','2026-05-24 04:22:06','2026-05-24 04:27:24'),
(2,2,150.00,150.00,0.00,150.00,0.00,'fechado','','2026-05-24 04:30:02','2026-05-24 04:38:21'),
(3,2,1480.00,2422.00,2422.00,0.00,0.00,'fechado','','2026-05-24 04:38:44','2026-05-24 04:51:46'),
(4,2,983.00,397.00,414.00,414.00,1000.00,'fechado','','2026-05-24 05:00:32','2026-05-24 05:07:05'),
(5,2,0.00,18.00,20.00,20.00,0.00,'fechado','','2026-05-24 05:21:59','2026-05-24 08:48:48'),
(6,1,0.00,384.00,260.00,384.00,0.00,'fechado','','2026-05-24 14:40:53','2026-05-24 14:51:38'),
(7,1,521.00,780.00,260.00,260.00,0.00,'fechado','Valor encontrado na manha\n[Fecho: O valor em falta comprou-se saco plastico]','2026-06-01 07:22:35','2026-06-01 18:19:48'),
(8,1,1000.00,1050.00,80.00,80.00,50.00,'fechado','','2026-06-02 04:25:27','2026-06-02 04:35:09'),
(9,1,0.00,0.00,0.00,0.00,100.00,'fechado','O dia comecou sem nenhum dinheiro nma caixa\n[Fecho: Nao ouve vendas]','2026-06-04 17:54:23','2026-06-05 18:48:59'),
(10,1,0.00,3257.00,3307.00,3307.00,50.00,'fechado','','2026-06-05 18:50:30','2026-06-07 05:51:29');
/*!40000 ALTER TABLE `caixa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `salario_base` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cargos_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cargos e funções dos funcionários da farmácia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos`
--

LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
INSERT INTO `cargos` VALUES
(1,'Director Técnico','Responsável técnico e científico da farmácia',80000.00,1,'2026-05-21 08:58:54'),
(2,'Farmacêutico','Dispensa medicamentos e orienta os clientes',55000.00,1,'2026-05-21 08:58:54'),
(3,'Técnico de Farmácia','Apoio à dispensa sob supervisão farmacêutica',35000.00,1,'2026-05-21 08:58:54'),
(4,'Operador de Caixa','Processamento de vendas e pagamentos',28000.00,1,'2026-05-21 08:58:54'),
(5,'Administrativo','Gestão administrativa e de arquivo',30000.00,1,'2026-05-21 08:58:54'),
(6,'Gestor de Stock','Controlo de entradas, saídas e validades',32000.00,1,'2026-05-21 08:58:54'),
(7,'Auxiliar de Limpeza','Higiene e limpeza das instalações',18000.00,1,'2026-05-21 08:58:54');
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_pai_id` int(10) unsigned DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categorias_nome` (`nome`),
  KEY `idx_categorias_pai` (`categoria_pai_id`),
  CONSTRAINT `fk_categorias_pai` FOREIGN KEY (`categoria_pai_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorias de produtos com suporte a hierarquia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES
(1,'Medicamentos','Produtos farmacêuticos em geral',NULL,1,'2026-05-21 08:50:44'),
(2,'Dermocosméticos','Produtos para cuidado da pele e higiene',NULL,1,'2026-05-21 08:50:44'),
(3,'Suplementos','Vitaminas, minerais e suplementos alimentares',NULL,1,'2026-05-21 08:50:44'),
(4,'Equipamentos','Aparelhos e equipamentos de saúde',NULL,1,'2026-05-21 08:50:44'),
(5,'Higiene e Bebé','Produtos de higiene pessoal e para bebé',NULL,1,'2026-05-21 08:50:44'),
(6,'Primeiros Socorros','Material de penso e primeiros socorros',NULL,1,'2026-05-21 08:50:44'),
(7,'Antibióticos','Medicamentos antibacterianos',1,1,'2026-05-21 08:50:44'),
(8,'Anti-inflamatórios','Medicamentos anti-inflamatórios e analgésicos',1,1,'2026-05-21 08:50:44'),
(9,'Antiparasitários','Medicamentos contra parasitas',1,1,'2026-05-21 08:50:44'),
(10,'Antimaláricos','Medicamentos para tratamento e prevenção da malária',1,1,'2026-05-21 08:50:44'),
(11,'Cardiovasculares','Medicamentos para o sistema cardiovascular',1,1,'2026-05-21 08:50:44'),
(12,'Antidiabéticos','Insulinas e hipoglicemiantes orais',1,1,'2026-05-21 08:50:44'),
(13,'Analgésico',NULL,NULL,1,'2026-05-22 09:21:51'),
(14,'Anti-inflamatório',NULL,NULL,1,'2026-05-22 09:21:51'),
(15,'Analgésico Opioide',NULL,NULL,1,'2026-05-22 09:21:51'),
(16,'Antiplaquetário',NULL,NULL,1,'2026-05-22 09:21:51'),
(17,'Antiulceroso',NULL,NULL,1,'2026-05-22 09:21:51'),
(18,'Antiácido',NULL,NULL,1,'2026-05-22 09:21:51'),
(19,'Antiflatulento',NULL,NULL,1,'2026-05-22 09:21:51'),
(20,'Antidiarreico',NULL,NULL,1,'2026-05-22 09:21:51'),
(21,'Reidratação',NULL,NULL,1,'2026-05-22 09:21:51'),
(22,'Antiemético',NULL,NULL,1,'2026-05-22 09:21:51'),
(23,'Laxante',NULL,NULL,1,'2026-05-22 09:21:51'),
(24,'Antiparasitário',NULL,NULL,1,'2026-05-22 09:21:51'),
(25,'Antibiótico',NULL,NULL,1,'2026-05-22 09:21:51'),
(26,'Antifúngico',NULL,NULL,1,'2026-05-22 09:21:51'),
(27,'Antiviral',NULL,NULL,1,'2026-05-22 09:21:51'),
(28,'Antirretroviral',NULL,NULL,1,'2026-05-22 09:21:51'),
(29,'Antimalárico',NULL,NULL,1,'2026-05-22 09:21:51'),
(30,'Antidiabético',NULL,NULL,1,'2026-05-22 09:21:51'),
(31,'Anti-hipertensivo',NULL,NULL,1,'2026-05-22 09:21:51'),
(32,'Diurético',NULL,NULL,1,'2026-05-22 09:21:51'),
(33,'Hipolipemiante',NULL,NULL,1,'2026-05-22 09:21:51'),
(34,'Anticoagulante',NULL,NULL,1,'2026-05-22 09:21:51'),
(35,'Broncodilatador',NULL,NULL,1,'2026-05-22 09:21:51'),
(36,'Corticoide',NULL,NULL,1,'2026-05-22 09:21:51'),
(37,'Antialérgico',NULL,NULL,1,'2026-05-22 09:21:51'),
(38,'Ansiolítico',NULL,NULL,1,'2026-05-22 09:21:51'),
(39,'Antidepressivo',NULL,NULL,1,'2026-05-22 09:21:51'),
(40,'Antipsicótico',NULL,NULL,1,'2026-05-22 09:21:51'),
(41,'Anticonvulsivante',NULL,NULL,1,'2026-05-22 09:21:51'),
(42,'Hormonal',NULL,NULL,1,'2026-05-22 09:21:51'),
(43,'Antitireoidiano',NULL,NULL,1,'2026-05-22 09:21:51'),
(44,'Vitamina',NULL,NULL,1,'2026-05-22 09:21:51'),
(45,'Antianêmico',NULL,NULL,1,'2026-05-22 09:21:51'),
(46,'Suplemento',NULL,NULL,1,'2026-05-22 09:21:51'),
(47,'Antisséptico',NULL,NULL,1,'2026-05-22 09:21:51'),
(48,'Dermatológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(49,'Oftálmico',NULL,NULL,1,'2026-05-22 09:21:51'),
(50,'Otológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(51,'Hospitalar',NULL,NULL,1,'2026-05-22 09:21:51'),
(52,'Eletrólito',NULL,NULL,1,'2026-05-22 09:21:51'),
(53,'Emergência',NULL,NULL,1,'2026-05-22 09:21:51'),
(54,'Anestésico',NULL,NULL,1,'2026-05-22 09:21:51'),
(55,'Sedativo',NULL,NULL,1,'2026-05-22 09:21:51'),
(56,'Obstétrico',NULL,NULL,1,'2026-05-22 09:21:51'),
(57,'Contraceptivo',NULL,NULL,1,'2026-05-22 09:21:51'),
(58,'Urológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(59,'Antigotoso',NULL,NULL,1,'2026-05-22 09:21:51'),
(60,'Imunomodulador',NULL,NULL,1,'2026-05-22 09:21:51'),
(61,'Imunossupressor',NULL,NULL,1,'2026-05-22 09:21:51'),
(62,'Antineoplásico',NULL,NULL,1,'2026-05-22 09:21:51'),
(63,'Probiótico',NULL,NULL,1,'2026-05-22 09:21:51'),
(64,'Mucolítico',NULL,NULL,1,'2026-05-22 09:21:51'),
(65,'Expectorante',NULL,NULL,1,'2026-05-22 09:21:51'),
(66,'Antitussígeno',NULL,NULL,1,'2026-05-22 09:21:51'),
(67,'Antiasmático',NULL,NULL,1,'2026-05-22 09:21:51'),
(68,'Antimicobacteriano',NULL,NULL,1,'2026-05-22 09:21:51'),
(69,'Antituberculoso',NULL,NULL,1,'2026-05-22 09:21:51'),
(70,'Antiparkinsoniano',NULL,NULL,1,'2026-05-22 09:21:51'),
(71,'Neurológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(72,'Cardiovascular',NULL,NULL,1,'2026-05-22 09:21:51'),
(73,'Antiarrítmico',NULL,NULL,1,'2026-05-22 09:21:51'),
(74,'Vasodilatador',NULL,NULL,1,'2026-05-22 09:21:51'),
(75,'Antivertiginoso',NULL,NULL,1,'2026-05-22 09:21:51'),
(76,'Antiespasmódico',NULL,NULL,1,'2026-05-22 09:21:51'),
(77,'Gastrointestinal',NULL,NULL,1,'2026-05-22 09:21:51'),
(78,'Fertilidade',NULL,NULL,1,'2026-05-22 09:21:51'),
(79,'Hemostático',NULL,NULL,1,'2026-05-22 09:21:51'),
(80,'Ginecológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(81,'Hipnótico',NULL,NULL,1,'2026-05-22 09:21:51'),
(82,'Terapia Antitabagismo',NULL,NULL,1,'2026-05-22 09:21:51'),
(83,'Controle de Peso',NULL,NULL,1,'2026-05-22 09:21:51'),
(84,'Osteoarticular',NULL,NULL,1,'2026-05-22 09:21:51'),
(85,'Osteoporose',NULL,NULL,1,'2026-05-22 09:21:51'),
(86,'Antídoto',NULL,NULL,1,'2026-05-22 09:21:51'),
(87,'Nefrológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(88,'Hematológico',NULL,NULL,1,'2026-05-22 09:21:51'),
(89,'Bloqueador Neuromuscular',NULL,NULL,1,'2026-05-22 09:21:51'),
(90,'Descongestionante',NULL,NULL,1,'2026-05-22 09:21:51'),
(91,'Antigripais','Medicamentos para gripe e constipação',1,1,'2026-06-06 05:06:51'),
(92,'Gastrointestinais','Medicamentos para o sistema digestivo',1,1,'2026-06-06 05:06:51'),
(93,'Respiratórios','Medicamentos para o sistema respiratório',1,1,'2026-06-06 05:06:51'),
(94,'Antifúngicos','Medicamentos antifúngicos',1,1,'2026-06-06 05:06:51'),
(95,'Antivirais','Medicamentos antivirais',1,1,'2026-06-06 05:06:51'),
(96,'Antihipertensores','Medicamentos para hipertensão arterial',1,1,'2026-06-06 05:06:51'),
(97,'SNC','Medicamentos para o sistema nervoso central',1,1,'2026-06-06 05:06:51'),
(98,'Oftalmológicos','Medicamentos para os olhos',1,1,'2026-06-06 05:06:51'),
(99,'Dermatológicos','Medicamentos para a pele',1,1,'2026-06-06 05:06:51'),
(100,'Analgésicos','Medicamentos para alívio da dor',1,1,'2026-06-06 05:06:51'),
(101,'Antialérgicos','Medicamentos para alergias',1,1,'2026-06-06 05:06:51'),
(102,'Antieméticos','Medicamentos para náuseas e vómitos',1,1,'2026-06-06 05:06:51'),
(103,'Vitaminas','Vitaminas e complexos vitamínicos',3,1,'2026-06-06 05:06:51'),
(104,'Minerais','Minerais e suplementos minerais',3,1,'2026-06-06 05:06:51'),
(105,'Contraceptivos','Métodos contraceptivos hormonais',1,1,'2026-06-06 05:06:51');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `nuit` varchar(20) DEFAULT NULL,
  `bi` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `sexo` enum('M','F','outro') DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clientes_nuit` (`nuit`),
  KEY `idx_clientes_nome` (`nome`),
  KEY `idx_clientes_telefone` (`telefone`),
  KEY `idx_clientes_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clientes da farmácia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES
(1,'Nildo Manuel','845975821','040402584567N','876200584','nildoalberto@gmail.com','12 de Outubro_Gile-Sede','2026-11-27','M','E alergico em diclofenaco',1,'2026-05-22 10:21:02','2026-05-22 10:21:02');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `numero_compra` varchar(20) NOT NULL,
  `fornecedor_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `numero_fatura` varchar(60) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `desconto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('rascunho','enviada','parcialmente_recebida','recebida','cancelada') NOT NULL DEFAULT 'rascunho',
  `data_pedido` date NOT NULL DEFAULT curdate(),
  `data_entrega` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_compras_numero` (`numero_compra`),
  KEY `idx_compras_fornecedor` (`fornecedor_id`),
  KEY `idx_compras_usuario` (`usuario_id`),
  KEY `idx_compras_status` (`status`),
  KEY `idx_compras_data` (`data_pedido`),
  CONSTRAINT `fk_compras_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cabeçalho das ordens de compra a fornecedores';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES
(1,'CP-2026-00001',1,1,'ESN485E',44000.00,0.00,44000.00,'recebida','2026-05-23','2026-05-28',NULL,'2026-05-23 19:59:45','2026-05-23 20:38:48'),
(2,'CP-2026-00002',1,1,'SGRET5YR',332.80,0.00,332.80,'parcialmente_recebida','2026-05-24',NULL,NULL,'2026-05-23 22:18:23','2026-05-24 03:51:17'),
(3,'CP-2026-00003',3,1,'CHJDFHG64',117249.11,0.00,117249.11,'cancelada','2026-05-25','2026-06-05',NULL,'2026-05-25 00:26:32','2026-05-25 00:27:08'),
(4,'CP-2026-00004',4,1,'ERWYGIUS',2196.00,0.00,2196.00,'recebida','2026-06-02','2026-06-10',NULL,'2026-06-02 04:39:21','2026-06-06 12:09:59');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chave` varchar(80) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_configuracoes_chave` (`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configurações e parâmetros globais do sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracoes`
--

LOCK TABLES `configuracoes` WRITE;
/*!40000 ALTER TABLE `configuracoes` DISABLE KEYS */;
INSERT INTO `configuracoes` VALUES
(1,'nome_farmacia','Kewan_Farma','Nome da farmácia'),
(2,'nuit_farmacia','15874656','NUIT da farmácia'),
(3,'endereco_farmacia','Nampula, Nampula, Mutauanha, Piloto','Endereço da farmácia'),
(4,'telefone_farmacia','868900224','Telefone da farmácia'),
(5,'email_farmacia','','Email de contacto da farmácia'),
(6,'moeda','MZN','Moeda utilizada no sistema'),
(7,'iva_percentagem','16','Percentagem de IVA aplicada'),
(8,'prefixo_venda','VD','Prefixo das vendas'),
(9,'prefixo_compra','CP','Prefixo das compras'),
(10,'dias_alerta_validade','90','Dias de antecedência para alertas de validade'),
(11,'versao_sistema','1.0.0','Versão actual do sistema'),
(22,'logo_farmacia','logos/logo_1779667808.png','Logo da farmácia'),
(64,'backup_hora_automatico','08:30','Hora diária para execução do backup automático');
/*!40000 ALTER TABLE `configuracoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credenciais_historico`
--

DROP TABLE IF EXISTS `credenciais_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `credenciais_historico` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `funcionario_id` int(10) unsigned NOT NULL,
  `acao` enum('criacao','alteracao_perfil','alteracao_senha','bloqueio','desbloqueio','desactivacao','reactivacao') NOT NULL,
  `perfil_anterior` enum('admin','farmaceutico','caixa','tecnico') DEFAULT NULL,
  `perfil_novo` enum('admin','farmaceutico','caixa','tecnico') DEFAULT NULL,
  `executado_por` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cred_hist_usuario` (`usuario_id`),
  KEY `idx_cred_hist_funcionario` (`funcionario_id`),
  KEY `idx_cred_hist_executado` (`executado_por`),
  KEY `idx_cred_hist_data` (`criado_em`),
  CONSTRAINT `fk_cred_hist_executado_por` FOREIGN KEY (`executado_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cred_hist_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cred_hist_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoria completa de criação e alteração de credenciais';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credenciais_historico`
--

LOCK TABLES `credenciais_historico` WRITE;
/*!40000 ALTER TABLE `credenciais_historico` DISABLE KEYS */;
INSERT INTO `credenciais_historico` VALUES
(1,2,1,'criacao',NULL,'admin',1,NULL,NULL,'2026-05-21 22:10:39'),
(2,3,2,'criacao',NULL,'farmaceutico',2,NULL,NULL,'2026-05-21 22:21:06'),
(3,3,2,'desactivacao','farmaceutico','farmaceutico',2,NULL,NULL,'2026-05-21 22:57:14'),
(4,3,2,'reactivacao','farmaceutico','farmaceutico',2,NULL,NULL,'2026-05-22 15:41:45'),
(5,4,3,'criacao',NULL,'farmaceutico',1,NULL,NULL,'2026-06-04 18:21:43');
/*!40000 ALTER TABLE `credenciais_historico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque_movimentos`
--

DROP TABLE IF EXISTS `estoque_movimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_movimentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int(10) unsigned NOT NULL,
  `lote_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('entrada','saida','ajuste_positivo','ajuste_negativo','devolucao_cliente','devolucao_fornecedor','perda','vencimento') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `quantidade_anterior` int(11) NOT NULL DEFAULT 0,
  `quantidade_posterior` int(11) NOT NULL DEFAULT 0,
  `referencia` varchar(60) DEFAULT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_estoque_mov_produto` (`produto_id`),
  KEY `idx_estoque_mov_lote` (`lote_id`),
  KEY `idx_estoque_mov_tipo` (`tipo`),
  KEY `idx_estoque_mov_data` (`criado_em`),
  KEY `fk_estoque_mov_usuario` (`usuario_id`),
  CONSTRAINT `fk_estoque_mov_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_estoque_mov_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_estoque_mov_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico completo de movimentos de stock';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque_movimentos`
--

LOCK TABLES `estoque_movimentos` WRITE;
/*!40000 ALTER TABLE `estoque_movimentos` DISABLE KEYS */;
INSERT INTO `estoque_movimentos` VALUES
(1,1,NULL,'entrada',354,354,708,NULL,1,NULL,'2026-05-22 05:31:03'),
(2,1,NULL,'entrada',38,392,430,NULL,1,NULL,'2026-05-22 05:31:57'),
(3,2,NULL,'entrada',54,54,108,NULL,1,NULL,'2026-05-22 05:40:15'),
(4,3,NULL,'entrada',400,400,800,NULL,1,NULL,'2026-05-22 09:33:08'),
(5,4,NULL,'entrada',48,48,96,NULL,1,NULL,'2026-05-22 09:35:56'),
(6,5,NULL,'entrada',69,69,138,NULL,1,NULL,'2026-05-22 09:38:39'),
(7,8,NULL,'entrada',100,100,200,NULL,1,NULL,'2026-05-22 09:51:17'),
(8,9,NULL,'entrada',35,35,70,NULL,1,NULL,'2026-05-22 10:00:48'),
(9,10,NULL,'entrada',5,5,10,NULL,1,NULL,'2026-05-22 10:03:34'),
(10,11,NULL,'entrada',14,14,28,NULL,1,NULL,'2026-05-22 10:06:48'),
(12,4,NULL,'saida',1,48,47,'VD-2026-00001',NULL,NULL,'2026-05-23 05:07:54'),
(13,4,NULL,'saida',3,46,43,'VD-2026-00002',NULL,NULL,'2026-05-23 05:11:04'),
(14,4,NULL,'saida',2,40,38,'VD-2026-00003',NULL,NULL,'2026-05-23 05:12:47'),
(15,11,NULL,'saida',2,14,12,'VD-2026-00004',NULL,NULL,'2026-05-23 10:15:06'),
(16,10,NULL,'saida',1,5,4,'VD-2026-00005',NULL,NULL,'2026-05-23 10:17:24'),
(17,8,NULL,'saida',1,100,99,'VD-2026-00006',NULL,NULL,'2026-05-23 10:20:39'),
(18,2,NULL,'saida',4,54,50,'VD-2026-00007',NULL,NULL,'2026-05-23 11:10:22'),
(19,4,NULL,'saida',2,36,34,'VD-2026-00007',NULL,NULL,'2026-05-23 11:10:22'),
(20,2,NULL,'saida',3,46,43,'VD-2026-00008',NULL,NULL,'2026-05-23 12:26:42'),
(21,11,NULL,'saida',2,10,8,'VD-2026-00008',NULL,NULL,'2026-05-23 12:26:42'),
(22,8,NULL,'saida',1,98,97,'VD-2026-00008',NULL,NULL,'2026-05-23 12:26:42'),
(23,9,NULL,'saida',1,35,34,'VD-2026-00009',NULL,NULL,'2026-05-23 12:28:38'),
(24,13,NULL,'entrada',20,20,40,NULL,3,NULL,'2026-05-23 13:55:19'),
(25,11,NULL,'entrada',26,32,58,NULL,2,NULL,'2026-05-23 14:06:43'),
(26,11,NULL,'saida',15,32,17,'VD-2026-00010',NULL,NULL,'2026-05-23 14:08:43'),
(27,1,NULL,'saida',4,392,388,'VD-2026-00010',NULL,NULL,'2026-05-23 14:08:43'),
(28,11,NULL,'saida',2,2,0,'VD-2026-00011',NULL,NULL,'2026-05-23 14:10:22'),
(29,4,NULL,'entrada',500,32,532,'CP-2026-00001',NULL,NULL,'2026-05-23 20:38:48'),
(30,9,8,'saida',10,33,23,'VD-2026-00012',NULL,NULL,'2026-05-23 21:37:10'),
(31,3,4,'saida',8,400,392,'VD-2026-00012',NULL,NULL,'2026-05-23 21:37:10'),
(32,9,8,'saida',13,13,0,'VD-2026-00013',NULL,NULL,'2026-05-23 21:39:31'),
(33,7,NULL,'entrada',9,9,18,NULL,2,NULL,'2026-05-24 03:41:41'),
(34,7,NULL,'entrada',12,21,33,NULL,2,NULL,'2026-05-24 03:42:59'),
(35,7,14,'saida',9,21,12,'VD-2026-00014',NULL,NULL,'2026-05-24 03:44:10'),
(36,7,15,'saida',2,12,10,'VD-2026-00014',NULL,NULL,'2026-05-24 03:44:10'),
(37,7,15,'saida',3,10,7,'VD-2026-00015',NULL,NULL,'2026-05-24 03:45:54'),
(39,2,NULL,'entrada',10,40,50,'CP-2026-00002',NULL,NULL,'2026-05-24 03:51:17'),
(40,8,NULL,'entrada',17,96,113,'CP-2026-00002',NULL,NULL,'2026-05-24 03:51:17'),
(42,7,15,'saida',2,4,2,'VD-2026-00016',NULL,NULL,'2026-05-24 04:23:57'),
(43,2,3,'saida',3,60,57,'VD-2026-00017',NULL,NULL,'2026-05-24 04:24:38'),
(44,3,4,'saida',35,384,349,'VD-2026-00018',NULL,NULL,'2026-05-24 04:40:11'),
(45,1,1,'saida',7,384,377,'VD-2026-00018',NULL,NULL,'2026-05-24 04:40:11'),
(47,1,1,'saida',9,370,361,'VD-2026-00019',NULL,NULL,'2026-05-24 05:05:34'),
(48,2,3,'saida',2,54,52,'VD-2026-00020',NULL,NULL,'2026-05-24 08:46:20'),
(49,10,NULL,'entrada',36,39,75,NULL,1,NULL,'2026-05-24 14:37:16'),
(50,10,18,'saida',2,39,37,'VD-2026-00021',NULL,NULL,'2026-05-24 14:49:05'),
(51,4,5,'saida',1,1032,1031,'VD-2026-00021',NULL,NULL,'2026-05-24 14:49:05'),
(53,10,18,'saida',3,35,32,'VD-2026-00022',NULL,NULL,'2026-06-01 07:24:20'),
(54,2,3,'saida',5,54,49,'VD-2026-00022',NULL,NULL,'2026-06-01 07:24:20'),
(55,2,3,'saida',2,44,42,'VD-2026-00023',NULL,NULL,'2026-06-02 04:30:19'),
(56,5,6,'saida',3,69,66,'VD-2026-00023',NULL,NULL,'2026-06-02 04:30:19'),
(57,9,NULL,'entrada',10,0,10,'CP-2026-00004',NULL,NULL,'2026-06-02 04:40:50'),
(58,6,NULL,'entrada',20,0,20,'CP-2026-00004',NULL,NULL,'2026-06-02 04:40:50'),
(59,9,8,'saida',2,20,18,'VD-2026-00024',NULL,NULL,'2026-06-05 19:04:54'),
(60,9,19,'saida',1,18,17,'VD-2026-00024',NULL,NULL,'2026-06-05 19:04:54'),
(61,43,187,'saida',2,80,78,'VD-2026-00025',NULL,NULL,'2026-06-06 11:44:47'),
(62,30,204,'saida',1,100,99,'VD-2026-00025',NULL,NULL,'2026-06-06 11:44:47'),
(63,29,31,'saida',6,20,14,'VD-2026-00026',NULL,NULL,'2026-06-06 11:46:20'),
(64,9,NULL,'entrada',2,14,16,'CP-2026-00004',NULL,NULL,'2026-06-06 12:09:59');
/*!40000 ALTER TABLE `estoque_movimentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `nuit` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(80) DEFAULT NULL,
  `pais` varchar(80) NOT NULL DEFAULT 'Moçambique',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fornecedores_nuit` (`nuit`),
  KEY `idx_fornecedores_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fornecedores e distribuidores de produtos farmacêuticos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedores`
--

LOCK TABLES `fornecedores` WRITE;
/*!40000 ALTER TABLE `fornecedores` DISABLE KEYS */;
INSERT INTO `fornecedores` VALUES
(1,'ACE','894751280','859741258','ace@fornece.mz','Namutequeliua','Nampula','Moçambique',0,'2026-05-23 19:57:56','2026-05-24 13:41:44'),
(2,'Grupo Azevedos','98214574','844979183','sac-nampula@medis.co.mz','Av. do Trabalho, 952 Bloco 25','Nampula','Moçambique',1,'2026-05-24 13:32:28','2026-05-24 13:32:28'),
(3,'ACE HealthCare Limitada','59655157','846023894','info@ace-healthcare.com','AV. do Trabalho, Bairro de Namutequeliua, Praceta AA043','Nampula','Moçambique',1,'2026-05-24 13:37:25','2026-05-24 13:37:25'),
(4,'dkt Mozambique','15781465','848100540','info@dktmozambique.org','Rua de Xai-Xai Numero 52 Bairro Muahivire','Nampula','Moçambique',1,'2026-05-24 13:41:04','2026-05-24 13:41:04');
/*!40000 ALTER TABLE `fornecedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcionarios`
--

DROP TABLE IF EXISTS `funcionarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcionarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(200) NOT NULL,
  `data_nascimento` date NOT NULL,
  `sexo` enum('M','F','outro') NOT NULL,
  `estado_civil` enum('solteiro','casado','divorciado','viuvo','uniao_de_facto') DEFAULT NULL,
  `nacionalidade` varchar(80) NOT NULL DEFAULT 'Moçambicana',
  `naturalidade` varchar(100) DEFAULT NULL,
  `bi_numero` varchar(30) NOT NULL,
  `bi_validade` date DEFAULT NULL,
  `nuit` varchar(20) DEFAULT NULL,
  `nrps` varchar(30) DEFAULT NULL,
  `telefone_principal` varchar(20) NOT NULL,
  `telefone_alternativo` varchar(20) DEFAULT NULL,
  `email_pessoal` varchar(180) DEFAULT NULL,
  `endereco` varchar(255) NOT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(80) NOT NULL DEFAULT 'Quelimane',
  `provincia` varchar(80) NOT NULL DEFAULT 'Zambézia',
  `emergencia_nome` varchar(150) DEFAULT NULL,
  `emergencia_parentesco` varchar(60) DEFAULT NULL,
  `emergencia_telefone` varchar(20) DEFAULT NULL,
  `cargo_id` int(10) unsigned NOT NULL,
  `data_admissao` date NOT NULL,
  `data_saida` date DEFAULT NULL,
  `tipo_contrato` enum('efectivo','temporario','estagio','prestacao_servicos') NOT NULL DEFAULT 'efectivo',
  `salario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `numero_funcionario` varchar(20) NOT NULL,
  `nivel_escolaridade` enum('primario','secundario','tecnico_medio','licenciatura','mestrado','doutoramento') DEFAULT NULL,
  `curso` varchar(150) DEFAULT NULL,
  `instituicao` varchar(150) DEFAULT NULL,
  `ano_conclusao` year(4) DEFAULT NULL,
  `foto_url` varchar(500) DEFAULT NULL,
  `foto_mime` varchar(30) DEFAULT NULL,
  `doc_identificacao_url` varchar(500) DEFAULT NULL,
  `doc_identificacao_nome` varchar(150) DEFAULT NULL,
  `doc_identificacao_mime` varchar(30) DEFAULT NULL,
  `doc_complementar_url` varchar(500) DEFAULT NULL,
  `doc_complementar_nome` varchar(150) DEFAULT NULL,
  `doc_complementar_mime` varchar(30) DEFAULT NULL,
  `status` enum('activo','inactivo','suspenso','desligado') NOT NULL DEFAULT 'activo',
  `motivo_saida` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `criado_por` int(10) unsigned DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_funcionarios_bi` (`bi_numero`),
  UNIQUE KEY `uq_funcionarios_numero` (`numero_funcionario`),
  UNIQUE KEY `uq_funcionarios_nuit` (`nuit`),
  KEY `idx_funcionarios_cargo` (`cargo_id`),
  KEY `idx_funcionarios_status` (`status`),
  KEY `idx_funcionarios_admissao` (`data_admissao`),
  KEY `idx_funcionarios_nome` (`nome_completo`),
  KEY `fk_funcionarios_criado_por` (`criado_por`),
  CONSTRAINT `fk_funcionarios_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_funcionarios_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dados completos dos funcionários da KewanFarma';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcionarios`
--

LOCK TABLES `funcionarios` WRITE;
/*!40000 ALTER TABLE `funcionarios` DISABLE KEYS */;
INSERT INTO `funcionarios` VALUES
(1,'Patrao Manuel Alberto','1999-05-15','M','solteiro','Moçambicana','Gile-Sede','040402668166B','2019-08-18','1578498653','2015478','868900224','867769094','patraomanuelalberto@gmail.com','Nampula_Mutauanha_Piloto','Pilo','Nampula','Nampula','Almira Lusabio','Esposa','871271033',1,'2018-01-28',NULL,'efectivo',50000.00,'KF-0001','licenciatura','Farmacia','IGCS',2012,'funcionarios/fotos/KF-0001.jpg','image/jpeg','funcionarios/docs/KF-0001_bi.pdf','CV.pdf','application/pdf',NULL,'DOC-20260126-WA0000..PDF','','activo',NULL,NULL,1,'2026-05-21 22:08:34','2026-05-21 22:08:34'),
(2,'Labistruria Manuel','2000-06-19','F','solteiro','Moçambicana','Gile-Sede','112233445566H','2028-09-05','549874158','25498714','875849925',NULL,NULL,'Zambezia','12 de Outubro','Gile','Zambézia','Maria Joao','Mae','857239458',3,'2024-03-06',NULL,'efectivo',8000.00,'KF-0002','tecnico_medio','Farmacia','IGCS',2024,'funcionarios/fotos/KF-0002.jpg','image/jpeg','funcionarios/docs/KF-0002_bi.pdf','SAAJ orientações do funcionamento .pdf','application/pdf','funcionarios/docs/KF-0002_complementar.pdf','Patrão Manuel.pdf','application/pdf','activo',NULL,NULL,2,'2026-05-21 22:20:15','2026-06-06 11:42:32'),
(3,'Almira Lusabio Manuel','2000-05-05','F','uniao_de_facto','Moçambicana','Gile-Sede','040402688166B','2027-09-15','845523565',NULL,'879542103',NULL,NULL,'AV. do Trabalho, Bairro de Namutequeliua, Praceta AA043','Piloto','Quelimane','Zambézia',NULL,NULL,NULL,3,'2022-07-05',NULL,'efectivo',12000.00,'KF-0003','tecnico_medio','Farmacia','IGCS',2022,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'activo',NULL,NULL,1,'2026-06-04 18:20:52','2026-06-04 18:20:52');
/*!40000 ALTER TABLE `funcionarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_funcionario_status_sync
AFTER UPDATE ON funcionarios
FOR EACH ROW
BEGIN
  IF NEW.status IN ('inactivo','suspenso','desligado') AND OLD.status = 'activo' THEN
    UPDATE usuarios SET ativo = 0 WHERE funcionario_id = NEW.id;
  END IF;
  IF NEW.status = 'activo' AND OLD.status != 'activo' THEN
    UPDATE usuarios SET ativo = 1 WHERE funcionario_id = NEW.id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `funcionarios_documentos`
--

DROP TABLE IF EXISTS `funcionarios_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcionarios_documentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `funcionario_id` int(10) unsigned NOT NULL,
  `tipo` enum('cv','certificado','contrato','atestado','formacao','outro') NOT NULL DEFAULT 'outro',
  `titulo` varchar(150) NOT NULL,
  `ficheiro_url` varchar(500) NOT NULL,
  `ficheiro_nome` varchar(150) NOT NULL,
  `ficheiro_mime` varchar(50) NOT NULL DEFAULT 'application/pdf',
  `ficheiro_tamanho` int(10) unsigned DEFAULT NULL,
  `carregado_por` int(10) unsigned DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_func_docs_funcionario` (`funcionario_id`),
  KEY `idx_func_docs_tipo` (`tipo`),
  KEY `fk_func_docs_carregado_por` (`carregado_por`),
  CONSTRAINT `fk_func_docs_carregado_por` FOREIGN KEY (`carregado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_func_docs_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos adicionais dos funcionários';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcionarios_documentos`
--

LOCK TABLES `funcionarios_documentos` WRITE;
/*!40000 ALTER TABLE `funcionarios_documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `funcionarios_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itens_compra`
--

DROP TABLE IF EXISTS `itens_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens_compra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` int(10) unsigned NOT NULL,
  `produto_id` int(10) unsigned NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `numero_lote` varchar(60) DEFAULT NULL,
  `validade_lote` date DEFAULT NULL,
  `quantidade_recebida` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_itens_compra_compra` (`compra_id`),
  KEY `idx_itens_compra_produto` (`produto_id`),
  CONSTRAINT `fk_itens_compra_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_itens_compra_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Itens (linhas) de cada ordem de compra';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens_compra`
--

LOCK TABLES `itens_compra` WRITE;
/*!40000 ALTER TABLE `itens_compra` DISABLE KEYS */;
INSERT INTO `itens_compra` VALUES
(1,1,4,500,88.00,44000.00,'LOT-2026-0036','2028-12-30',500),
(2,2,2,15,8.31,124.65,NULL,NULL,10),
(3,2,8,23,9.05,208.15,NULL,NULL,17),
(4,3,14,789,78.99,62323.11,'LOTE-2026-184','2028-08-30',0),
(5,3,9,947,58.00,54926.00,'LOTE-2026-300','2027-09-03',0),
(6,4,9,12,58.00,696.00,NULL,NULL,12),
(7,4,6,20,75.00,1500.00,NULL,NULL,20);
/*!40000 ALTER TABLE `itens_compra` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_after_item_compra_update
AFTER UPDATE ON itens_compra
FOR EACH ROW
BEGIN
  DECLARE diff INT;
  SET diff = NEW.quantidade_recebida - OLD.quantidade_recebida;

  IF diff > 0 THEN
    UPDATE produtos
      SET estoque_actual = estoque_actual + diff
    WHERE id = NEW.produto_id;

    
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `itens_venda`
--

DROP TABLE IF EXISTS `itens_venda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens_venda` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` int(10) unsigned NOT NULL,
  `produto_id` int(10) unsigned NOT NULL,
  `lote_id` int(10) unsigned DEFAULT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `preco_unitario` decimal(12,2) NOT NULL,
  `desconto_item` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_itens_venda_venda` (`venda_id`),
  KEY `idx_itens_venda_produto` (`produto_id`),
  KEY `idx_itens_venda_lote` (`lote_id`),
  CONSTRAINT `fk_itens_venda_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_itens_venda_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_itens_venda_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Itens (linhas) de cada venda';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens_venda`
--

LOCK TABLES `itens_venda` WRITE;
/*!40000 ALTER TABLE `itens_venda` DISABLE KEYS */;
INSERT INTO `itens_venda` VALUES
(2,2,4,NULL,1,120.00,0.00,120.00),
(3,3,4,NULL,3,120.00,0.00,360.00),
(4,4,4,NULL,2,120.00,0.00,240.00),
(5,5,11,NULL,2,35.00,0.00,70.00),
(6,6,10,NULL,1,70.00,0.00,70.00),
(7,7,8,NULL,1,120.00,0.00,120.00),
(8,8,2,NULL,4,10.00,0.00,40.00),
(9,8,4,NULL,2,120.00,0.00,240.00),
(10,9,2,NULL,3,10.00,0.00,30.00),
(11,9,11,NULL,2,35.00,0.00,70.00),
(12,9,8,NULL,1,120.00,0.00,120.00),
(13,10,9,NULL,1,79.00,0.00,79.00),
(14,11,11,NULL,15,35.00,0.00,525.00),
(15,11,1,NULL,4,46.00,0.00,184.00),
(16,12,11,NULL,2,35.00,0.00,70.00),
(17,14,9,8,10,79.00,0.00,790.00),
(18,14,3,4,8,60.00,0.00,480.00),
(19,15,9,8,13,79.00,0.00,1027.00),
(20,16,7,14,9,10.00,0.00,90.00),
(21,16,7,15,2,10.00,0.00,110.00),
(22,17,7,15,3,10.00,0.00,30.00),
(23,18,7,15,2,10.00,0.00,20.00),
(24,19,2,3,3,10.00,0.00,30.00),
(25,20,3,4,35,60.00,0.00,2100.00),
(26,20,1,1,7,46.00,0.00,322.00),
(28,22,1,1,9,46.00,0.00,414.00),
(29,23,2,3,2,10.00,0.00,20.00),
(30,24,10,18,2,70.00,0.00,140.00),
(31,24,4,5,1,120.00,0.00,120.00),
(32,25,10,18,3,70.00,0.00,210.00),
(33,25,2,3,5,10.00,0.00,50.00),
(34,26,2,3,2,10.00,0.00,20.00),
(35,26,5,6,3,20.00,0.00,60.00),
(36,27,9,8,2,79.00,0.00,158.00),
(37,27,9,19,1,79.00,0.00,237.00),
(38,28,43,187,2,180.00,0.00,360.00),
(39,28,30,204,1,10.00,0.00,10.00),
(40,29,29,31,6,450.00,0.00,2700.00);
/*!40000 ALTER TABLE `itens_venda` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_after_item_venda_insert
AFTER INSERT ON itens_venda
FOR EACH ROW
BEGIN
  UPDATE produtos
    SET estoque_actual = estoque_actual - NEW.quantidade
  WHERE id = NEW.produto_id;

  
  IF NEW.lote_id IS NOT NULL THEN
    UPDATE lotes
      SET quantidade = quantidade - NEW.quantidade
    WHERE id = NEW.lote_id;
  END IF;

  
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `lotes`
--

DROP TABLE IF EXISTS `lotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int(10) unsigned NOT NULL,
  `numero_lote` varchar(60) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `validade` date NOT NULL,
  `data_entrada` date NOT NULL DEFAULT curdate(),
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lotes_produto_lote` (`produto_id`,`numero_lote`),
  KEY `idx_lotes_validade` (`validade`),
  KEY `idx_lotes_produto` (`produto_id`),
  CONSTRAINT `fk_lotes_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=222 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lotes de produtos com rastreabilidade de validade';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes`
--

LOCK TABLES `lotes` WRITE;
/*!40000 ALTER TABLE `lotes` DISABLE KEYS */;
INSERT INTO `lotes` VALUES
(1,1,'LOT-2026-001',322,'2026-06-30','2026-05-22',NULL,'2026-05-22 05:31:03'),
(2,1,'LOT-2026-002',38,'2028-08-30','2026-05-22',NULL,'2026-05-22 05:31:57'),
(3,2,'LOT-2026-003',38,'2027-01-30','2026-05-22',NULL,'2026-05-22 05:40:15'),
(4,3,'LOT-2026-009',314,'2027-07-30','2026-05-22',NULL,'2026-05-22 09:33:08'),
(5,4,'LOT-2026-010',46,'2026-06-03','2026-05-22',NULL,'2026-05-22 09:35:56'),
(6,5,'LOT-2026-011',69,'2029-05-30','2026-05-22',NULL,'2026-05-22 09:38:39'),
(7,8,'LOT-2026-020',100,'2029-06-28','2026-05-22',NULL,'2026-05-22 09:51:17'),
(8,9,'LOT-2026-023',0,'2027-07-30','2026-05-22',NULL,'2026-05-22 10:00:48'),
(9,10,'LOT-2026-059',5,'2026-02-09','2026-05-22',NULL,'2026-05-22 10:03:34'),
(10,11,'LOT-2026-087',14,'2027-06-05','2026-05-22',NULL,'2026-05-22 10:06:48'),
(11,13,'LOT-2026-077',20,'2026-04-09','2026-05-23',NULL,'2026-05-23 13:55:19'),
(12,11,'LOT-2026-100',26,'2028-07-18','2026-05-23',NULL,'2026-05-23 14:06:43'),
(13,4,'LOT-2026-0036',500,'2028-12-30','2026-05-23','Compra #1','2026-05-23 20:38:48'),
(14,7,'LOT-2026-101',0,'2026-05-24','2026-05-24',NULL,'2026-05-24 03:41:41'),
(15,7,'LOT-2026-102',0,'2027-04-30','2026-05-24',NULL,'2026-05-24 03:42:59'),
(16,2,'LOTE-2026-141',10,'2029-11-30','2026-05-24','Compra #2','2026-05-24 03:51:17'),
(17,8,'LOTE-2026-189',17,'2028-08-30','2026-05-24','Compra #2','2026-05-24 03:51:17'),
(18,10,'LOT-2026-122',26,'2028-08-01','2026-05-24',NULL,'2026-05-24 14:37:16'),
(19,9,'GGTYGSVJH',8,'2028-06-30','2026-06-02','Compra #4','2026-06-02 04:40:50'),
(20,6,'FTUSIUU',20,'2029-07-01','2026-06-02','Compra #4','2026-06-02 04:40:50'),
(21,167,'LOT-0167-2025',8,'2028-05-11','2026-06-06',NULL,'2026-06-06 05:06:51'),
(22,168,'LOT-0168-2025',10,'2027-05-22','2026-06-06',NULL,'2026-06-06 05:06:51'),
(23,26,'LOT-0026-2025',15,'2027-11-20','2026-06-06',NULL,'2026-06-06 05:06:51'),
(24,42,'LOT-0042-2025',15,'2027-05-26','2026-06-06',NULL,'2026-06-06 05:06:51'),
(25,182,'LOT-0182-2025',15,'2027-11-20','2026-06-06',NULL,'2026-06-06 05:06:51'),
(26,185,'LOT-0185-2025',15,'2027-05-10','2026-06-06',NULL,'2026-06-06 05:06:51'),
(27,186,'LOT-0186-2025',15,'2027-09-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(28,17,'LOT-0017-2025',20,'2027-12-10','2026-06-06',NULL,'2026-06-06 05:06:51'),
(29,23,'LOT-0023-2025',20,'2028-04-04','2026-06-06',NULL,'2026-06-06 05:06:51'),
(30,25,'LOT-0025-2025',20,'2027-08-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(31,29,'LOT-0029-2025',8,'2027-10-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(32,41,'LOT-0041-2025',20,'2027-08-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(33,82,'LOT-0082-2025',20,'2027-12-29','2026-06-06',NULL,'2026-06-06 05:06:51'),
(34,113,'LOT-0113-2025',20,'2027-02-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(35,118,'LOT-0118-2025',20,'2027-10-28','2026-06-06',NULL,'2026-06-06 05:06:51'),
(36,119,'LOT-0119-2025',20,'2027-10-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(37,165,'LOT-0165-2025',20,'2027-01-11','2026-06-06',NULL,'2026-06-06 05:06:51'),
(38,169,'LOT-0169-2025',20,'2027-11-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(39,183,'LOT-0183-2025',20,'2028-04-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(40,184,'LOT-0184-2025',20,'2027-11-29','2026-06-06',NULL,'2026-06-06 05:06:51'),
(41,187,'LOT-0187-2025',20,'2027-11-02','2026-06-06',NULL,'2026-06-06 05:06:51'),
(42,188,'LOT-0188-2025',20,'2027-01-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(43,189,'LOT-0189-2025',20,'2027-10-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(44,190,'LOT-0190-2025',20,'2027-11-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(45,195,'LOT-0195-2025',20,'2027-07-31','2026-06-06',NULL,'2026-06-06 05:06:51'),
(46,196,'LOT-0196-2025',20,'2027-05-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(47,204,'LOT-0204-2025',20,'2027-04-18','2026-06-06',NULL,'2026-06-06 05:06:51'),
(48,205,'LOT-0205-2025',20,'2027-05-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(49,211,'LOT-0211-2025',20,'2028-02-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(50,212,'LOT-0212-2025',20,'2026-12-18','2026-06-06',NULL,'2026-06-06 05:06:51'),
(51,19,'LOT-0019-2025',25,'2028-01-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(52,24,'LOT-0024-2025',25,'2028-01-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(53,37,'LOT-0037-2025',25,'2027-07-09','2026-06-06',NULL,'2026-06-06 05:06:51'),
(54,49,'LOT-0049-2025',25,'2028-02-11','2026-06-06',NULL,'2026-06-06 05:06:51'),
(55,81,'LOT-0081-2025',25,'2028-02-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(56,90,'LOT-0090-2025',25,'2027-11-28','2026-06-06',NULL,'2026-06-06 05:06:51'),
(57,103,'LOT-0103-2025',25,'2028-03-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(58,111,'LOT-0111-2025',25,'2027-07-28','2026-06-06',NULL,'2026-06-06 05:06:51'),
(59,115,'LOT-0115-2025',25,'2027-09-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(60,116,'LOT-0116-2025',25,'2027-06-01','2026-06-06',NULL,'2026-06-06 05:06:51'),
(61,124,'LOT-0124-2025',25,'2027-01-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(62,166,'LOT-0166-2025',25,'2027-07-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(63,181,'LOT-0181-2025',25,'2028-02-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(64,191,'LOT-0191-2025',25,'2028-01-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(65,194,'LOT-0194-2025',25,'2027-07-18','2026-06-06',NULL,'2026-06-06 05:06:51'),
(66,203,'LOT-0203-2025',25,'2028-02-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(67,206,'LOT-0206-2025',25,'2028-02-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(68,16,'LOT-0016-2025',30,'2027-11-14','2026-06-06',NULL,'2026-06-06 05:06:51'),
(69,21,'LOT-0021-2025',30,'2028-01-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(70,28,'LOT-0028-2025',30,'2028-02-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(71,33,'LOT-0033-2025',30,'2028-04-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(72,39,'LOT-0039-2025',30,'2027-02-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(73,48,'LOT-0048-2025',30,'2028-02-24','2026-06-06',NULL,'2026-06-06 05:06:51'),
(74,54,'LOT-0054-2025',30,'2028-02-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(75,62,'LOT-0062-2025',30,'2027-08-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(76,68,'LOT-0068-2025',30,'2028-04-18','2026-06-06',NULL,'2026-06-06 05:06:51'),
(77,78,'LOT-0078-2025',30,'2027-04-29','2026-06-06',NULL,'2026-06-06 05:06:51'),
(78,99,'LOT-0099-2025',30,'2027-10-10','2026-06-06',NULL,'2026-06-06 05:06:51'),
(79,110,'LOT-0110-2025',30,'2027-01-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(80,112,'LOT-0112-2025',30,'2027-10-04','2026-06-06',NULL,'2026-06-06 05:06:51'),
(81,114,'LOT-0114-2025',30,'2027-11-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(82,117,'LOT-0117-2025',30,'2027-10-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(83,131,'LOT-0131-2025',30,'2028-04-01','2026-06-06',NULL,'2026-06-06 05:06:51'),
(84,156,'LOT-0156-2025',30,'2028-01-26','2026-06-06',NULL,'2026-06-06 05:06:51'),
(85,164,'LOT-0164-2025',30,'2027-03-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(86,173,'LOT-0173-2025',30,'2027-11-05','2026-06-06',NULL,'2026-06-06 05:06:51'),
(87,180,'LOT-0180-2025',30,'2027-09-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(88,193,'LOT-0193-2025',30,'2028-03-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(89,199,'LOT-0199-2025',30,'2027-12-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(90,209,'LOT-0209-2025',30,'2028-04-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(91,210,'LOT-0210-2025',30,'2027-10-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(92,18,'LOT-0018-2025',35,'2026-12-04','2026-06-06',NULL,'2026-06-06 05:06:51'),
(93,40,'LOT-0040-2025',35,'2027-05-24','2026-06-06',NULL,'2026-06-06 05:06:51'),
(94,197,'LOT-0197-2025',35,'2027-10-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(95,200,'LOT-0200-2025',35,'2028-05-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(96,207,'LOT-0207-2025',35,'2027-02-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(97,71,'LOT-0071-2025',35,'2028-01-28','2026-06-06',NULL,'2026-06-06 05:06:51'),
(98,80,'LOT-0080-2025',35,'2027-08-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(99,102,'LOT-0102-2025',35,'2026-12-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(100,129,'LOT-0129-2025',35,'2027-11-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(101,172,'LOT-0172-2025',35,'2027-02-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(102,20,'LOT-0020-2025',40,'2028-01-01','2026-06-06',NULL,'2026-06-06 05:06:51'),
(103,35,'LOT-0035-2025',40,'2027-05-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(104,38,'LOT-0038-2025',40,'2027-03-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(105,47,'LOT-0047-2025',40,'2027-03-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(106,57,'LOT-0057-2025',40,'2027-06-26','2026-06-06',NULL,'2026-06-06 05:06:51'),
(107,59,'LOT-0059-2025',40,'2027-05-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(108,74,'LOT-0074-2025',40,'2027-06-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(109,77,'LOT-0077-2025',40,'2028-04-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(110,79,'LOT-0079-2025',40,'2027-10-02','2026-06-06',NULL,'2026-06-06 05:06:51'),
(111,84,'LOT-0084-2025',40,'2026-12-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(112,89,'LOT-0089-2025',40,'2027-06-14','2026-06-06',NULL,'2026-06-06 05:06:51'),
(113,92,'LOT-0092-2025',40,'2028-01-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(114,94,'LOT-0094-2025',40,'2028-01-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(115,101,'LOT-0101-2025',40,'2027-07-18','2026-06-06',NULL,'2026-06-06 05:06:51'),
(116,109,'LOT-0109-2025',40,'2028-03-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(117,122,'LOT-0122-2025',40,'2027-02-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(118,128,'LOT-0128-2025',40,'2027-01-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(119,155,'LOT-0155-2025',40,'2028-05-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(120,178,'LOT-0178-2025',40,'2027-11-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(121,179,'LOT-0179-2025',40,'2027-05-30','2026-06-06',NULL,'2026-06-06 05:06:51'),
(122,192,'LOT-0192-2025',40,'2027-12-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(123,198,'LOT-0198-2025',40,'2027-07-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(124,201,'LOT-0201-2025',40,'2027-01-28','2026-06-06',NULL,'2026-06-06 05:06:51'),
(125,208,'LOT-0208-2025',40,'2027-04-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(126,15,'LOT-0015-2025',45,'2028-02-28','2026-06-06',NULL,'2026-06-06 05:06:51'),
(127,61,'LOT-0061-2025',45,'2027-08-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(128,75,'LOT-0075-2025',45,'2028-05-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(129,100,'LOT-0100-2025',45,'2027-05-30','2026-06-06',NULL,'2026-06-06 05:06:51'),
(130,127,'LOT-0127-2025',45,'2028-01-20','2026-06-06',NULL,'2026-06-06 05:06:51'),
(131,151,'LOT-0151-2025',45,'2028-02-26','2026-06-06',NULL,'2026-06-06 05:06:51'),
(132,107,'LOT-0107-2025',45,'2028-03-18','2026-06-06',NULL,'2026-06-06 05:06:51'),
(133,121,'LOT-0121-2025',45,'2028-03-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(134,22,'LOT-0022-2025',50,'2027-12-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(135,45,'LOT-0045-2025',50,'2028-05-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(136,55,'LOT-0055-2025',50,'2027-12-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(137,60,'LOT-0060-2025',50,'2027-10-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(138,64,'LOT-0064-2025',50,'2028-01-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(139,67,'LOT-0067-2025',50,'2027-03-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(140,76,'LOT-0076-2025',50,'2027-09-14','2026-06-06',NULL,'2026-06-06 05:06:51'),
(141,85,'LOT-0085-2025',50,'2027-02-10','2026-06-06',NULL,'2026-06-06 05:06:51'),
(142,88,'LOT-0088-2025',50,'2027-01-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(143,95,'LOT-0095-2025',50,'2028-04-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(144,104,'LOT-0104-2025',50,'2027-06-05','2026-06-06',NULL,'2026-06-06 05:06:51'),
(145,126,'LOT-0126-2025',50,'2028-05-24','2026-06-06',NULL,'2026-06-06 05:06:51'),
(146,130,'LOT-0130-2025',50,'2028-05-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(147,141,'LOT-0141-2025',50,'2028-02-26','2026-06-06',NULL,'2026-06-06 05:06:51'),
(148,150,'LOT-0150-2025',50,'2027-04-30','2026-06-06',NULL,'2026-06-06 05:06:51'),
(149,171,'LOT-0171-2025',50,'2028-03-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(150,174,'LOT-0174-2025',50,'2027-09-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(151,175,'LOT-0175-2025',50,'2027-02-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(152,202,'LOT-0202-2025',50,'2028-05-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(153,93,'LOT-0093-2025',55,'2027-08-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(154,27,'LOT-0027-2025',60,'2027-07-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(155,32,'LOT-0032-2025',60,'2027-11-04','2026-06-06',NULL,'2026-06-06 05:06:51'),
(156,36,'LOT-0036-2025',60,'2028-04-09','2026-06-06',NULL,'2026-06-06 05:06:51'),
(157,44,'LOT-0044-2025',60,'2027-12-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(158,53,'LOT-0053-2025',60,'2028-01-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(159,58,'LOT-0058-2025',60,'2027-12-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(160,65,'LOT-0065-2025',60,'2027-03-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(161,72,'LOT-0072-2025',60,'2028-03-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(162,73,'LOT-0073-2025',60,'2028-01-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(163,83,'LOT-0083-2025',60,'2027-04-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(164,91,'LOT-0091-2025',60,'2026-12-04','2026-06-06',NULL,'2026-06-06 05:06:51'),
(165,96,'LOT-0096-2025',60,'2027-03-31','2026-06-06',NULL,'2026-06-06 05:06:51'),
(166,106,'LOT-0106-2025',60,'2027-01-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(167,120,'LOT-0120-2025',60,'2028-02-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(168,125,'LOT-0125-2025',60,'2027-12-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(169,137,'LOT-0137-2025',60,'2027-03-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(170,139,'LOT-0139-2025',60,'2028-02-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(171,148,'LOT-0148-2025',60,'2027-08-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(172,154,'LOT-0154-2025',60,'2028-04-29','2026-06-06',NULL,'2026-06-06 05:06:51'),
(173,176,'LOT-0176-2025',60,'2027-05-25','2026-06-06',NULL,'2026-06-06 05:06:51'),
(174,146,'LOT-0146-2025',65,'2028-01-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(175,34,'LOT-0034-2025',70,'2028-02-09','2026-06-06',NULL,'2026-06-06 05:06:51'),
(176,46,'LOT-0046-2025',70,'2028-01-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(177,66,'LOT-0066-2025',70,'2027-06-14','2026-06-06',NULL,'2026-06-06 05:06:51'),
(178,69,'LOT-0069-2025',70,'2027-09-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(179,97,'LOT-0097-2025',70,'2027-10-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(180,108,'LOT-0108-2025',70,'2027-06-04','2026-06-06',NULL,'2026-06-06 05:06:51'),
(181,123,'LOT-0123-2025',70,'2028-04-19','2026-06-06',NULL,'2026-06-06 05:06:51'),
(182,135,'LOT-0135-2025',70,'2027-11-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(183,145,'LOT-0145-2025',70,'2027-07-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(184,149,'LOT-0149-2025',70,'2027-04-14','2026-06-06',NULL,'2026-06-06 05:06:51'),
(185,160,'LOT-0160-2025',70,'2028-04-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(186,31,'LOT-0031-2025',80,'2028-03-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(187,43,'LOT-0043-2025',76,'2027-09-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(188,51,'LOT-0051-2025',80,'2026-12-15','2026-06-06',NULL,'2026-06-06 05:06:51'),
(189,56,'LOT-0056-2025',80,'2027-10-03','2026-06-06',NULL,'2026-06-06 05:06:51'),
(190,63,'LOT-0063-2025',80,'2028-01-11','2026-06-06',NULL,'2026-06-06 05:06:51'),
(191,86,'LOT-0086-2025',80,'2027-01-02','2026-06-06',NULL,'2026-06-06 05:06:51'),
(192,98,'LOT-0098-2025',80,'2026-12-23','2026-06-06',NULL,'2026-06-06 05:06:51'),
(193,132,'LOT-0132-2025',80,'2026-12-10','2026-06-06',NULL,'2026-06-06 05:06:51'),
(194,134,'LOT-0134-2025',80,'2028-05-02','2026-06-06',NULL,'2026-06-06 05:06:51'),
(195,140,'LOT-0140-2025',80,'2028-01-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(196,147,'LOT-0147-2025',80,'2028-02-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(197,153,'LOT-0153-2025',80,'2028-05-01','2026-06-06',NULL,'2026-06-06 05:06:51'),
(198,177,'LOT-0177-2025',80,'2027-04-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(199,87,'LOT-0087-2025',90,'2027-08-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(200,105,'LOT-0105-2025',90,'2027-10-30','2026-06-06',NULL,'2026-06-06 05:06:51'),
(201,136,'LOT-0136-2025',90,'2027-10-30','2026-06-06',NULL,'2026-06-06 05:06:51'),
(202,138,'LOT-0138-2025',90,'2027-04-06','2026-06-06',NULL,'2026-06-06 05:06:51'),
(203,159,'LOT-0159-2025',90,'2027-05-17','2026-06-06',NULL,'2026-06-06 05:06:51'),
(204,30,'LOT-0030-2025',98,'2028-03-01','2026-06-06',NULL,'2026-06-06 05:06:51'),
(205,142,'LOT-0142-2025',100,'2027-05-10','2026-06-06',NULL,'2026-06-06 05:06:51'),
(206,143,'LOT-0143-2025',100,'2028-04-21','2026-06-06',NULL,'2026-06-06 05:06:51'),
(207,144,'LOT-0144-2025',100,'2028-02-11','2026-06-06',NULL,'2026-06-06 05:06:51'),
(208,157,'LOT-0157-2025',100,'2027-03-31','2026-06-06',NULL,'2026-06-06 05:06:51'),
(209,162,'LOT-0162-2025',100,'2027-12-05','2026-06-06',NULL,'2026-06-06 05:06:51'),
(210,170,'LOT-0170-2025',100,'2028-01-07','2026-06-06',NULL,'2026-06-06 05:06:51'),
(211,52,'LOT-0052-2025',120,'2027-11-27','2026-06-06',NULL,'2026-06-06 05:06:51'),
(212,50,'LOT-0050-2025',150,'2027-01-29','2026-06-06',NULL,'2026-06-06 05:06:51'),
(213,133,'LOT-0133-2025',150,'2027-09-16','2026-06-06',NULL,'2026-06-06 05:06:51'),
(214,158,'LOT-0158-2025',150,'2027-06-08','2026-06-06',NULL,'2026-06-06 05:06:51'),
(215,163,'LOT-0163-2025',150,'2027-02-14','2026-06-06',NULL,'2026-06-06 05:06:51'),
(216,213,'LOT-0213-2025',150,'2027-11-12','2026-06-06',NULL,'2026-06-06 05:06:51'),
(217,161,'LOT-0161-2025',200,'2028-01-30','2026-06-06',NULL,'2026-06-06 05:06:51'),
(218,152,'LOT-0152-2025',200,'2026-12-05','2026-06-06',NULL,'2026-06-06 05:06:51'),
(219,214,'LOT-0214-2025',200,'2027-12-02','2026-06-06',NULL,'2026-06-06 05:06:51'),
(220,70,'LOT-0070-2025',300,'2027-06-13','2026-06-06',NULL,'2026-06-06 05:06:51'),
(221,9,'LOTE_2026-187600',2,'2026-07-10','2026-06-06','Compra #4','2026-06-06 12:09:59');
/*!40000 ALTER TABLE `lotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimentos_caixa`
--

DROP TABLE IF EXISTS `movimentos_caixa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentos_caixa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `caixa_id` int(10) unsigned NOT NULL,
  `venda_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('venda','entrada','saida','sangria','suprimento','devolucao') NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mov_caixa_caixa` (`caixa_id`),
  KEY `idx_mov_caixa_venda` (`venda_id`),
  KEY `idx_mov_caixa_tipo` (`tipo`),
  KEY `idx_mov_caixa_data` (`criado_em`),
  KEY `fk_mov_caixa_usuario` (`usuario_id`),
  CONSTRAINT `fk_mov_caixa_caixa` FOREIGN KEY (`caixa_id`) REFERENCES `caixa` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_caixa_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_caixa_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimentos financeiros por sessão de caixa';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimentos_caixa`
--

LOCK TABLES `movimentos_caixa` WRITE;
/*!40000 ALTER TABLE `movimentos_caixa` DISABLE KEYS */;
INSERT INTO `movimentos_caixa` VALUES
(1,1,NULL,'suprimento',3658.50,'Fundo inicial de caixa',2,'2026-05-24 04:22:06'),
(2,1,18,'venda',20.00,'Venda VD-2026-00016',2,'2026-05-24 04:23:57'),
(3,1,19,'venda',30.00,'Venda VD-2026-00017',2,'2026-05-24 04:24:38'),
(4,1,NULL,'saida',120.00,'Pagamento de taxi',2,'2026-05-24 04:26:18'),
(5,2,NULL,'suprimento',150.00,'Fundo inicial de caixa',2,'2026-05-24 04:30:02'),
(6,3,20,'venda',2422.00,'Venda VD-2026-00018',2,'2026-05-24 04:40:11'),
(8,4,22,'venda',414.00,'Venda VD-2026-00019',2,'2026-05-24 05:05:34'),
(9,4,NULL,'saida',1000.00,'Deposito Bancario',2,'2026-05-24 05:06:39'),
(10,5,23,'venda',20.00,'Venda VD-2026-00020',1,'2026-05-24 08:46:20'),
(11,6,NULL,'entrada',124.00,'Pagamento da divida da Tecnica Almira',1,'2026-05-24 14:42:08'),
(12,6,24,'venda',260.00,'Venda VD-2026-00021',3,'2026-05-24 14:49:05'),
(13,7,25,'venda',260.00,'Venda VD-2026-00022',1,'2026-06-01 07:24:20'),
(14,8,26,'venda',80.00,'Venda VD-2026-00023',1,'2026-06-02 04:30:19'),
(15,8,NULL,'saida',50.00,'Pagamento de taxi',1,'2026-06-02 04:32:57'),
(16,9,NULL,'saida',100.00,'Banco',1,'2026-06-04 17:54:56'),
(17,10,27,'venda',237.00,'Venda VD-2026-00024',1,'2026-06-05 19:04:54'),
(18,10,NULL,'saida',50.00,'Pagamento de taxi',1,'2026-06-05 19:05:27'),
(19,10,28,'venda',370.00,'Venda VD-2026-00025',1,'2026-06-06 11:44:47'),
(20,10,29,'venda',2700.00,'Venda VD-2026-00026',4,'2026-06-06 11:46:20');
/*!40000 ALTER TABLE `movimentos_caixa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimentos_stock`
--

DROP TABLE IF EXISTS `movimentos_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentos_stock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int(10) unsigned NOT NULL,
  `lote_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('entrada','saida','ajuste_positivo','ajuste_negativo','devolucao_cliente','devolucao_fornecedor','perda','vencimento') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `referencia` varchar(60) DEFAULT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mov_produto` (`produto_id`),
  KEY `idx_mov_tipo` (`tipo`),
  KEY `idx_mov_criado` (`criado_em`),
  KEY `fk_ms_lote` (`lote_id`),
  CONSTRAINT `fk_mov_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ms_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimentos_stock`
--

LOCK TABLES `movimentos_stock` WRITE;
/*!40000 ALTER TABLE `movimentos_stock` DISABLE KEYS */;
INSERT INTO `movimentos_stock` VALUES
(1,4,NULL,'entrada',500,'CP-2026-00001',1,'Recepção de compra #1','2026-05-23 20:38:48'),
(2,9,8,'saida',10,'VD-2026-00012',1,'Venda VD-2026-00012','2026-05-23 21:37:10'),
(3,3,4,'saida',8,'VD-2026-00012',1,'Venda VD-2026-00012','2026-05-23 21:37:10'),
(4,9,8,'saida',13,'VD-2026-00013',1,'Venda VD-2026-00013','2026-05-23 21:39:31'),
(5,7,14,'saida',9,'VD-2026-00014',2,'Venda VD-2026-00014','2026-05-24 03:44:10'),
(6,7,15,'saida',2,'VD-2026-00014',2,'Venda VD-2026-00014','2026-05-24 03:44:10'),
(7,7,15,'saida',3,'VD-2026-00015',2,'Venda VD-2026-00015','2026-05-24 03:45:54'),
(8,2,NULL,'entrada',10,'CP-2026-00002',2,'Recepção de compra #2','2026-05-24 03:51:17'),
(9,8,NULL,'entrada',17,'CP-2026-00002',2,'Recepção de compra #2','2026-05-24 03:51:17'),
(10,7,15,'saida',2,'VD-2026-00016',2,'Venda VD-2026-00016','2026-05-24 04:23:57'),
(11,2,3,'saida',3,'VD-2026-00017',2,'Venda VD-2026-00017','2026-05-24 04:24:38'),
(12,3,4,'saida',35,'VD-2026-00018',2,'Venda VD-2026-00018','2026-05-24 04:40:11'),
(13,1,1,'saida',7,'VD-2026-00018',2,'Venda VD-2026-00018','2026-05-24 04:40:11'),
(15,1,1,'saida',9,'VD-2026-00019',2,'Venda VD-2026-00019','2026-05-24 05:05:34'),
(16,2,3,'saida',2,'VD-2026-00020',1,'Venda VD-2026-00020','2026-05-24 08:46:20'),
(17,10,18,'saida',2,'VD-2026-00021',3,'Venda VD-2026-00021','2026-05-24 14:49:05'),
(18,4,5,'saida',1,'VD-2026-00021',3,'Venda VD-2026-00021','2026-05-24 14:49:05'),
(19,10,18,'saida',3,'VD-2026-00022',1,'Venda VD-2026-00022','2026-06-01 07:24:20'),
(20,2,3,'saida',5,'VD-2026-00022',1,'Venda VD-2026-00022','2026-06-01 07:24:20'),
(21,2,3,'saida',2,'VD-2026-00023',1,'Venda VD-2026-00023','2026-06-02 04:30:19'),
(22,5,6,'saida',3,'VD-2026-00023',1,'Venda VD-2026-00023','2026-06-02 04:30:19'),
(23,9,NULL,'entrada',10,'CP-2026-00004',1,'Recepção de compra #4','2026-06-02 04:40:50'),
(24,6,NULL,'entrada',20,'CP-2026-00004',1,'Recepção de compra #4','2026-06-02 04:40:50'),
(25,9,8,'saida',2,'VD-2026-00024',1,'Venda VD-2026-00024','2026-06-05 19:04:54'),
(26,9,19,'saida',1,'VD-2026-00024',1,'Venda VD-2026-00024','2026-06-05 19:04:54'),
(27,43,187,'saida',2,'VD-2026-00025',1,'Venda VD-2026-00025','2026-06-06 11:44:47'),
(28,30,204,'saida',1,'VD-2026-00025',1,'Venda VD-2026-00025','2026-06-06 11:44:47'),
(29,29,31,'saida',6,'VD-2026-00026',4,'Venda VD-2026-00026','2026-06-06 11:46:20'),
(30,9,NULL,'entrada',2,'CP-2026-00004',4,'Compra #4','2026-06-06 12:09:59');
/*!40000 ALTER TABLE `movimentos_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produtos`
--

DROP TABLE IF EXISTS `produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `principio_ativo` varchar(200) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_id` int(10) unsigned NOT NULL,
  `fornecedor_id` int(10) unsigned DEFAULT NULL,
  `unidade_medida` varchar(30) NOT NULL DEFAULT 'unidade',
  `unidade_compra` varchar(50) NOT NULL DEFAULT 'caixa',
  `unidade_venda` varchar(50) NOT NULL DEFAULT 'unidade',
  `fator_conversao` decimal(10,3) NOT NULL DEFAULT 1.000,
  `preco_compra_unitario` decimal(10,2) GENERATED ALWAYS AS (round(`preco_compra` / `fator_conversao`,2)) VIRTUAL,
  `preco_compra` decimal(12,2) NOT NULL DEFAULT 0.00,
  `preco_venda` decimal(12,2) NOT NULL DEFAULT 0.00,
  `margem_lucro` decimal(5,2) GENERATED ALWAYS AS (case when `preco_compra` > 0 then (`preco_venda` - `preco_compra`) / `preco_compra` * 100 else 0 end) VIRTUAL,
  `estoque_actual` int(11) NOT NULL DEFAULT 0,
  `estoque_min` int(11) NOT NULL DEFAULT 5,
  `requer_receita` tinyint(1) NOT NULL DEFAULT 0,
  `controlado` tinyint(1) NOT NULL DEFAULT 0,
  `imagem_url` varchar(500) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_produtos_codigo_barras` (`codigo_barras`),
  KEY `idx_produtos_categoria` (`categoria_id`),
  KEY `idx_produtos_fornecedor` (`fornecedor_id`),
  KEY `idx_produtos_ativo` (`ativo`),
  KEY `idx_produtos_requer_receita` (`requer_receita`),
  KEY `idx_produtos_estoque` (`estoque_actual`,`estoque_min`),
  KEY `idx_fator_conversao` (`fator_conversao`),
  CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_produtos_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de produtos farmacêuticos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produtos`
--

LOCK TABLES `produtos` WRITE;
/*!40000 ALTER TABLE `produtos` DISABLE KEYS */;
INSERT INTO `produtos` VALUES
(1,'Amoxicilina 500mg','EAN-13','Amoxicilina',NULL,7,NULL,'caixa','caixa','unidade',1.000,38.00,38.00,46.00,21.05,352,20,0,0,'produtos/amoxicilina-500mg-6a1389e3a867e.jpg',1,'2026-05-22 05:21:49','2026-05-24 23:29:39'),
(2,'Paracetamol 500mg','1452FD','Paracetamol',NULL,7,NULL,'unidade','caixa','unidade',1.000,8.00,8.00,10.00,25.00,44,5,0,0,NULL,1,'2026-05-22 05:39:46','2026-06-04 17:24:42'),
(3,'Sertralina 50mg Comprimido',NULL,'Sertralina',NULL,39,NULL,'comprimido','caixa','unidade',1.000,28.00,28.00,60.00,114.29,314,5,0,0,NULL,1,'2026-05-22 09:33:08','2026-05-24 04:40:11'),
(4,'Paracetamol Infantil 120mg/5ml Xarope',NULL,'Paracetamol',NULL,13,NULL,'frasco','caixa','unidade',1.000,88.00,88.00,120.00,36.36,1030,10,0,0,NULL,1,'2026-05-22 09:35:56','2026-05-24 14:49:05'),
(5,'Ibuprofeno 400mg Comprimido',NULL,'Ibuprofeno',NULL,14,NULL,'comprimido','caixa','unidade',1.000,15.00,15.00,20.00,33.33,69,40,0,0,NULL,1,'2026-05-22 09:38:39','2026-06-04 17:24:42'),
(6,'Ibuprofeno Pediatrico 100 mg/5 ml Suspensao',NULL,'Ibuprofeno',NULL,14,NULL,'frasco','caixa','unidade',1.000,75.00,75.00,90.00,20.00,40,15,0,0,NULL,1,'2026-05-22 09:40:50','2026-06-02 04:40:50'),
(7,'Diclofenaco 50 mg Comprimido',NULL,'Diclofenaco Sodico',NULL,14,NULL,'comprimido','caixa','unidade',1.000,8.00,8.00,10.00,25.00,0,20,0,0,NULL,1,'2026-05-22 09:48:49','2026-05-24 04:23:57'),
(8,'Diclofenaco Gel 1% Gel',NULL,'Diclofenaco Dietilamonio',NULL,14,NULL,'frasco','caixa','unidade',1.000,98.00,98.00,120.00,22.45,130,25,0,0,NULL,1,'2026-05-22 09:51:17','2026-05-24 03:51:17'),
(9,'Aspirina 100 mg Comprimido',NULL,'Acido Acetilsalicilico',NULL,16,NULL,'comprimido','caixa','unidade',1.000,58.00,58.00,79.00,36.21,18,10,1,0,NULL,1,'2026-05-22 10:00:48','2026-06-06 12:09:59'),
(10,'Aspirina Forte 500 mg Comprimido','2989462','Acido Acetilsalicilico',NULL,13,NULL,'comprimido','caixa','unidade',1.000,58.00,58.00,70.00,20.69,29,10,1,0,NULL,1,'2026-05-22 10:03:34','2026-06-01 07:24:20'),
(11,'Albendazol 400 mg Comprimido',NULL,'Albendazol',NULL,24,NULL,'comprimido','caixa','unidade',1.000,26.00,26.00,35.00,34.62,0,5,0,0,'produtos/albendazol-400-mg-comprimido-6a138ab4a78d5.webp',1,'2026-05-22 10:06:48','2026-05-24 23:33:08'),
(12,'Salbutamos 100 mg Xarope','EAN-12','Salbutamol',NULL,37,NULL,'frasco','caixa','unidade',1.000,50.00,50.00,68.00,36.00,0,10,0,0,NULL,1,'2026-05-23 13:52:36','2026-05-23 13:52:36'),
(13,'Azitromicina 500 mg Cápsula','EAN-14','Azitromicina',NULL,14,NULL,'comprimido','caixa','unidade',1.000,26.00,26.00,30.00,15.38,20,15,0,0,NULL,1,'2026-05-23 13:55:19','2026-05-23 13:55:19'),
(14,'abacavir and lamivudine  (abacavir sulfate / lamivudine, ABC / 3TC)',NULL,NULL,NULL,28,NULL,'Frasco','caixa','unidade',1.000,78.99,78.99,159.99,102.54,0,100,0,0,NULL,1,'2026-05-25 00:24:21','2026-05-25 00:24:21'),
(15,'Amoxicilina 500mg Cápsulas 21un','5900000000001','Amoxicilina','Antibiótico de largo espectro. 21 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,180.00,180.00,280.00,55.56,45,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(16,'Amoxicilina 250mg/5ml Suspensão 100ml','5900000000002','Amoxicilina','Suspensão oral pediátrica. Frasco 100ml.',7,NULL,'frasco','caixa','unidade',1.000,120.00,120.00,190.00,58.33,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(17,'Amoxicilina+Clavulanato 875mg 14un','5900000000003','Amoxicilina+Ácido Clavulânico','Antibiótico de largo espectro com inibidor. 14 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,380.00,380.00,580.00,52.63,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(18,'Azitromicina 500mg Comprimidos 3un','5900000000004','Azitromicina','Macrólido de curta duração. 3 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,320.00,60.00,35,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(19,'Azitromicina 200mg/5ml Suspensão','5900000000005','Azitromicina','Suspensão pediátrica 30ml.',7,NULL,'frasco','caixa','unidade',1.000,160.00,160.00,260.00,62.50,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(20,'Ciprofloxacina 500mg Comprimidos 10un','5900000000006','Ciprofloxacina','Fluoroquinolona de largo espectro. 10 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,160.00,160.00,260.00,62.50,40,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(21,'Doxiciclina 100mg Cápsulas 10un','5900000000007','Doxiciclina','Tetraciclina de largo espectro. 10 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(22,'Metronidazol 400mg Comprimidos 21un','5900000000008','Metronidazol','Antibiótico e antiprotozoário. 21 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,140.00,75.00,50,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(23,'Metronidazol 250mg/5ml Suspensão','5900000000009','Metronidazol','Suspensão oral pediátrica 100ml.',7,NULL,'frasco','caixa','unidade',1.000,90.00,90.00,150.00,66.67,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(24,'Eritromicina 500mg Comprimidos 20un','5900000000010','Eritromicina','Macrólido para alérgicos à penicilina. 20 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,140.00,140.00,230.00,64.29,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(25,'Cefalexina 500mg Cápsulas 20un','5900000000011','Cefalexina','Cefalosporina de 1ª geração. 20 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,320.00,60.00,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(26,'Clindamicina 300mg Cápsulas 16un','5900000000012','Clindamicina','Lincosamida para infecções graves. 16 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,320.00,320.00,500.00,56.25,15,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(27,'Sulfametoxazol+Trimetoprim 800/160mg','5900000000013','Sulfametoxazol+Trimetoprim','Antibacteriano de largo espectro. 14 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,60,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(28,'Ampicilina 500mg Cápsulas 20un','5900000000014','Ampicilina','Penicilina de largo espectro. 20 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,170.00,70.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(29,'Ceftriaxona 1g Pó Injectável','5900000000015','Ceftriaxona','Cefalosporina 3ª geração para uso hospitalar.',7,NULL,'unidade','caixa','unidade',1.000,280.00,280.00,450.00,60.71,8,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 11:46:20'),
(30,'Paracetamol 500mg Comprimidos 20un','5900000000016','Paracetamol','Analgésico e antipirético. 20 comprimidos.',8,2,'caixa','caixa','cartela',10.000,5.30,53.00,10.00,-81.13,98,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 11:44:47'),
(31,'Paracetamol 1000mg Comprimidos 20un','5900000000017','Paracetamol','Analgésico forte. 20 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,80,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(32,'Paracetamol 120mg/5ml Xarope 100ml','5900000000018','Paracetamol','Xarope pediátrico. Frasco 100ml.',8,NULL,'frasco','caixa','unidade',1.000,60.00,60.00,110.00,83.33,60,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(33,'Paracetamol 250mg Supositórios 10un','5900000000019','Paracetamol','Supositórios pediátricos. 10 unidades.',8,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,140.00,75.00,30,8,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(34,'Ibuprofeno 400mg Comprimidos 20un','5900000000020','Ibuprofeno','Anti-inflamatório e analgésico. 20 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(35,'Ibuprofeno 200mg/5ml Suspensão 100ml','5900000000021','Ibuprofeno','Suspensão pediátrica. Frasco 100ml.',8,NULL,'frasco','caixa','unidade',1.000,90.00,90.00,160.00,77.78,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(36,'Diclofenac 50mg Comprimidos 20un','5900000000022','Diclofenac Sódico','Anti-inflamatório para dor e inflamação. 20 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,70.00,70.00,130.00,85.71,60,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(37,'Diclofenac 75mg/3ml Injectável 3un','5900000000023','Diclofenac Sódico','Ampolas injectáveis para dor intensa. 3 ampolas.',8,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(38,'Naproxeno 500mg Comprimidos 20un','5900000000024','Naproxeno','Anti-inflamatório de longa duração. 20 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,90.00,90.00,160.00,77.78,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(39,'Piroxicam 20mg Cápsulas 20un','5900000000025','Piroxicam','Anti-inflamatório de longa duração. 20 cápsulas.',8,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,30,8,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(40,'Meloxicam 15mg Comprimidos 10un','5900000000026','Meloxicam','Anti-inflamatório selectivo COX-2. 10 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,35,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(41,'Tramadol 50mg Cápsulas 20un','5900000000027','Tramadol','Analgésico opioide para dor moderada a intensa.',17,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,350.00,75.00,20,5,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(42,'Codeína+Paracetamol 30/500mg 20un','5900000000028','Codeína+Paracetamol','Analgésico opioide combinado. 20 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,180.00,180.00,300.00,66.67,15,5,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(43,'Artemeter+Lumefantrina 20/120mg 24un','5900000000029','Artemeter+Lumefantrina','Antimalárico de 1ª linha adulto. 24 comprimidos.',11,NULL,'caixa','caixa','cartela',20.000,90.00,1800.00,180.00,-90.00,76,20,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 11:44:47'),
(44,'Artemeter+Lumefantrina Pediátrico 12un','5900000000030','Artemeter+Lumefantrina','Antimalárico pediátrico. 12 comprimidos dispersíveis.',11,NULL,'caixa','caixa','unidade',1.000,150.00,150.00,250.00,66.67,60,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(45,'Artesunato+Amodiaquina 100/270mg','5900000000031','Artesunato+Amodiaquina','Antimalárico combinado. 3 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,50,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(46,'Sulfadoxina+Pirimetamina 500/25mg','5900000000032','Sulfadoxina+Pirimetamina','Antimalárico preventivo (TPI). 3 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,70,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(47,'Cloroquina 250mg Comprimidos 25un','5900000000033','Cloroquina','Antimalárico e anti-inflamatório. 25 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,140.00,75.00,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(48,'Primaquina 15mg Comprimidos 14un','5900000000034','Primaquina','Antimalárico para prevenção de recaídas. 14 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,170.00,70.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(49,'Quinina 300mg Comprimidos 30un','5900000000035','Quinina','Antimalárico de 2ª linha. 30 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,140.00,140.00,230.00,64.29,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(50,'Albendazol 400mg Comprimidos 1un','5900000000036','Albendazol','Antiparasitário de largo espectro. 1 comprimido.',10,NULL,'unidade','caixa','unidade',1.000,25.00,25.00,50.00,100.00,150,30,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(51,'Albendazol 200mg/5ml Suspensão 10ml','5900000000037','Albendazol','Suspensão pediátrica. Frasco 10ml.',10,NULL,'frasco','caixa','unidade',1.000,60.00,60.00,110.00,83.33,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(52,'Mebendazol 100mg Comprimidos 6un','5900000000038','Mebendazol','Antiparasitário intestinal. 6 comprimidos.',10,NULL,'caixa','caixa','unidade',1.000,30.00,30.00,60.00,100.00,120,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(53,'Ivermectina 6mg Comprimidos 4un','5900000000039','Ivermectina','Antiparasitário para sarna e helmintas. 4 comprimidos.',10,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,60,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(54,'Praziquantel 600mg Comprimidos 6un','5900000000040','Praziquantel','Antiparasitário para esquistossomose. 6 comprimidos.',10,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(55,'Tinidazol 500mg Comprimidos 4un','5900000000041','Tinidazol','Antiprotozoário para giardíase e amebíase. 4 comprimidos.',10,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,50,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(56,'Fluconazol 150mg Cápsula 1un','5900000000042','Fluconazol','Antifúngico sistémico dose única. 1 cápsula.',15,NULL,'unidade','caixa','unidade',1.000,60.00,60.00,110.00,83.33,80,20,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(57,'Fluconazol 50mg Cápsulas 7un','5900000000043','Fluconazol','Antifúngico sistémico. 7 cápsulas.',15,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(58,'Clotrimazol 1% Creme 20g','5900000000044','Clotrimazol','Antifúngico tópico para dermatomicoses. Bisnaga 20g.',15,NULL,'unidade','caixa','unidade',1.000,80.00,80.00,150.00,87.50,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(59,'Clotrimazol 100mg Óvulos Vaginais 6un','5900000000045','Clotrimazol','Antifúngico vaginal. 6 óvulos.',15,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(60,'Miconazol 2% Creme 30g','5900000000046','Miconazol','Antifúngico tópico. Bisnaga 30g.',15,NULL,'unidade','caixa','unidade',1.000,90.00,90.00,160.00,77.78,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(61,'Nistatina 100000UI/g Creme 30g','5900000000047','Nistatina','Antifúngico para candidíase cutânea. Bisnaga 30g.',15,NULL,'unidade','caixa','unidade',1.000,80.00,80.00,140.00,75.00,45,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(62,'Nistatina Suspensão Oral 100ml','5900000000048','Nistatina','Antifúngico oral para candidíase. Frasco 100ml.',15,NULL,'frasco','caixa','unidade',1.000,100.00,100.00,170.00,70.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(63,'Omeprazol 20mg Cápsulas 14un','5900000000049','Omeprazol','Inibidor da bomba de protões. 14 cápsulas.',14,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(64,'Omeprazol 40mg Cápsulas 14un','5900000000050','Omeprazol','Inibidor da bomba de protões dose alta. 14 cápsulas.',14,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,50,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(65,'Ranitidina 150mg Comprimidos 20un','5900000000051','Ranitidina','Antiulceroso antagonista H2. 20 comprimidos.',14,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(66,'Metoclopramida 10mg Comprimidos 20un','5900000000052','Metoclopramida','Antiemético e procinético. 20 comprimidos.',21,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(67,'Domperidona 10mg Comprimidos 30un','5900000000053','Domperidona','Antiemético e procinético. 30 comprimidos.',21,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,140.00,75.00,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(68,'Ondansetrom 4mg Comprimidos 10un','5900000000054','Ondansetrom','Antiemético potente para náuseas intensas. 10 comprimidos.',21,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,350.00,75.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(69,'Loperamida 2mg Cápsulas 12un','5900000000055','Loperamida','Antidiarreico. 12 cápsulas.',14,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(70,'Sais de Reidratação Oral Sachê','5900000000056','SRO','Reidratação oral. Sachê para 1 litro.',14,NULL,'unidade','caixa','unidade',1.000,10.00,10.00,20.00,100.00,300,50,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(71,'Lactulose Xarope 200ml','5900000000057','Lactulose','Laxante osmótico. Frasco 200ml.',14,NULL,'frasco','caixa','unidade',1.000,120.00,120.00,210.00,75.00,35,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(72,'Bisacodil 5mg Comprimidos 20un','5900000000058','Bisacodil','Laxante estimulante. 20 comprimidos.',14,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(73,'Hidróxido de Alumínio+Magnésio 200ml','5900000000059','Alumínio+Magnésio','Antiácido. Suspensão 200ml.',14,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(74,'Lansoprazol 30mg Cápsulas 14un','5900000000060','Lansoprazol','Inibidor da bomba de protões. 14 cápsulas.',14,NULL,'caixa','caixa','unidade',1.000,140.00,140.00,240.00,71.43,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(75,'Pantoprazol 40mg Comprimidos 14un','5900000000061','Pantoprazol','Inibidor da bomba de protões. 14 comprimidos.',14,NULL,'caixa','caixa','unidade',1.000,130.00,130.00,220.00,69.23,45,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(76,'Carvão Activado 250mg Cápsulas 20un','5900000000062','Carvão Activado','Adsorvente intestinal. 20 cápsulas.',14,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(77,'Diosmectita Sachê 3g 30un','5900000000063','Diosmectita','Adsorvente para diarreia. 30 sachês.',14,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(78,'Mebeverina 135mg Comprimidos 20un','5900000000064','Mebeverina','Antiespasmódico intestinal. 20 comprimidos.',14,NULL,'caixa','caixa','unidade',1.000,140.00,140.00,240.00,71.43,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(79,'Salbutamol 100mcg Inalador 200 doses','5900000000065','Salbutamol','Broncodilatador de curta acção. Inalador 200 doses.',13,NULL,'unidade','caixa','unidade',1.000,180.00,180.00,300.00,66.67,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(80,'Salbutamol 2mg/5ml Xarope 100ml','5900000000066','Salbutamol','Broncodilatador oral pediátrico. Frasco 100ml.',13,NULL,'frasco','caixa','unidade',1.000,90.00,90.00,160.00,77.78,35,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(81,'Beclometasona 250mcg Inalador','5900000000067','Beclometasona','Corticoide inalado para asma. Inalador 200 doses.',13,NULL,'unidade','caixa','unidade',1.000,280.00,280.00,450.00,60.71,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(82,'Brometo de Ipratrópio Inalador','5900000000068','Ipratrópio','Broncodilatador anticolinérgico. 200 doses.',13,NULL,'unidade','caixa','unidade',1.000,250.00,250.00,400.00,60.00,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(83,'Ambroxol 30mg/5ml Xarope 120ml','5900000000069','Ambroxol','Mucolítico expectorante. Frasco 120ml.',13,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(84,'Carbocisteína 250mg/5ml Xarope 100ml','5900000000070','Carbocisteína','Mucolítico. Frasco 100ml.',13,NULL,'frasco','caixa','unidade',1.000,90.00,90.00,160.00,77.78,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(85,'Dextrometorfano+Guaifenesina Xarope','5900000000071','Dextrometorfano','Antitússico expectorante. Frasco 100ml.',12,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(86,'Cetirizina 10mg Comprimidos 10un','5900000000072','Cetirizina','Anti-histamínico de 2ª geração. 10 comprimidos.',20,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(87,'Loratadina 10mg Comprimidos 10un','5900000000073','Loratadina','Anti-histamínico sem sedação. 10 comprimidos.',20,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,90,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(88,'Loratadina 1mg/ml Xarope 100ml','5900000000074','Loratadina','Anti-histamínico pediátrico. Frasco 100ml.',20,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(89,'Prednisolona 5mg Comprimidos 20un','5900000000075','Prednisolona','Corticoide oral. 20 comprimidos.',13,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(90,'Dexametasona 4mg/ml Injectável 5un','5900000000076','Dexametasona','Corticoide injectável. 5 ampolas.',13,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(91,'Amlodipina 5mg Comprimidos 30un','5900000000077','Amlodipina','Bloqueador canais cálcio anti-hipertensor. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,60,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(92,'Amlodipina 10mg Comprimidos 30un','5900000000078','Amlodipina','Bloqueador canais cálcio dose alta. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,130.00,130.00,220.00,69.23,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(93,'Enalapril 10mg Comprimidos 30un','5900000000079','Enalapril','IECA anti-hipertensor. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,55,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(94,'Enalapril 20mg Comprimidos 30un','5900000000080','Enalapril','IECA anti-hipertensor dose alta. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,110.00,110.00,190.00,72.73,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(95,'Losartan 50mg Comprimidos 30un','5900000000081','Losartan','Antagonista ARA II anti-hipertensor. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,50,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(96,'Atenolol 50mg Comprimidos 30un','5900000000082','Atenolol','Beta-bloqueador anti-hipertensor. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,70.00,70.00,130.00,85.71,60,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(97,'Hidroclorotiazida 25mg Comprimidos 30un','5900000000083','Hidroclorotiazida','Diurético tiazídico. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,70,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(98,'Furosemida 40mg Comprimidos 20un','5900000000084','Furosemida','Diurético de ansa. 20 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,40.00,40.00,80.00,100.00,80,20,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(99,'Furosemida 20mg/2ml Injectável 5un','5900000000085','Furosemida','Diurético injectável. 5 ampolas.',16,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(100,'Nifedipina 10mg Cápsulas 30un','5900000000086','Nifedipina','Bloqueador canais cálcio. 30 cápsulas.',16,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,45,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(101,'Metoprolol 50mg Comprimidos 30un','5900000000087','Metoprolol','Beta-bloqueador cardioselectivo. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(102,'Espironolactona 25mg Comprimidos 30un','5900000000088','Espironolactona','Diurético poupador de potássio. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,90.00,90.00,160.00,77.78,35,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(103,'Digoxina 0.25mg Comprimidos 30un','5900000000089','Digoxina','Glicosídeo cardíaco para insuficiência. 30 comprimidos.',12,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(104,'Sinvastatina 20mg Comprimidos 30un','5900000000090','Sinvastatina','Estatina para redução do colesterol. 30 comprimidos.',12,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,50,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(105,'Aspirina 100mg Comprimidos 30un','5900000000091','Ácido Acetilsalicílico','Antiagregante plaquetário. 30 comprimidos.',12,NULL,'caixa','caixa','unidade',1.000,40.00,40.00,80.00,100.00,90,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(106,'Metformina 500mg Comprimidos 60un','5900000000092','Metformina','Antidiabético oral biguanida. 60 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,60,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(107,'Metformina 850mg Comprimidos 60un','5900000000093','Metformina','Antidiabético oral dose alta. 60 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,45,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(108,'Glibenclamida 5mg Comprimidos 30un','5900000000094','Glibenclamida','Sulfonilureia para DM2. 30 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,70,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(109,'Glicazida 80mg Comprimidos 30un','5900000000095','Glicazida','Sulfonilureia de 2ª geração. 30 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(110,'Insulina NPH 100UI/ml 10ml','5900000000096','Insulina NPH Humana','Insulina de acção intermédia. Frasco 10ml.',17,NULL,'frasco','caixa','unidade',1.000,400.00,400.00,650.00,62.50,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(111,'Insulina Regular 100UI/ml 10ml','5900000000097','Insulina Regular Humana','Insulina de acção rápida. Frasco 10ml.',17,NULL,'frasco','caixa','unidade',1.000,400.00,400.00,650.00,62.50,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(112,'Diazepam 5mg Comprimidos 20un','5900000000098','Diazepam','Benzodiazepina ansiolítica e miorrelaxante.',18,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,120.00,100.00,30,8,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(113,'Diazepam 10mg/2ml Injectável 5un','5900000000099','Diazepam','Benzodiazepina injectável. 5 ampolas.',18,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,20,5,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(114,'Amitriptilina 25mg Comprimidos 20un','5900000000100','Amitriptilina','Antidepressivo tricíclico. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(115,'Carbamazepina 200mg Comprimidos 20un','5900000000101','Carbamazepina','Antiepiléptico e estabilizador do humor. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(116,'Fenitoína 100mg Comprimidos 30un','5900000000102','Fenitoína','Antiepiléptico. 30 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,70.00,70.00,130.00,85.71,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(117,'Fenobarbital 100mg Comprimidos 20un','5900000000103','Fenobarbital','Antiepiléptico barbitúrico. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,30,8,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(118,'Haloperidol 5mg Comprimidos 20un','5900000000104','Haloperidol','Antipsicótico. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(119,'Clorpromazina 100mg Comprimidos 20un','5900000000105','Clorpromazina','Antipsicótico clássico. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,70.00,70.00,130.00,85.71,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(120,'Cloranfenicol 0.5% Colírio 5ml','5900000000106','Cloranfenicol','Antibiótico ocular. Frasco 5ml.',19,NULL,'frasco','caixa','unidade',1.000,60.00,60.00,110.00,83.33,60,15,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(121,'Gentamicina 0.3% Colírio 5ml','5900000000107','Gentamicina','Antibiótico ocular aminoglicosídeo. Frasco 5ml.',19,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,150.00,87.50,45,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(122,'Dexametasona 0.1% Colírio 5ml','5900000000108','Dexametasona','Corticoide ocular. Frasco 5ml.',19,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,150.00,87.50,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(123,'Lágrimas Artificiais 10ml','5900000000109','Carboximetilcelulose','Lubrificante ocular. Frasco 10ml.',19,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,150.00,87.50,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(124,'Timolol 0.5% Colírio 5ml','5900000000110','Timolol','Beta-bloqueador para glaucoma. Frasco 5ml.',19,NULL,'frasco','caixa','unidade',1.000,100.00,100.00,180.00,80.00,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(125,'Hidrocortisona 1% Creme 30g','5900000000111','Hidrocortisona','Corticoide tópico suave. Bisnaga 30g.',20,NULL,'unidade','caixa','unidade',1.000,80.00,80.00,150.00,87.50,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(126,'Betametasona 0.1% Creme 30g','5900000000112','Betametasona','Corticoide tópico potente. Bisnaga 30g.',20,NULL,'unidade','caixa','unidade',1.000,90.00,90.00,160.00,77.78,50,12,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(127,'Calamine Loção 100ml','5900000000113','Calamine','Antipruriginoso e adstringente. Frasco 100ml.',20,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,45,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(128,'Peróxido de Benzoílo 5% Gel 30g','5900000000114','Peróxido de Benzoílo','Acne. Gel 30g.',20,NULL,'unidade','caixa','unidade',1.000,100.00,100.00,180.00,80.00,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(129,'Mupirocina 2% Pomada 15g','5900000000115','Mupirocina','Antibiótico tópico para infecções cutâneas. 15g.',20,NULL,'unidade','caixa','unidade',1.000,140.00,140.00,240.00,71.43,35,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(130,'Aciclovir 5% Creme 5g','5900000000116','Aciclovir','Antiviral tópico para herpes labial. Bisnaga 5g.',20,3,'caixa','caixa','unidade',1.000,90.00,90.00,160.00,77.78,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 10:02:38'),
(131,'Clobetasol 0.05% Creme 30g','5900000000117','Clobetasol','Corticoide tópico muito potente. Bisnaga 30g.',20,NULL,'unidade','caixa','unidade',1.000,120.00,120.00,210.00,75.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(132,'Azul de Metileno 1% 30ml','5900000000118','Azul de Metileno','Antisséptico cutâneo. Frasco 30ml.',20,NULL,'frasco','caixa','unidade',1.000,40.00,40.00,80.00,100.00,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(133,'Permanganato de Potássio Comp 400mg','5900000000119','Permanganato de Potássio','Antisséptico e adstringente tópico.',20,NULL,'unidade','caixa','unidade',1.000,10.00,10.00,20.00,100.00,150,30,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(134,'Paracetamol+Pseudoefedrina Comprimidos','5900000000120','Paracetamol+Pseudoefedrina','Antigripal combinado. 12 comprimidos.',22,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(135,'Ibuprofeno+Pseudoefedrina 200/30mg','5900000000121','Ibuprofeno+Pseudoefedrina','Antigripal com descongestionante. 12 comprimidos.',22,NULL,'caixa','caixa','unidade',1.000,70.00,70.00,130.00,85.71,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(136,'Vitamina C 1000mg Efervescente 10un','5900000000122','Ácido Ascórbico','Vitamina C efervescente. 10 comprimidos.',22,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,140.00,75.00,90,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(137,'Mel+Limão+Eucalipto Xarope 150ml','5900000000123','Mel+Limão','Xarope natural para garganta. Frasco 150ml.',22,NULL,'frasco','caixa','unidade',1.000,100.00,100.00,180.00,80.00,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(138,'Vitamina C 500mg Comprimidos 30un','5900000000124','Ácido Ascórbico','Vitamina C oral. 30 comprimidos.',23,NULL,'caixa','caixa','unidade',1.000,70.00,70.00,130.00,85.71,90,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(139,'Vitamina D3 1000UI Cápsulas 30un','5900000000125','Colecalciferol','Vitamina D3 para ossos. 30 cápsulas.',23,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(140,'Vitamina B Complex Comprimidos 30un','5900000000126','Complexo B','Complexo vitamínico B. 30 comprimidos.',23,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(141,'Vitamina E 400UI Cápsulas 30un','5900000000127','Tocoferol','Vitamina E antioxidante. 30 cápsulas.',23,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(142,'Ácido Fólico 5mg Comprimidos 30un','5900000000128','Ácido Fólico','Vitamina B9 para gestantes. 30 comprimidos.',23,NULL,'caixa','caixa','unidade',1.000,40.00,40.00,80.00,100.00,100,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(143,'Ferro+Ácido Fólico Comprimidos 30un','5900000000129','Ferro+Ácido Fólico','Suplemento para anemia gestacional. 30 comprimidos.',24,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,100,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(144,'Sulfato Ferroso 200mg Comprimidos 30un','5900000000130','Sulfato Ferroso','Suplemento de ferro para anemia. 30 comprimidos.',24,NULL,'caixa','caixa','unidade',1.000,40.00,40.00,80.00,100.00,100,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(145,'Gluconato de Cálcio 500mg 30un','5900000000131','Gluconato de Cálcio','Suplemento de cálcio. 30 comprimidos.',24,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(146,'Cálcio+Vitamina D3 Comprimidos 30un','5900000000132','Cálcio+Vitamina D3','Suplemento para ossos. 30 comprimidos.',24,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,65,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(147,'Zinco 20mg Comprimidos 30un','5900000000133','Zinco','Suplemento mineral imunitário. 30 comprimidos.',24,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(148,'Magnésio 300mg Comprimidos 30un','5900000000134','Magnésio','Suplemento mineral muscular. 30 comprimidos.',24,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(149,'Multivitamínico Adulto 30un','5900000000135','Multivitamínico','Suplemento multivitamínico completo adulto.',23,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(150,'Multivitamínico Infantil Xarope 150ml','5900000000136','Multivitamínico','Suplemento pediátrico. Frasco 150ml.',23,NULL,'frasco','caixa','unidade',1.000,150.00,150.00,260.00,73.33,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(151,'Óleo de Fígado de Bacalhau 100ml','5900000000137','Óleo de Fígado de Bacalhau','Vitaminas A e D naturais. Frasco 100ml.',23,NULL,'frasco','caixa','unidade',1.000,100.00,100.00,180.00,80.00,45,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(152,'Vitamina A 200000UI Cápsula','5900000000138','Retinol','Suplemento de vitamina A. 1 cápsula.',23,NULL,'unidade','caixa','unidade',1.000,20.00,20.00,40.00,100.00,200,50,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(153,'Levonorgestrel+Etinilestradiol 21un','5900000000139','Levonorgestrel+Etinilestradiol','Contraceptivo oral combinado. 21 comprimidos.',25,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,80,20,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(154,'Levonorgestrel 1.5mg Comprimido','5900000000140','Levonorgestrel','Contracepção de emergência. 1 comprimido.',25,NULL,'unidade','caixa','unidade',1.000,100.00,100.00,180.00,80.00,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(155,'Desogestrel 75mcg Comprimidos 28un','5900000000141','Desogestrel','Minipílula. 28 comprimidos.',25,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(156,'Medroxiprogesterona 150mg/ml Inj','5900000000142','Medroxiprogesterona','Contraceptivo injectável trimestral.',25,NULL,'unidade','caixa','unidade',1.000,150.00,150.00,260.00,73.33,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(157,'Álcool Etílico 70% 1000ml','5900000000143','Álcool Etílico','Antisséptico. Frasco 1 litro.',6,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,100,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(158,'Água Oxigenada 3% 100ml','5900000000144','Peróxido de Hidrogénio','Antisséptico para feridas. Frasco 100ml.',6,NULL,'frasco','caixa','unidade',1.000,30.00,30.00,60.00,100.00,150,30,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(159,'Iodo Povidona 10% Solução 100ml','5900000000145','Povidona Iodada','Antisséptico de largo espectro. Frasco 100ml.',6,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,150.00,87.50,90,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(160,'Clorexidina 0.12% Solução 250ml','5900000000146','Clorexidina','Antisséptico oral e cutâneo. Frasco 250ml.',6,NULL,'frasco','caixa','unidade',1.000,90.00,90.00,160.00,77.78,70,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(161,'Gazes Esterilizadas 10x10cm 10un','5900000000147','Gazes','Penso esterilizado. 10 unidades.',6,NULL,'caixa','caixa','unidade',1.000,30.00,30.00,60.00,100.00,200,40,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(162,'Ligadura Elástica 10cm x 4.5m','5900000000148','Ligadura','Ligadura elástica para imobilização.',6,NULL,'unidade','caixa','unidade',1.000,40.00,40.00,80.00,100.00,100,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(163,'Adesivo Rápido Sortido 40un','5900000000149','Adesivo','Pensos rápidos sortidos. 40 unidades.',6,NULL,'caixa','caixa','unidade',1.000,50.00,50.00,90.00,80.00,150,30,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(164,'Seringa 5ml c/ Agulha 100un','5900000000150','Seringa','Seringa descartável 5ml. Caixa 100 unidades.',6,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,350.00,75.00,30,8,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(165,'Luvas Latex Descartáveis M 100un','5900000000151','Luvas','Luvas de látex descartáveis tamanho M.',6,NULL,'caixa','caixa','unidade',1.000,250.00,250.00,400.00,60.00,20,5,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(166,'Termómetro Digital','5900000000152','Termómetro','Termómetro digital auricular.',4,NULL,'unidade','caixa','unidade',1.000,300.00,300.00,500.00,66.67,25,6,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(167,'Tensiómetro Digital de Pulso','5900000000153','Tensiómetro','Aparelho de medição de tensão arterial digital.',4,NULL,'unidade','caixa','unidade',1.000,1800.00,1800.00,2800.00,55.56,8,3,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(168,'Glucómetro Kit Completo','5900000000154','Glucómetro','Kit completo com 10 tiras reactivas.',4,NULL,'unidade','caixa','unidade',1.000,1200.00,1200.00,1900.00,58.33,10,3,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(169,'Tiras Reactivas Glicose 50un','5900000000155','Tiras Reactivas','Tiras para medição de glicose. 50 unidades.',4,NULL,'caixa','caixa','unidade',1.000,350.00,350.00,550.00,57.14,20,5,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(170,'Vaselina Pura 100g','5900000000156','Vaselina','Emoliente e protector cutâneo. 100g.',2,NULL,'unidade','caixa','unidade',1.000,40.00,40.00,80.00,100.00,100,25,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(171,'Creme Hidratante Nívea 200ml','5900000000157','Urea+Glicerina','Hidratante corporal para pele seca. 200ml.',2,NULL,'frasco','caixa','unidade',1.000,150.00,150.00,260.00,73.33,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(172,'Protetor Solar FPS50 50ml','5900000000158','Filtro Solar','Proteção solar facial FPS50. 50ml.',2,NULL,'unidade','caixa','unidade',1.000,200.00,200.00,350.00,75.00,35,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(173,'Creme Anti-estrias 150ml','5900000000159','Centella Asiática','Prevenção e tratamento de estrias. 150ml.',2,NULL,'frasco','caixa','unidade',1.000,180.00,180.00,300.00,66.67,30,8,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(174,'Sabonete Líquido Íntimo 200ml','5900000000160','Ácido Láctico','Higiene íntima com pH balanceado. 200ml.',5,NULL,'frasco','caixa','unidade',1.000,100.00,100.00,180.00,80.00,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(175,'Creme para Assadura Bepanthen 30g','5900000000161','Dexpantenol','Creme preventivo de assaduras. Bisnaga 30g.',5,NULL,'unidade','caixa','unidade',1.000,160.00,160.00,270.00,68.75,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(176,'Talco Infantil Johnson 200g','5900000000162','Talco','Talco absorvente para bebé. 200g.',5,NULL,'unidade','caixa','unidade',1.000,120.00,120.00,200.00,66.67,60,15,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(177,'Soro Fisiológico Nasal Bebé 30ml','5900000000163','Cloreto de Sódio 0.9%','Lavagem nasal pediátrica. Frasco 30ml.',5,NULL,'frasco','caixa','unidade',1.000,80.00,80.00,140.00,75.00,80,20,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(178,'Chupeta Ortodôntica 0-6 meses','5900000000164','Chupeta','Chupeta ortodôntica silicone. 0-6 meses.',5,NULL,'unidade','caixa','unidade',1.000,80.00,80.00,150.00,87.50,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(179,'Aciclovir 200mg Comprimidos 25un','5900000000165','Aciclovir','Antiviral para herpes simples. 25 comprimidos.',16,NULL,'caixa','caixa','cartela',10.000,8.00,80.00,15.00,-81.25,40,10,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-07 05:58:42'),
(180,'Aciclovir 400mg Comprimidos 21un','5900000000166','Aciclovir','Antiviral para herpes zoster. 21 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,180.00,180.00,300.00,66.67,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(181,'Cloranfenicol 250mg Cápsulas 20un','5900000000167','Cloranfenicol','Antibiótico de reserva. 20 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(182,'Heparina Sódica 5000UI/ml 5ml','5900000000168','Heparina','Anticoagulante injectável. Frasco 5ml.',12,NULL,'frasco','caixa','unidade',1.000,300.00,300.00,500.00,66.67,15,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(183,'Varfarina 5mg Comprimidos 28un','5900000000169','Varfarina','Anticoagulante oral. 28 comprimidos.',12,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(184,'Ácido Tranexâmico 500mg 10un','5900000000170','Ácido Tranexâmico','Antifibrinolítico para hemorragias. 10 comprimidos.',12,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(185,'Ocitocina 10UI/ml Ampolas 5un','5900000000171','Ocitocina','Hormona uterotónica. 5 ampolas.',17,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,350.00,75.00,15,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(186,'Sulfato de Magnésio 50% Ampolas 5un','5900000000172','Sulfato de Magnésio','Anticonvulsivante e tocolítico. 5 ampolas.',18,NULL,'caixa','caixa','unidade',1.000,150.00,150.00,260.00,73.33,15,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(187,'Misoprostol 200mcg Comprimidos 4un','5900000000173','Misoprostol','Prostaglandina para HPP e indução. 4 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,350.00,75.00,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(188,'Ergometrina 0.2mg Ampola','5900000000174','Ergometrina','Uterotónico para hemorragia pós-parto.',17,NULL,'unidade','caixa','unidade',1.000,80.00,80.00,150.00,87.50,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(189,'Nevirapina 200mg Comprimidos 60un','5900000000175','Nevirapina','Antirretroviral INNTR. 60 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,300.00,300.00,500.00,66.67,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(190,'Efavirenz 600mg Comprimidos 30un','5900000000176','Efavirenz','Antirretroviral INNTR. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,350.00,350.00,580.00,65.71,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(191,'Tenofovir+Lamivudina+Efavirenz 30un','5900000000177','TDF+3TC+EFV','Antirretroviral combinado 1ª linha. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,400.00,400.00,650.00,62.50,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(192,'Cotrimoxazol 480mg Comprimidos 100un','5900000000178','Cotrimoxazol','Profilaxia para imunodeprimidos. 100 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,150.00,150.00,260.00,73.33,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(193,'Fluoxetina 20mg Cápsulas 30un','5900000000179','Fluoxetina','Antidepressivo ISRS. 30 cápsulas.',18,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(194,'Sertralina 50mg Comprimidos 30un','5900000000180','Sertralina','Antidepressivo ISRS. 30 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(195,'Alprazolam 0.5mg Comprimidos 20un','5900000000181','Alprazolam','Benzodiazepina ansiolítica. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,20,5,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(196,'Risperidona 2mg Comprimidos 20un','5900000000182','Risperidona','Antipsicótico atípico. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(197,'Cloroquina 150mg+Primaquina Comp','5900000000183','Cloroquina+Primaquina','Antimalárico combinado. 14 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,35,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(198,'Coartem 80/480mg Comprimidos 6un','5900000000184','Artemeter+Lumefantrina','Antimalárico pediátrico 15-24kg. 6 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,200.00,66.67,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(199,'Artesunato 50mg Comprimidos 12un','5900000000185','Artesunato','Antimalárico monoderapia emergência. 12 comprimidos.',11,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(200,'Nitrofurantoína 100mg Cápsulas 30un','5900000000186','Nitrofurantoína','Antibacteriano urinário. 30 cápsulas.',7,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,35,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(201,'Norfloxacina 400mg Comprimidos 14un','5900000000187','Norfloxacina','Antibacteriano urinário. 14 comprimidos.',7,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(202,'Fenazopiridina 100mg Comprimidos 6un','5900000000188','Fenazopiridina','Analgésico urinário. 6 comprimidos.',14,NULL,'caixa','caixa','unidade',1.000,60.00,60.00,110.00,83.33,50,12,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(203,'Doxazosina 4mg Comprimidos 30un','5900000000189','Doxazosina','Alfa-bloqueador para HBP. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,150.00,150.00,260.00,73.33,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(204,'Tamsulosina 0.4mg Cápsulas 30un','5900000000190','Tamsulosina','Alfa-bloqueador selectivo para HBP. 30 cápsulas.',16,NULL,'caixa','caixa','unidade',1.000,200.00,200.00,350.00,75.00,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(205,'Finasterida 5mg Comprimidos 30un','5900000000191','Finasterida','Para hiperplasia benigna prostática. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,180.00,180.00,300.00,66.67,20,5,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(206,'Colchicina 0.5mg Comprimidos 20un','5900000000192','Colchicina','Para gota aguda. 20 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,25,6,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(207,'Alopurinol 300mg Comprimidos 30un','5900000000193','Alopurinol','Para gota crónica. 30 comprimidos.',8,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,35,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(208,'Levotiroxina 50mcg Comprimidos 30un','5900000000194','Levotiroxina','Hormona tiroideia. 30 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,40,10,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(209,'Levotiroxina 100mcg Comprimidos 30un','5900000000195','Levotiroxina','Hormona tiroideia dose alta. 30 comprimidos.',17,NULL,'caixa','caixa','unidade',1.000,100.00,100.00,180.00,80.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(210,'Carvedilol 6.25mg Comprimidos 30un','5900000000196','Carvedilol','Beta-bloqueador não selectivo. 30 comprimidos.',16,NULL,'caixa','caixa','unidade',1.000,120.00,120.00,210.00,75.00,30,8,1,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(211,'Clonazepam 2mg Comprimidos 20un','5900000000197','Clonazepam','Benzodiazepina antiepiléptica. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,80.00,80.00,150.00,87.50,20,5,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(212,'Lorazepam 1mg Comprimidos 20un','5900000000198','Lorazepam','Benzodiazepina ansiolítica. 20 comprimidos.',18,NULL,'caixa','caixa','unidade',1.000,90.00,90.00,160.00,77.78,20,5,1,1,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(213,'Teste de Gravidez HCG','5900000000199','Teste HCG','Teste rápido de gravidez urinário.',6,NULL,'unidade','caixa','unidade',1.000,50.00,50.00,90.00,80.00,150,30,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51'),
(214,'Teste Rápido Malária (RDT)','5900000000200','Teste RDT','Teste rápido para diagnóstico de malária.',6,NULL,'unidade','caixa','unidade',1.000,60.00,60.00,110.00,83.33,200,50,0,0,NULL,1,'2026-06-06 05:06:51','2026-06-06 05:06:51');
/*!40000 ALTER TABLE `produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receitas_medicas`
--

DROP TABLE IF EXISTS `receitas_medicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `receitas_medicas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `medico_nome` varchar(150) NOT NULL,
  `medico_ordem` varchar(40) DEFAULT NULL,
  `especialidade` varchar(100) DEFAULT NULL,
  `data_emissao` date NOT NULL,
  `validade` date NOT NULL,
  `imagem_url` varchar(500) DEFAULT NULL,
  `status` enum('pendente','usada','expirada','cancelada') NOT NULL DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `criado_por` int(10) unsigned DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_receitas_cliente` (`cliente_id`),
  KEY `idx_receitas_status` (`status`),
  KEY `idx_receitas_validade` (`validade`),
  KEY `fk_receitas_usuario` (`criado_por`),
  CONSTRAINT `fk_receitas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_receitas_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Receitas médicas para controlo de produtos que exigem prescrição';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receitas_medicas`
--

LOCK TABLES `receitas_medicas` WRITE;
/*!40000 ALTER TABLE `receitas_medicas` DISABLE KEYS */;
/*!40000 ALTER TABLE `receitas_medicas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('admin','farmaceutico','caixa','tecnico') NOT NULL DEFAULT 'caixa',
  `funcionario_id` int(10) unsigned DEFAULT NULL COMMENT 'Funcionário associado a este utilizador',
  `criado_por` int(10) unsigned DEFAULT NULL COMMENT 'Administrador que criou / atribuiu as credenciais',
  `token_reset` varchar(100) DEFAULT NULL COMMENT 'Token para redefinição de senha',
  `token_expira_em` timestamp NULL DEFAULT NULL COMMENT 'Validade do token de redefinição',
  `tentativas_login` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Contador de tentativas de login falhadas',
  `bloqueado_ate` timestamp NULL DEFAULT NULL COMMENT 'Conta bloqueada temporariamente até esta data',
  `telefone` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  KEY `idx_usuarios_perfil` (`perfil`),
  KEY `idx_usuarios_ativo` (`ativo`),
  KEY `idx_usuarios_funcionario` (`funcionario_id`),
  KEY `fk_usuarios_criado_por` (`criado_por`),
  CONSTRAINT `fk_usuarios_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_usuarios_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Utilizadores do sistema com controlo de acesso por perfil';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'Administrador','admin@kewanfarma.mz','$2y$12$imLQ0KjAn.xgVEtIUZDpT.1fUqEVxq8JVIRQBByYWAt5Qv0tQSxsi','admin',NULL,NULL,NULL,NULL,0,NULL,NULL,1,'2026-06-07 07:55:06','2026-05-21 08:50:44','2026-06-07 05:55:06'),
(2,'Patrao Manuel Alberto','patraomanuelalberto@gmail.com','$2y$12$imLQ0KjAn.xgVEtIUZDpT.1fUqEVxq8JVIRQBByYWAt5Qv0tQSxsi','admin',1,1,NULL,NULL,0,NULL,NULL,1,'2026-05-24 05:37:25','2026-05-21 22:10:39','2026-06-01 07:12:55'),
(3,'Labistruria Manuel','labi@kewanfarma.mz','$2y$12$imLQ0KjAn.xgVEtIUZDpT.1fUqEVxq8JVIRQBByYWAt5Qv0tQSxsi','farmaceutico',2,2,NULL,NULL,0,NULL,NULL,1,'2026-05-25 06:35:33','2026-05-21 22:21:06','2026-06-01 07:12:55'),
(4,'Almira Lusabio Manuel','almira@kewanfarma.mz','$2y$12$BWPM3x8mDZW82SPGy2XjhOfgSsVAzT19jfmBe9YBsZLRdQMauRnmO','farmaceutico',3,1,NULL,NULL,0,NULL,NULL,1,'2026-06-07 07:51:39','2026-06-04 18:21:43','2026-06-07 05:51:39');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_credenciais_hist_perfil
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `vendas`
--

DROP TABLE IF EXISTS `vendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `numero_venda` varchar(20) NOT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `receita_id` int(10) unsigned DEFAULT NULL,
  `forma_pagamento` enum('dinheiro','mpesa','emola','cartao_debito','cartao_credito','transferencia','credito') NOT NULL DEFAULT 'dinheiro',
  `desconto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `valor_pago` decimal(12,2) NOT NULL DEFAULT 0.00,
  `troco` decimal(12,2) GENERATED ALWAYS AS (case when `valor_pago` >= `total` then `valor_pago` - `total` else 0 end) VIRTUAL,
  `status` enum('pendente','concluida','cancelada','devolvida') NOT NULL DEFAULT 'concluida',
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vendas_numero` (`numero_venda`),
  KEY `idx_vendas_cliente` (`cliente_id`),
  KEY `idx_vendas_usuario` (`usuario_id`),
  KEY `idx_vendas_receita` (`receita_id`),
  KEY `idx_vendas_status` (`status`),
  KEY `idx_vendas_data` (`criado_em`),
  CONSTRAINT `fk_vendas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_vendas_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas_medicas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_vendas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cabeçalho das vendas realizadas na farmácia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas`
--

LOCK TABLES `vendas` WRITE;
/*!40000 ALTER TABLE `vendas` DISABLE KEYS */;
INSERT INTO `vendas` VALUES
(2,'VD-2026-00001',NULL,1,NULL,'dinheiro',0.00,120.00,120.00,200.00,80.00,'concluida',NULL,'2026-05-23 05:07:54','2026-05-23 05:07:54'),
(3,'VD-2026-00002',NULL,2,NULL,'dinheiro',5.00,360.00,355.00,500.00,145.00,'concluida',NULL,'2026-05-23 05:11:04','2026-05-23 05:11:04'),
(4,'VD-2026-00003',NULL,2,NULL,'dinheiro',10.00,240.00,230.00,300.00,70.00,'concluida',NULL,'2026-05-23 05:12:47','2026-05-23 05:12:47'),
(5,'VD-2026-00004',NULL,2,NULL,'dinheiro',0.00,70.00,70.00,100.00,30.00,'concluida',NULL,'2026-05-23 10:15:06','2026-05-23 10:15:06'),
(6,'VD-2026-00005',NULL,2,NULL,'mpesa',0.00,70.00,70.00,70.00,0.00,'concluida',NULL,'2026-05-23 10:17:24','2026-05-23 10:17:24'),
(7,'VD-2026-00006',1,2,NULL,'dinheiro',0.00,120.00,120.00,1000.00,880.00,'concluida',NULL,'2026-05-23 10:20:39','2026-05-23 10:20:39'),
(8,'VD-2026-00007',NULL,2,NULL,'dinheiro',0.00,280.00,280.00,500.00,220.00,'concluida',NULL,'2026-05-23 11:10:22','2026-05-23 11:10:22'),
(9,'VD-2026-00008',NULL,2,NULL,'emola',7.00,220.00,213.00,213.00,0.00,'concluida',NULL,'2026-05-23 12:26:42','2026-05-23 12:26:42'),
(10,'VD-2026-00009',1,2,NULL,'emola',0.00,79.00,79.00,79.00,0.00,'concluida',NULL,'2026-05-23 12:28:38','2026-05-23 12:28:38'),
(11,'VD-2026-00010',NULL,2,NULL,'emola',0.00,709.00,709.00,709.00,0.00,'concluida',NULL,'2026-05-23 14:08:43','2026-05-23 14:08:43'),
(12,'VD-2026-00011',NULL,2,NULL,'dinheiro',0.00,70.00,70.00,200.00,130.00,'concluida',NULL,'2026-05-23 14:10:22','2026-05-23 14:10:22'),
(14,'VD-2026-00012',1,1,NULL,'dinheiro',0.00,1270.00,1270.00,2000.00,730.00,'concluida',NULL,'2026-05-23 21:37:10','2026-05-23 21:37:10'),
(15,'VD-2026-00013',NULL,1,NULL,'mpesa',0.00,1027.00,1027.00,1027.00,0.00,'concluida',NULL,'2026-05-23 21:39:31','2026-05-23 21:39:31'),
(16,'VD-2026-00014',NULL,2,NULL,'dinheiro',0.00,110.00,110.00,200.00,90.00,'concluida',NULL,'2026-05-24 03:44:10','2026-05-24 03:44:10'),
(17,'VD-2026-00015',NULL,2,NULL,'mpesa',0.00,30.00,30.00,30.00,0.00,'concluida',NULL,'2026-05-24 03:45:54','2026-05-24 03:45:54'),
(18,'VD-2026-00016',NULL,2,NULL,'mpesa',0.00,20.00,20.00,20.00,0.00,'concluida',NULL,'2026-05-24 04:23:57','2026-05-24 04:23:57'),
(19,'VD-2026-00017',1,2,NULL,'dinheiro',0.00,30.00,30.00,40.00,10.00,'concluida',NULL,'2026-05-24 04:24:38','2026-05-24 04:24:38'),
(20,'VD-2026-00018',NULL,2,NULL,'emola',0.00,2422.00,2422.00,2422.00,0.00,'concluida',NULL,'2026-05-24 04:40:11','2026-05-24 04:40:11'),
(22,'VD-2026-00019',NULL,2,NULL,'transferencia',0.00,414.00,414.00,414.00,0.00,'concluida',NULL,'2026-05-24 05:05:34','2026-05-24 05:05:34'),
(23,'VD-2026-00020',NULL,1,NULL,'dinheiro',0.00,20.00,20.00,50.00,30.00,'cancelada',' [CANCELADA: Produto expirado]','2026-05-24 08:46:20','2026-05-25 00:52:16'),
(24,'VD-2026-00021',NULL,3,NULL,'dinheiro',0.00,260.00,260.00,270.00,10.00,'concluida',NULL,'2026-05-24 14:49:05','2026-05-24 14:49:05'),
(25,'VD-2026-00022',NULL,1,NULL,'dinheiro',0.00,260.00,260.00,500.00,240.00,'concluida','Precisava de ARVs','2026-06-01 07:24:20','2026-06-01 07:24:20'),
(26,'VD-2026-00023',1,1,NULL,'dinheiro',0.00,80.00,80.00,200.00,120.00,'cancelada',' [CANCELADA: v]','2026-06-02 04:30:19','2026-06-04 17:24:42'),
(27,'VD-2026-00024',NULL,1,NULL,'dinheiro',0.00,237.00,237.00,500.00,263.00,'concluida',NULL,'2026-06-05 19:04:54','2026-06-05 19:04:54'),
(28,'VD-2026-00025',NULL,1,NULL,'emola',0.00,370.00,370.00,370.00,0.00,'concluida',NULL,'2026-06-06 11:44:47','2026-06-06 11:44:47'),
(29,'VD-2026-00026',NULL,4,NULL,'mpesa',0.00,2700.00,2700.00,2700.00,0.00,'concluida',NULL,'2026-06-06 11:46:20','2026-06-06 11:46:20');
/*!40000 ALTER TABLE `vendas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_after_venda_cancel
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary table structure for view `vw_funcionarios_com_acesso`
--

DROP TABLE IF EXISTS `vw_funcionarios_com_acesso`;
/*!50001 DROP VIEW IF EXISTS `vw_funcionarios_com_acesso`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_funcionarios_com_acesso` AS SELECT
 1 AS `funcionario_id`,
  1 AS `numero_funcionario`,
  1 AS `nome_completo`,
  1 AS `foto_url`,
  1 AS `cargo`,
  1 AS `status_funcionario`,
  1 AS `usuario_id`,
  1 AS `email`,
  1 AS `perfil`,
  1 AS `acesso_activo`,
  1 AS `ultimo_login`,
  1 AS `tentativas_login`,
  1 AS `bloqueado_ate`,
  1 AS `credenciais_criadas_por` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_funcionarios_sem_acesso`
--

DROP TABLE IF EXISTS `vw_funcionarios_sem_acesso`;
/*!50001 DROP VIEW IF EXISTS `vw_funcionarios_sem_acesso`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_funcionarios_sem_acesso` AS SELECT
 1 AS `funcionario_id`,
  1 AS `numero_funcionario`,
  1 AS `nome_completo`,
  1 AS `foto_url`,
  1 AS `cargo`,
  1 AS `telefone_principal`,
  1 AS `email_pessoal`,
  1 AS `data_admissao`,
  1 AS `status` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_lotes_a_vencer`
--

DROP TABLE IF EXISTS `vw_lotes_a_vencer`;
/*!50001 DROP VIEW IF EXISTS `vw_lotes_a_vencer`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_lotes_a_vencer` AS SELECT
 1 AS `id`,
  1 AS `numero_lote`,
  1 AS `validade`,
  1 AS `quantidade`,
  1 AS `dias_para_vencer`,
  1 AS `produto_id`,
  1 AS `produto_nome`,
  1 AS `unidade_medida`,
  1 AS `status_validade` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_lotes_vencidos`
--

DROP TABLE IF EXISTS `vw_lotes_vencidos`;
/*!50001 DROP VIEW IF EXISTS `vw_lotes_vencidos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_lotes_vencidos` AS SELECT
 1 AS `id`,
  1 AS `numero_lote`,
  1 AS `validade`,
  1 AS `quantidade`,
  1 AS `dias_vencido`,
  1 AS `produto_id`,
  1 AS `produto_nome`,
  1 AS `unidade_medida` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_produtos_stock_baixo`
--

DROP TABLE IF EXISTS `vw_produtos_stock_baixo`;
/*!50001 DROP VIEW IF EXISTS `vw_produtos_stock_baixo`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_produtos_stock_baixo` AS SELECT
 1 AS `id`,
  1 AS `nome`,
  1 AS `codigo_barras`,
  1 AS `categoria`,
  1 AS `estoque_actual`,
  1 AS `estoque_min`,
  1 AS `deficit`,
  1 AS `fornecedor` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_rastreabilidade_vendas`
--

DROP TABLE IF EXISTS `vw_rastreabilidade_vendas`;
/*!50001 DROP VIEW IF EXISTS `vw_rastreabilidade_vendas`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_rastreabilidade_vendas` AS SELECT
 1 AS `item_id`,
  1 AS `numero_venda`,
  1 AS `data_venda`,
  1 AS `produto_nome`,
  1 AS `numero_lote`,
  1 AS `lote_validade`,
  1 AS `quantidade`,
  1 AS `preco_unitario`,
  1 AS `subtotal`,
  1 AS `cliente_nome`,
  1 AS `vendedor_nome` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_top_produtos_30d`
--

DROP TABLE IF EXISTS `vw_top_produtos_30d`;
/*!50001 DROP VIEW IF EXISTS `vw_top_produtos_30d`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_top_produtos_30d` AS SELECT
 1 AS `id`,
  1 AS `nome`,
  1 AS `unidades_vendidas`,
  1 AS `valor_total`,
  1 AS `num_vendas` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_vendas_por_dia`
--

DROP TABLE IF EXISTS `vw_vendas_por_dia`;
/*!50001 DROP VIEW IF EXISTS `vw_vendas_por_dia`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_vendas_por_dia` AS SELECT
 1 AS `data`,
  1 AS `total_vendas`,
  1 AS `valor_total`,
  1 AS `descontos_concedidos`,
  1 AS `ticket_medio` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'kewanfarma'
--

--
-- Final view structure for view `vw_funcionarios_com_acesso`
--

/*!50001 DROP VIEW IF EXISTS `vw_funcionarios_com_acesso`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_funcionarios_com_acesso` AS select `f`.`id` AS `funcionario_id`,`f`.`numero_funcionario` AS `numero_funcionario`,`f`.`nome_completo` AS `nome_completo`,`f`.`foto_url` AS `foto_url`,`c`.`nome` AS `cargo`,`f`.`status` AS `status_funcionario`,`u`.`id` AS `usuario_id`,`u`.`email` AS `email`,`u`.`perfil` AS `perfil`,`u`.`ativo` AS `acesso_activo`,`u`.`ultimo_login` AS `ultimo_login`,`u`.`tentativas_login` AS `tentativas_login`,`u`.`bloqueado_ate` AS `bloqueado_ate`,`adm`.`nome` AS `credenciais_criadas_por` from (((`funcionarios` `f` join `cargos` `c` on(`c`.`id` = `f`.`cargo_id`)) left join `usuarios` `u` on(`u`.`funcionario_id` = `f`.`id`)) left join `usuarios` `adm` on(`adm`.`id` = `u`.`criado_por`)) order by `f`.`nome_completo` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_funcionarios_sem_acesso`
--

/*!50001 DROP VIEW IF EXISTS `vw_funcionarios_sem_acesso`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_funcionarios_sem_acesso` AS select `f`.`id` AS `funcionario_id`,`f`.`numero_funcionario` AS `numero_funcionario`,`f`.`nome_completo` AS `nome_completo`,`f`.`foto_url` AS `foto_url`,`c`.`nome` AS `cargo`,`f`.`telefone_principal` AS `telefone_principal`,`f`.`email_pessoal` AS `email_pessoal`,`f`.`data_admissao` AS `data_admissao`,`f`.`status` AS `status` from ((`funcionarios` `f` join `cargos` `c` on(`c`.`id` = `f`.`cargo_id`)) left join `usuarios` `u` on(`u`.`funcionario_id` = `f`.`id`)) where `u`.`id` is null and `f`.`status` = 'activo' order by `f`.`nome_completo` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_lotes_a_vencer`
--

/*!50001 DROP VIEW IF EXISTS `vw_lotes_a_vencer`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_lotes_a_vencer` AS select `l`.`id` AS `id`,`l`.`numero_lote` AS `numero_lote`,`l`.`validade` AS `validade`,`l`.`quantidade` AS `quantidade`,to_days(`l`.`validade`) - to_days(curdate()) AS `dias_para_vencer`,`p`.`id` AS `produto_id`,`p`.`nome` AS `produto_nome`,`p`.`unidade_medida` AS `unidade_medida`,case when to_days(`l`.`validade`) - to_days(curdate()) <= 30 then 'critico' when to_days(`l`.`validade`) - to_days(curdate()) <= 60 then 'atencao' when to_days(`l`.`validade`) - to_days(curdate()) <= 90 then 'aviso' else 'ok' end AS `status_validade` from (`lotes` `l` join `produtos` `p` on(`p`.`id` = `l`.`produto_id`)) where `l`.`quantidade` > 0 and `l`.`validade` >= curdate() order by `l`.`validade` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_lotes_vencidos`
--

/*!50001 DROP VIEW IF EXISTS `vw_lotes_vencidos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_lotes_vencidos` AS select `l`.`id` AS `id`,`l`.`numero_lote` AS `numero_lote`,`l`.`validade` AS `validade`,`l`.`quantidade` AS `quantidade`,to_days(curdate()) - to_days(`l`.`validade`) AS `dias_vencido`,`p`.`id` AS `produto_id`,`p`.`nome` AS `produto_nome`,`p`.`unidade_medida` AS `unidade_medida` from (`lotes` `l` join `produtos` `p` on(`p`.`id` = `l`.`produto_id`)) where `l`.`quantidade` > 0 and `l`.`validade` < curdate() order by `l`.`validade` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_produtos_stock_baixo`
--

/*!50001 DROP VIEW IF EXISTS `vw_produtos_stock_baixo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_produtos_stock_baixo` AS select `p`.`id` AS `id`,`p`.`nome` AS `nome`,`p`.`codigo_barras` AS `codigo_barras`,`c`.`nome` AS `categoria`,`p`.`estoque_actual` AS `estoque_actual`,`p`.`estoque_min` AS `estoque_min`,`p`.`estoque_min` - `p`.`estoque_actual` AS `deficit`,`f`.`nome` AS `fornecedor` from ((`produtos` `p` join `categorias` `c` on(`c`.`id` = `p`.`categoria_id`)) left join `fornecedores` `f` on(`f`.`id` = `p`.`fornecedor_id`)) where `p`.`estoque_actual` < `p`.`estoque_min` and `p`.`ativo` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_rastreabilidade_vendas`
--

/*!50001 DROP VIEW IF EXISTS `vw_rastreabilidade_vendas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_rastreabilidade_vendas` AS select `iv`.`id` AS `item_id`,`v`.`numero_venda` AS `numero_venda`,`v`.`criado_em` AS `data_venda`,`p`.`nome` AS `produto_nome`,`l`.`numero_lote` AS `numero_lote`,`l`.`validade` AS `lote_validade`,`iv`.`quantidade` AS `quantidade`,`iv`.`preco_unitario` AS `preco_unitario`,`iv`.`subtotal` AS `subtotal`,`c`.`nome` AS `cliente_nome`,`u`.`nome` AS `vendedor_nome` from (((((`itens_venda` `iv` join `vendas` `v` on(`v`.`id` = `iv`.`venda_id`)) join `produtos` `p` on(`p`.`id` = `iv`.`produto_id`)) left join `lotes` `l` on(`l`.`id` = `iv`.`lote_id`)) left join `clientes` `c` on(`c`.`id` = `v`.`cliente_id`)) left join `usuarios` `u` on(`u`.`id` = `v`.`usuario_id`)) order by `v`.`criado_em` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_top_produtos_30d`
--

/*!50001 DROP VIEW IF EXISTS `vw_top_produtos_30d`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_top_produtos_30d` AS select `p`.`id` AS `id`,`p`.`nome` AS `nome`,sum(`iv`.`quantidade`) AS `unidades_vendidas`,sum(`iv`.`subtotal`) AS `valor_total`,count(distinct `iv`.`venda_id`) AS `num_vendas` from ((`itens_venda` `iv` join `produtos` `p` on(`p`.`id` = `iv`.`produto_id`)) join `vendas` `v` on(`v`.`id` = `iv`.`venda_id`)) where `v`.`status` = 'concluida' and `v`.`criado_em` >= curdate() - interval 30 day group by `p`.`id`,`p`.`nome` order by sum(`iv`.`quantidade`) desc limit 10 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_vendas_por_dia`
--

/*!50001 DROP VIEW IF EXISTS `vw_vendas_por_dia`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_vendas_por_dia` AS select cast(`v`.`criado_em` as date) AS `data`,count(`v`.`id`) AS `total_vendas`,sum(`v`.`total`) AS `valor_total`,sum(`v`.`desconto`) AS `descontos_concedidos`,avg(`v`.`total`) AS `ticket_medio` from `vendas` `v` where `v`.`status` = 'concluida' group by cast(`v`.`criado_em` as date) order by cast(`v`.`criado_em` as date) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-07  6:30:40
