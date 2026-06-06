-- ================================================================
-- KewanFarma — 200 Produtos Farmacêuticos
-- Preços em MZN (Meticais) — mercado moçambicano 2025
-- ================================================================

-- Garantir categorias completas
INSERT IGNORE INTO categorias (nome, descricao, categoria_pai_id) VALUES
('Antigripais',          'Medicamentos para gripe e constipação', 1),
('Gastrointestinais',    'Medicamentos para o sistema digestivo', 1),
('Respiratórios',        'Medicamentos para o sistema respiratório', 1),
('Antifúngicos',         'Medicamentos antifúngicos', 1),
('Antivirais',           'Medicamentos antivirais', 1),
('Antihipertensores',    'Medicamentos para hipertensão arterial', 1),
('SNC',                  'Medicamentos para o sistema nervoso central', 1),
('Oftalmológicos',       'Medicamentos para os olhos', 1),
('Dermatológicos',       'Medicamentos para a pele', 1),
('Analgésicos',          'Medicamentos para alívio da dor', 1),
('Antialérgicos',        'Medicamentos para alergias', 1),
('Antieméticos',         'Medicamentos para náuseas e vómitos', 1),
('Vitaminas',            'Vitaminas e complexos vitamínicos', 3),
('Minerais',             'Minerais e suplementos minerais', 3),
('Contraceptivos',       'Métodos contraceptivos hormonais', 1);

-- ================================================================
-- INSERIR 200 PRODUTOS
-- ================================================================
INSERT INTO produtos (nome, codigo_barras, principio_ativo, descricao, categoria_id, unidade_medida, preco_compra, preco_venda, estoque_actual, estoque_min, requer_receita, controlado, ativo) VALUES

-- ── ANTIBIÓTICOS (cat 7) ──────────────────────────────────────
('Amoxicilina 500mg Cápsulas 21un',        '5900000000001', 'Amoxicilina',              'Antibiótico de largo espectro. 21 cápsulas.',                        7, 'caixa',     180, 280,  45, 10, 1, 0, 1),
('Amoxicilina 250mg/5ml Suspensão 100ml',  '5900000000002', 'Amoxicilina',              'Suspensão oral pediátrica. Frasco 100ml.',                           7, 'frasco',    120, 190,  30,  8, 1, 0, 1),
('Amoxicilina+Clavulanato 875mg 14un',     '5900000000003', 'Amoxicilina+Ácido Clavulânico', 'Antibiótico de largo espectro com inibidor. 14 comprimidos.',   7, 'caixa',     380, 580,  20,  5, 1, 0, 1),
('Azitromicina 500mg Comprimidos 3un',     '5900000000004', 'Azitromicina',             'Macrólido de curta duração. 3 comprimidos.',                         7, 'caixa',     200, 320,  35,  8, 1, 0, 1),
('Azitromicina 200mg/5ml Suspensão',       '5900000000005', 'Azitromicina',             'Suspensão pediátrica 30ml.',                                         7, 'frasco',    160, 260,  25,  6, 1, 0, 1),
('Ciprofloxacina 500mg Comprimidos 10un',  '5900000000006', 'Ciprofloxacina',           'Fluoroquinolona de largo espectro. 10 comprimidos.',                 7, 'caixa',     160, 260,  40,  8, 1, 0, 1),
('Doxiciclina 100mg Cápsulas 10un',        '5900000000007', 'Doxiciclina',              'Tetraciclina de largo espectro. 10 cápsulas.',                       7, 'caixa',     120, 200,  30,  8, 1, 0, 1),
('Metronidazol 400mg Comprimidos 21un',    '5900000000008', 'Metronidazol',             'Antibiótico e antiprotozoário. 21 comprimidos.',                     7, 'caixa',     80,  140,  50, 10, 1, 0, 1),
('Metronidazol 250mg/5ml Suspensão',       '5900000000009', 'Metronidazol',             'Suspensão oral pediátrica 100ml.',                                   7, 'frasco',    90,  150,  20,  5, 1, 0, 1),
('Eritromicina 500mg Comprimidos 20un',    '5900000000010', 'Eritromicina',             'Macrólido para alérgicos à penicilina. 20 comprimidos.',             7, 'caixa',     140, 230,  25,  6, 1, 0, 1),
('Cefalexina 500mg Cápsulas 20un',         '5900000000011', 'Cefalexina',               'Cefalosporina de 1ª geração. 20 cápsulas.',                          7, 'caixa',     200, 320,  20,  5, 1, 0, 1),
('Clindamicina 300mg Cápsulas 16un',       '5900000000012', 'Clindamicina',             'Lincosamida para infecções graves. 16 cápsulas.',                    7, 'caixa',     320, 500,  15,  5, 1, 0, 1),
('Sulfametoxazol+Trimetoprim 800/160mg',   '5900000000013', 'Sulfametoxazol+Trimetoprim','Antibacteriano de largo espectro. 14 comprimidos.',                 7, 'caixa',     60,  110,  60, 12, 1, 0, 1),
('Ampicilina 500mg Cápsulas 20un',         '5900000000014', 'Ampicilina',               'Penicilina de largo espectro. 20 cápsulas.',                         7, 'caixa',     100, 170,  30,  8, 1, 0, 1),
('Ceftriaxona 1g Pó Injectável',           '5900000000015', 'Ceftriaxona',              'Cefalosporina 3ª geração para uso hospitalar.',                      7, 'unidade',   280, 450,  20,  5, 1, 0, 1),

-- ── ANTI-INFLAMATÓRIOS / ANALGÉSICOS (cat 8 e 17) ─────────────
('Paracetamol 500mg Comprimidos 20un',     '5900000000016', 'Paracetamol',              'Analgésico e antipirético. 20 comprimidos.',                         8, 'caixa',     30,  60,  100, 20, 0, 0, 1),
('Paracetamol 1000mg Comprimidos 20un',    '5900000000017', 'Paracetamol',              'Analgésico forte. 20 comprimidos.',                                  8, 'caixa',     50,  90,   80, 15, 0, 0, 1),
('Paracetamol 120mg/5ml Xarope 100ml',     '5900000000018', 'Paracetamol',              'Xarope pediátrico. Frasco 100ml.',                                   8, 'frasco',    60,  110,  60, 12, 0, 0, 1),
('Paracetamol 250mg Supositórios 10un',    '5900000000019', 'Paracetamol',              'Supositórios pediátricos. 10 unidades.',                             8, 'caixa',     80,  140,  30,  8, 0, 0, 1),
('Ibuprofeno 400mg Comprimidos 20un',      '5900000000020', 'Ibuprofeno',               'Anti-inflamatório e analgésico. 20 comprimidos.',                    8, 'caixa',     60,  110,  70, 15, 0, 0, 1),
('Ibuprofeno 200mg/5ml Suspensão 100ml',   '5900000000021', 'Ibuprofeno',               'Suspensão pediátrica. Frasco 100ml.',                                8, 'frasco',    90,  160,  40, 10, 0, 0, 1),
('Diclofenac 50mg Comprimidos 20un',       '5900000000022', 'Diclofenac Sódico',        'Anti-inflamatório para dor e inflamação. 20 comprimidos.',           8, 'caixa',     70,  130,  60, 12, 0, 0, 1),
('Diclofenac 75mg/3ml Injectável 3un',     '5900000000023', 'Diclofenac Sódico',        'Ampolas injectáveis para dor intensa. 3 ampolas.',                   8, 'caixa',     120, 210,  25,  6, 1, 0, 1),
('Naproxeno 500mg Comprimidos 20un',       '5900000000024', 'Naproxeno',                'Anti-inflamatório de longa duração. 20 comprimidos.',                8, 'caixa',     90,  160,  40, 10, 0, 0, 1),
('Piroxicam 20mg Cápsulas 20un',           '5900000000025', 'Piroxicam',                'Anti-inflamatório de longa duração. 20 cápsulas.',                   8, 'caixa',     80,  150,  30,  8, 0, 0, 1),
('Meloxicam 15mg Comprimidos 10un',        '5900000000026', 'Meloxicam',                'Anti-inflamatório selectivo COX-2. 10 comprimidos.',                 8, 'caixa',     100, 180,  35,  8, 1, 0, 1),
('Tramadol 50mg Cápsulas 20un',            '5900000000027', 'Tramadol',                 'Analgésico opioide para dor moderada a intensa.',                    17,'caixa',     200, 350,  20,  5, 1, 1, 1),
('Codeína+Paracetamol 30/500mg 20un',      '5900000000028', 'Codeína+Paracetamol',      'Analgésico opioide combinado. 20 comprimidos.',                      17,'caixa',     180, 300,  15,  5, 1, 1, 1),

-- ── ANTIMALÁRICOS (cat 11) ────────────────────────────────────
('Artemeter+Lumefantrina 20/120mg 24un',   '5900000000029', 'Artemeter+Lumefantrina',   'Antimalárico de 1ª linha adulto. 24 comprimidos.',                   11,'caixa',     180, 290,  80, 20, 1, 0, 1),
('Artemeter+Lumefantrina Pediátrico 12un', '5900000000030', 'Artemeter+Lumefantrina',   'Antimalárico pediátrico. 12 comprimidos dispersíveis.',              11,'caixa',     150, 250,  60, 15, 1, 0, 1),
('Artesunato+Amodiaquina 100/270mg',       '5900000000031', 'Artesunato+Amodiaquina',   'Antimalárico combinado. 3 comprimidos.',                             11,'caixa',     120, 200,  50, 12, 1, 0, 1),
('Sulfadoxina+Pirimetamina 500/25mg',      '5900000000032', 'Sulfadoxina+Pirimetamina', 'Antimalárico preventivo (TPI). 3 comprimidos.',                      11,'caixa',     60,  110,  70, 15, 1, 0, 1),
('Cloroquina 250mg Comprimidos 25un',      '5900000000033', 'Cloroquina',               'Antimalárico e anti-inflamatório. 25 comprimidos.',                  11,'caixa',     80,  140,  40, 10, 1, 0, 1),
('Primaquina 15mg Comprimidos 14un',       '5900000000034', 'Primaquina',               'Antimalárico para prevenção de recaídas. 14 comprimidos.',           11,'caixa',     100, 170,  30,  8, 1, 0, 1),
('Quinina 300mg Comprimidos 30un',         '5900000000035', 'Quinina',                  'Antimalárico de 2ª linha. 30 comprimidos.',                          11,'caixa',     140, 230,  25,  6, 1, 0, 1),

-- ── ANTIPARASITÁRIOS (cat 10) ─────────────────────────────────
('Albendazol 400mg Comprimidos 1un',       '5900000000036', 'Albendazol',               'Antiparasitário de largo espectro. 1 comprimido.',                   10,'unidade',   25,  50,  150, 30, 0, 0, 1),
('Albendazol 200mg/5ml Suspensão 10ml',    '5900000000037', 'Albendazol',               'Suspensão pediátrica. Frasco 10ml.',                                 10,'frasco',    60,  110,  80, 20, 0, 0, 1),
('Mebendazol 100mg Comprimidos 6un',       '5900000000038', 'Mebendazol',               'Antiparasitário intestinal. 6 comprimidos.',                         10,'caixa',     30,  60,  120, 25, 0, 0, 1),
('Ivermectina 6mg Comprimidos 4un',        '5900000000039', 'Ivermectina',              'Antiparasitário para sarna e helmintas. 4 comprimidos.',             10,'caixa',     80,  150,  60, 15, 1, 0, 1),
('Praziquantel 600mg Comprimidos 6un',     '5900000000040', 'Praziquantel',             'Antiparasitário para esquistossomose. 6 comprimidos.',               10,'caixa',     120, 200,  30,  8, 1, 0, 1),
('Tinidazol 500mg Comprimidos 4un',        '5900000000041', 'Tinidazol',                'Antiprotozoário para giardíase e amebíase. 4 comprimidos.',          10,'caixa',     60,  110,  50, 12, 1, 0, 1),

-- ── ANTIFÚNGICOS (cat 15) ─────────────────────────────────────
('Fluconazol 150mg Cápsula 1un',           '5900000000042', 'Fluconazol',               'Antifúngico sistémico dose única. 1 cápsula.',                       15,'unidade',   60,  110,  80, 20, 1, 0, 1),
('Fluconazol 50mg Cápsulas 7un',           '5900000000043', 'Fluconazol',               'Antifúngico sistémico. 7 cápsulas.',                                 15,'caixa',     120, 200,  40, 10, 1, 0, 1),
('Clotrimazol 1% Creme 20g',               '5900000000044', 'Clotrimazol',              'Antifúngico tópico para dermatomicoses. Bisnaga 20g.',               15,'unidade',   80,  150,  60, 15, 0, 0, 1),
('Clotrimazol 100mg Óvulos Vaginais 6un',  '5900000000045', 'Clotrimazol',              'Antifúngico vaginal. 6 óvulos.',                                     15,'caixa',     100, 180,  40, 10, 1, 0, 1),
('Miconazol 2% Creme 30g',                 '5900000000046', 'Miconazol',                'Antifúngico tópico. Bisnaga 30g.',                                   15,'unidade',   90,  160,  50, 12, 0, 0, 1),
('Nistatina 100000UI/g Creme 30g',         '5900000000047', 'Nistatina',                'Antifúngico para candidíase cutânea. Bisnaga 30g.',                  15,'unidade',   80,  140,  45, 10, 1, 0, 1),
('Nistatina Suspensão Oral 100ml',         '5900000000048', 'Nistatina',                'Antifúngico oral para candidíase. Frasco 100ml.',                    15,'frasco',    100, 170,  30,  8, 1, 0, 1),

-- ── GASTROINTESTINAIS (cat 14) ────────────────────────────────
('Omeprazol 20mg Cápsulas 14un',           '5900000000049', 'Omeprazol',                'Inibidor da bomba de protões. 14 cápsulas.',                         14,'caixa',     80,  150,  80, 20, 0, 0, 1),
('Omeprazol 40mg Cápsulas 14un',           '5900000000050', 'Omeprazol',                'Inibidor da bomba de protões dose alta. 14 cápsulas.',               14,'caixa',     120, 210,  50, 12, 1, 0, 1),
('Ranitidina 150mg Comprimidos 20un',      '5900000000051', 'Ranitidina',               'Antiulceroso antagonista H2. 20 comprimidos.',                       14,'caixa',     60,  110,  60, 15, 0, 0, 1),
('Metoclopramida 10mg Comprimidos 20un',   '5900000000052', 'Metoclopramida',            'Antiemético e procinético. 20 comprimidos.',                         21,'caixa',     50,  90,   70, 15, 0, 0, 1),
('Domperidona 10mg Comprimidos 30un',      '5900000000053', 'Domperidona',              'Antiemético e procinético. 30 comprimidos.',                         21,'caixa',     80,  140,  50, 12, 0, 0, 1),
('Ondansetrom 4mg Comprimidos 10un',       '5900000000054', 'Ondansetrom',              'Antiemético potente para náuseas intensas. 10 comprimidos.',         21,'caixa',     200, 350,  30,  8, 1, 0, 1),
('Loperamida 2mg Cápsulas 12un',           '5900000000055', 'Loperamida',               'Antidiarreico. 12 cápsulas.',                                        14,'caixa',     60,  110,  70, 15, 0, 0, 1),
('Sais de Reidratação Oral Sachê',         '5900000000056', 'SRO',                      'Reidratação oral. Sachê para 1 litro.',                              14,'unidade',   10,  20,  300, 50, 0, 0, 1),
('Lactulose Xarope 200ml',                 '5900000000057', 'Lactulose',                'Laxante osmótico. Frasco 200ml.',                                    14,'frasco',    120, 210,  35, 10, 0, 0, 1),
('Bisacodil 5mg Comprimidos 20un',         '5900000000058', 'Bisacodil',                'Laxante estimulante. 20 comprimidos.',                               14,'caixa',     50,  90,   60, 15, 0, 0, 1),
('Hidróxido de Alumínio+Magnésio 200ml',   '5900000000059', 'Alumínio+Magnésio',        'Antiácido. Suspensão 200ml.',                                        14,'frasco',    80,  140,  60, 15, 0, 0, 1),
('Lansoprazol 30mg Cápsulas 14un',         '5900000000060', 'Lansoprazol',              'Inibidor da bomba de protões. 14 cápsulas.',                         14,'caixa',     140, 240,  40, 10, 1, 0, 1),
('Pantoprazol 40mg Comprimidos 14un',      '5900000000061', 'Pantoprazol',              'Inibidor da bomba de protões. 14 comprimidos.',                      14,'caixa',     130, 220,  45, 10, 1, 0, 1),
('Carvão Activado 250mg Cápsulas 20un',    '5900000000062', 'Carvão Activado',          'Adsorvente intestinal. 20 cápsulas.',                                14,'caixa',     60,  110,  50, 12, 0, 0, 1),
('Diosmectita Sachê 3g 30un',              '5900000000063', 'Diosmectita',              'Adsorvente para diarreia. 30 sachês.',                               14,'caixa',     120, 200,  40, 10, 0, 0, 1),
('Mebeverina 135mg Comprimidos 20un',      '5900000000064', 'Mebeverina',               'Antiespasmódico intestinal. 20 comprimidos.',                        14,'caixa',     140, 240,  30,  8, 1, 0, 1),

-- ── RESPIRATÓRIOS (cat 13) ────────────────────────────────────
('Salbutamol 100mcg Inalador 200 doses',   '5900000000065', 'Salbutamol',               'Broncodilatador de curta acção. Inalador 200 doses.',                13,'unidade',   180, 300,  40, 10, 1, 0, 1),
('Salbutamol 2mg/5ml Xarope 100ml',        '5900000000066', 'Salbutamol',               'Broncodilatador oral pediátrico. Frasco 100ml.',                     13,'frasco',    90,  160,  35, 10, 1, 0, 1),
('Beclometasona 250mcg Inalador',          '5900000000067', 'Beclometasona',            'Corticoide inalado para asma. Inalador 200 doses.',                  13,'unidade',   280, 450,  25,  6, 1, 0, 1),
('Brometo de Ipratrópio Inalador',         '5900000000068', 'Ipratrópio',               'Broncodilatador anticolinérgico. 200 doses.',                        13,'unidade',   250, 400,  20,  5, 1, 0, 1),
('Ambroxol 30mg/5ml Xarope 120ml',         '5900000000069', 'Ambroxol',                 'Mucolítico expectorante. Frasco 120ml.',                             13,'frasco',    80,  140,  60, 15, 0, 0, 1),
('Carbocisteína 250mg/5ml Xarope 100ml',   '5900000000070', 'Carbocisteína',            'Mucolítico. Frasco 100ml.',                                          13,'frasco',    90,  160,  40, 10, 0, 0, 1),
('Dextrometorfano+Guaifenesina Xarope',    '5900000000071', 'Dextrometorfano',          'Antitússico expectorante. Frasco 100ml.',                            12,'frasco',    80,  140,  50, 12, 0, 0, 1),
('Cetirizina 10mg Comprimidos 10un',       '5900000000072', 'Cetirizina',               'Anti-histamínico de 2ª geração. 10 comprimidos.',                    20,'caixa',     60,  110,  80, 20, 0, 0, 1),
('Loratadina 10mg Comprimidos 10un',       '5900000000073', 'Loratadina',               'Anti-histamínico sem sedação. 10 comprimidos.',                      20,'caixa',     50,  90,   90, 20, 0, 0, 1),
('Loratadina 1mg/ml Xarope 100ml',         '5900000000074', 'Loratadina',               'Anti-histamínico pediátrico. Frasco 100ml.',                         20,'frasco',    80,  140,  50, 12, 0, 0, 1),
('Prednisolona 5mg Comprimidos 20un',      '5900000000075', 'Prednisolona',             'Corticoide oral. 20 comprimidos.',                                   13,'caixa',     80,  150,  40, 10, 1, 0, 1),
('Dexametasona 4mg/ml Injectável 5un',     '5900000000076', 'Dexametasona',             'Corticoide injectável. 5 ampolas.',                                  13,'caixa',     120, 210,  25,  6, 1, 0, 1),

-- ── ANTIHIPERTENSORES / CARDIOVASCULARES (cat 12 e 16) ────────
('Amlodipina 5mg Comprimidos 30un',        '5900000000077', 'Amlodipina',               'Bloqueador canais cálcio anti-hipertensor. 30 comprimidos.',          16,'caixa',     100, 180,  60, 15, 1, 0, 1),
('Amlodipina 10mg Comprimidos 30un',       '5900000000078', 'Amlodipina',               'Bloqueador canais cálcio dose alta. 30 comprimidos.',                16,'caixa',     130, 220,  40, 10, 1, 0, 1),
('Enalapril 10mg Comprimidos 30un',        '5900000000079', 'Enalapril',                'IECA anti-hipertensor. 30 comprimidos.',                             16,'caixa',     80,  150,  55, 12, 1, 0, 1),
('Enalapril 20mg Comprimidos 30un',        '5900000000080', 'Enalapril',                'IECA anti-hipertensor dose alta. 30 comprimidos.',                   16,'caixa',     110, 190,  40, 10, 1, 0, 1),
('Losartan 50mg Comprimidos 30un',         '5900000000081', 'Losartan',                 'Antagonista ARA II anti-hipertensor. 30 comprimidos.',               16,'caixa',     120, 200,  50, 12, 1, 0, 1),
('Atenolol 50mg Comprimidos 30un',         '5900000000082', 'Atenolol',                 'Beta-bloqueador anti-hipertensor. 30 comprimidos.',                  16,'caixa',     70,  130,  60, 15, 1, 0, 1),
('Hidroclorotiazida 25mg Comprimidos 30un','5900000000083', 'Hidroclorotiazida',         'Diurético tiazídico. 30 comprimidos.',                              16,'caixa',     50,  90,   70, 15, 1, 0, 1),
('Furosemida 40mg Comprimidos 20un',       '5900000000084', 'Furosemida',               'Diurético de ansa. 20 comprimidos.',                                 16,'caixa',     40,  80,   80, 20, 1, 0, 1),
('Furosemida 20mg/2ml Injectável 5un',     '5900000000085', 'Furosemida',               'Diurético injectável. 5 ampolas.',                                   16,'caixa',     100, 180,  30,  8, 1, 0, 1),
('Nifedipina 10mg Cápsulas 30un',          '5900000000086', 'Nifedipina',               'Bloqueador canais cálcio. 30 cápsulas.',                             16,'caixa',     80,  150,  45, 10, 1, 0, 1),
('Metoprolol 50mg Comprimidos 30un',       '5900000000087', 'Metoprolol',               'Beta-bloqueador cardioselectivo. 30 comprimidos.',                   16,'caixa',     100, 180,  40, 10, 1, 0, 1),
('Espironolactona 25mg Comprimidos 30un',  '5900000000088', 'Espironolactona',           'Diurético poupador de potássio. 30 comprimidos.',                    16,'caixa',     90,  160,  35, 10, 1, 0, 1),
('Digoxina 0.25mg Comprimidos 30un',       '5900000000089', 'Digoxina',                 'Glicosídeo cardíaco para insuficiência. 30 comprimidos.',             12,'caixa',     80,  150,  25,  6, 1, 0, 1),
('Sinvastatina 20mg Comprimidos 30un',     '5900000000090', 'Sinvastatina',             'Estatina para redução do colesterol. 30 comprimidos.',               12,'caixa',     100, 180,  50, 12, 1, 0, 1),
('Aspirina 100mg Comprimidos 30un',        '5900000000091', 'Ácido Acetilsalicílico',   'Antiagregante plaquetário. 30 comprimidos.',                         12,'caixa',     40,  80,   90, 20, 0, 0, 1),

-- ── ANTIDIABÉTICOS (cat 17 da BD) ─────────────────────────────
('Metformina 500mg Comprimidos 60un',      '5900000000092', 'Metformina',               'Antidiabético oral biguanida. 60 comprimidos.',                      17,'caixa',     80,  150,  60, 15, 1, 0, 1),
('Metformina 850mg Comprimidos 60un',      '5900000000093', 'Metformina',               'Antidiabético oral dose alta. 60 comprimidos.',                      17,'caixa',     100, 180,  45, 12, 1, 0, 1),
('Glibenclamida 5mg Comprimidos 30un',     '5900000000094', 'Glibenclamida',            'Sulfonilureia para DM2. 30 comprimidos.',                            17,'caixa',     50,  90,   70, 15, 1, 0, 1),
('Glicazida 80mg Comprimidos 30un',        '5900000000095', 'Glicazida',                'Sulfonilureia de 2ª geração. 30 comprimidos.',                       17,'caixa',     100, 180,  40, 10, 1, 0, 1),
('Insulina NPH 100UI/ml 10ml',             '5900000000096', 'Insulina NPH Humana',      'Insulina de acção intermédia. Frasco 10ml.',                         17,'frasco',    400, 650,  30,  8, 1, 0, 1),
('Insulina Regular 100UI/ml 10ml',         '5900000000097', 'Insulina Regular Humana',  'Insulina de acção rápida. Frasco 10ml.',                             17,'frasco',    400, 650,  25,  6, 1, 0, 1),

-- ── SNC / NEUROLÓGICOS (cat 18) ───────────────────────────────
('Diazepam 5mg Comprimidos 20un',          '5900000000098', 'Diazepam',                 'Benzodiazepina ansiolítica e miorrelaxante.',                         18,'caixa',     60,  120,  30,  8, 1, 1, 1),
('Diazepam 10mg/2ml Injectável 5un',       '5900000000099', 'Diazepam',                 'Benzodiazepina injectável. 5 ampolas.',                              18,'caixa',     80,  150,  20,  5, 1, 1, 1),
('Amitriptilina 25mg Comprimidos 20un',    '5900000000100', 'Amitriptilina',            'Antidepressivo tricíclico. 20 comprimidos.',                         18,'caixa',     60,  110,  30,  8, 1, 0, 1),
('Carbamazepina 200mg Comprimidos 20un',   '5900000000101', 'Carbamazepina',            'Antiepiléptico e estabilizador do humor. 20 comprimidos.',           18,'caixa',     80,  150,  25,  6, 1, 0, 1),
('Fenitoína 100mg Comprimidos 30un',       '5900000000102', 'Fenitoína',                'Antiepiléptico. 30 comprimidos.',                                    18,'caixa',     70,  130,  25,  6, 1, 0, 1),
('Fenobarbital 100mg Comprimidos 20un',    '5900000000103', 'Fenobarbital',             'Antiepiléptico barbitúrico. 20 comprimidos.',                        18,'caixa',     50,  90,   30,  8, 1, 1, 1),
('Haloperidol 5mg Comprimidos 20un',       '5900000000104', 'Haloperidol',              'Antipsicótico. 20 comprimidos.',                                     18,'caixa',     80,  150,  20,  5, 1, 0, 1),
('Clorpromazina 100mg Comprimidos 20un',   '5900000000105', 'Clorpromazina',            'Antipsicótico clássico. 20 comprimidos.',                            18,'caixa',     70,  130,  20,  5, 1, 0, 1),

-- ── OFTALMOLÓGICOS (cat 19) ───────────────────────────────────
('Cloranfenicol 0.5% Colírio 5ml',         '5900000000106', 'Cloranfenicol',            'Antibiótico ocular. Frasco 5ml.',                                    19,'frasco',    60,  110,  60, 15, 1, 0, 1),
('Gentamicina 0.3% Colírio 5ml',           '5900000000107', 'Gentamicina',              'Antibiótico ocular aminoglicosídeo. Frasco 5ml.',                    19,'frasco',    80,  150,  45, 12, 1, 0, 1),
('Dexametasona 0.1% Colírio 5ml',          '5900000000108', 'Dexametasona',             'Corticoide ocular. Frasco 5ml.',                                     19,'frasco',    80,  150,  40, 10, 1, 0, 1),
('Lágrimas Artificiais 10ml',              '5900000000109', 'Carboximetilcelulose',     'Lubrificante ocular. Frasco 10ml.',                                  19,'frasco',    80,  150,  70, 15, 0, 0, 1),
('Timolol 0.5% Colírio 5ml',               '5900000000110', 'Timolol',                  'Beta-bloqueador para glaucoma. Frasco 5ml.',                         19,'frasco',    100, 180,  25,  6, 1, 0, 1),

-- ── DERMATOLÓGICOS (cat 20) ───────────────────────────────────
('Hidrocortisona 1% Creme 30g',            '5900000000111', 'Hidrocortisona',           'Corticoide tópico suave. Bisnaga 30g.',                              20,'unidade',   80,  150,  60, 15, 0, 0, 1),
('Betametasona 0.1% Creme 30g',            '5900000000112', 'Betametasona',             'Corticoide tópico potente. Bisnaga 30g.',                            20,'unidade',   90,  160,  50, 12, 1, 0, 1),
('Calamine Loção 100ml',                   '5900000000113', 'Calamine',                 'Antipruriginoso e adstringente. Frasco 100ml.',                      20,'frasco',    80,  140,  45, 10, 0, 0, 1),
('Peróxido de Benzoílo 5% Gel 30g',        '5900000000114', 'Peróxido de Benzoílo',     'Acne. Gel 30g.',                                                     20,'unidade',   100, 180,  40, 10, 0, 0, 1),
('Mupirocina 2% Pomada 15g',               '5900000000115', 'Mupirocina',               'Antibiótico tópico para infecções cutâneas. 15g.',                   20,'unidade',   140, 240,  35, 10, 1, 0, 1),
('Aciclovir 5% Creme 5g',                  '5900000000116', 'Aciclovir',                'Antiviral tópico para herpes labial. Bisnaga 5g.',                   20,'unidade',   90,  160,  50, 12, 0, 0, 1),
('Clobetasol 0.05% Creme 30g',             '5900000000117', 'Clobetasol',               'Corticoide tópico muito potente. Bisnaga 30g.',                      20,'unidade',   120, 210,  30,  8, 1, 0, 1),
('Azul de Metileno 1% 30ml',               '5900000000118', 'Azul de Metileno',         'Antisséptico cutâneo. Frasco 30ml.',                                 20,'frasco',    40,  80,   80, 20, 0, 0, 1),
('Permanganato de Potássio Comp 400mg',    '5900000000119', 'Permanganato de Potássio', 'Antisséptico e adstringente tópico.',                                20,'unidade',   10,  20,  150, 30, 0, 0, 1),

-- ── ANTIGRIPAIS (cat 22) ──────────────────────────────────────
('Paracetamol+Pseudoefedrina Comprimidos', '5900000000120', 'Paracetamol+Pseudoefedrina','Antigripal combinado. 12 comprimidos.',                             22,'caixa',     60,  110,  80, 20, 0, 0, 1),
('Ibuprofeno+Pseudoefedrina 200/30mg',     '5900000000121', 'Ibuprofeno+Pseudoefedrina','Antigripal com descongestionante. 12 comprimidos.',                  22,'caixa',     70,  130,  70, 15, 0, 0, 1),
('Vitamina C 1000mg Efervescente 10un',    '5900000000122', 'Ácido Ascórbico',          'Vitamina C efervescente. 10 comprimidos.',                           22,'caixa',     80,  140,  90, 20, 0, 0, 1),
('Mel+Limão+Eucalipto Xarope 150ml',       '5900000000123', 'Mel+Limão',                'Xarope natural para garganta. Frasco 150ml.',                        22,'frasco',    100, 180,  60, 15, 0, 0, 1),

-- ── VITAMINAS E SUPLEMENTOS (cat 23 e 24) ─────────────────────
('Vitamina C 500mg Comprimidos 30un',      '5900000000124', 'Ácido Ascórbico',          'Vitamina C oral. 30 comprimidos.',                                   23,'caixa',     70,  130,  90, 20, 0, 0, 1),
('Vitamina D3 1000UI Cápsulas 30un',       '5900000000125', 'Colecalciferol',            'Vitamina D3 para ossos. 30 cápsulas.',                               23,'caixa',     120, 210,  60, 15, 0, 0, 1),
('Vitamina B Complex Comprimidos 30un',    '5900000000126', 'Complexo B',               'Complexo vitamínico B. 30 comprimidos.',                             23,'caixa',     80,  150,  80, 20, 0, 0, 1),
('Vitamina E 400UI Cápsulas 30un',         '5900000000127', 'Tocoferol',                'Vitamina E antioxidante. 30 cápsulas.',                              23,'caixa',     100, 180,  50, 12, 0, 0, 1),
('Ácido Fólico 5mg Comprimidos 30un',      '5900000000128', 'Ácido Fólico',             'Vitamina B9 para gestantes. 30 comprimidos.',                        23,'caixa',     40,  80,  100, 25, 0, 0, 1),
('Ferro+Ácido Fólico Comprimidos 30un',    '5900000000129', 'Ferro+Ácido Fólico',       'Suplemento para anemia gestacional. 30 comprimidos.',                24,'caixa',     60,  110, 100, 25, 0, 0, 1),
('Sulfato Ferroso 200mg Comprimidos 30un', '5900000000130', 'Sulfato Ferroso',           'Suplemento de ferro para anemia. 30 comprimidos.',                  24,'caixa',     40,  80,  100, 25, 0, 0, 1),
('Gluconato de Cálcio 500mg 30un',         '5900000000131', 'Gluconato de Cálcio',      'Suplemento de cálcio. 30 comprimidos.',                              24,'caixa',     60,  110,  70, 15, 0, 0, 1),
('Cálcio+Vitamina D3 Comprimidos 30un',    '5900000000132', 'Cálcio+Vitamina D3',       'Suplemento para ossos. 30 comprimidos.',                             24,'caixa',     100, 180,  65, 15, 0, 0, 1),
('Zinco 20mg Comprimidos 30un',            '5900000000133', 'Zinco',                    'Suplemento mineral imunitário. 30 comprimidos.',                     24,'caixa',     60,  110,  80, 20, 0, 0, 1),
('Magnésio 300mg Comprimidos 30un',        '5900000000134', 'Magnésio',                 'Suplemento mineral muscular. 30 comprimidos.',                       24,'caixa',     80,  150,  60, 15, 0, 0, 1),
('Multivitamínico Adulto 30un',            '5900000000135', 'Multivitamínico',          'Suplemento multivitamínico completo adulto.',                        23,'caixa',     120, 210,  70, 15, 0, 0, 1),
('Multivitamínico Infantil Xarope 150ml',  '5900000000136', 'Multivitamínico',          'Suplemento pediátrico. Frasco 150ml.',                               23,'frasco',    150, 260,  50, 12, 0, 0, 1),
('Óleo de Fígado de Bacalhau 100ml',       '5900000000137', 'Óleo de Fígado de Bacalhau','Vitaminas A e D naturais. Frasco 100ml.',                           23,'frasco',    100, 180,  45, 10, 0, 0, 1),
('Vitamina A 200000UI Cápsula',            '5900000000138', 'Retinol',                  'Suplemento de vitamina A. 1 cápsula.',                               23,'unidade',   20,  40,  200, 50, 0, 0, 1),

-- ── CONTRACEPTIVOS (cat 25) ───────────────────────────────────
('Levonorgestrel+Etinilestradiol 21un',    '5900000000139', 'Levonorgestrel+Etinilestradiol','Contraceptivo oral combinado. 21 comprimidos.',                 25,'caixa',     60,  110,  80, 20, 1, 0, 1),
('Levonorgestrel 1.5mg Comprimido',        '5900000000140', 'Levonorgestrel',            'Contracepção de emergência. 1 comprimido.',                          25,'unidade',   100, 180,  60, 15, 0, 0, 1),
('Desogestrel 75mcg Comprimidos 28un',     '5900000000141', 'Desogestrel',              'Minipílula. 28 comprimidos.',                                        25,'caixa',     120, 210,  40, 10, 1, 0, 1),
('Medroxiprogesterona 150mg/ml Inj',       '5900000000142', 'Medroxiprogesterona',       'Contraceptivo injectável trimestral.',                               25,'unidade',   150, 260,  30,  8, 1, 0, 1),

-- ── HIGIENE E PRIMEIROS SOCORROS ──────────────────────────────
('Álcool Etílico 70% 1000ml',              '5900000000143', 'Álcool Etílico',           'Antisséptico. Frasco 1 litro.',                                       6,'frasco',    80,  140, 100, 25, 0, 0, 1),
('Água Oxigenada 3% 100ml',                '5900000000144', 'Peróxido de Hidrogénio',   'Antisséptico para feridas. Frasco 100ml.',                            6,'frasco',    30,  60,  150, 30, 0, 0, 1),
('Iodo Povidona 10% Solução 100ml',        '5900000000145', 'Povidona Iodada',          'Antisséptico de largo espectro. Frasco 100ml.',                       6,'frasco',    80,  150,  90, 20, 0, 0, 1),
('Clorexidina 0.12% Solução 250ml',        '5900000000146', 'Clorexidina',              'Antisséptico oral e cutâneo. Frasco 250ml.',                          6,'frasco',    90,  160,  70, 15, 0, 0, 1),
('Gazes Esterilizadas 10x10cm 10un',       '5900000000147', 'Gazes',                    'Penso esterilizado. 10 unidades.',                                    6,'caixa',     30,  60,  200, 40, 0, 0, 1),
('Ligadura Elástica 10cm x 4.5m',          '5900000000148', 'Ligadura',                 'Ligadura elástica para imobilização.',                                6,'unidade',   40,  80,  100, 25, 0, 0, 1),
('Adesivo Rápido Sortido 40un',            '5900000000149', 'Adesivo',                  'Pensos rápidos sortidos. 40 unidades.',                               6,'caixa',     50,  90,  150, 30, 0, 0, 1),
('Seringa 5ml c/ Agulha 100un',            '5900000000150', 'Seringa',                  'Seringa descartável 5ml. Caixa 100 unidades.',                        6,'caixa',     200, 350,  30,  8, 0, 0, 1),
('Luvas Latex Descartáveis M 100un',       '5900000000151', 'Luvas',                    'Luvas de látex descartáveis tamanho M.',                              6,'caixa',     250, 400,  20,  5, 0, 0, 1),
('Termómetro Digital',                     '5900000000152', 'Termómetro',               'Termómetro digital auricular.',                                       4,'unidade',   300, 500,  25,  6, 0, 0, 1),
('Tensiómetro Digital de Pulso',           '5900000000153', 'Tensiómetro',              'Aparelho de medição de tensão arterial digital.',                     4,'unidade',   1800,2800,  8,  3, 0, 0, 1),
('Glucómetro Kit Completo',                '5900000000154', 'Glucómetro',               'Kit completo com 10 tiras reactivas.',                                4,'unidade',   1200,1900, 10,  3, 0, 0, 1),
('Tiras Reactivas Glicose 50un',           '5900000000155', 'Tiras Reactivas',           'Tiras para medição de glicose. 50 unidades.',                        4,'caixa',     350, 550,  20,  5, 0, 0, 1),

-- ── DERMOCOSMÉTICOS (cat 2) ───────────────────────────────────
('Vaselina Pura 100g',                     '5900000000156', 'Vaselina',                 'Emoliente e protector cutâneo. 100g.',                                2,'unidade',   40,  80,  100, 25, 0, 0, 1),
('Creme Hidratante Nívea 200ml',           '5900000000157', 'Urea+Glicerina',           'Hidratante corporal para pele seca. 200ml.',                          2,'frasco',    150, 260,  50, 12, 0, 0, 1),
('Protetor Solar FPS50 50ml',              '5900000000158', 'Filtro Solar',             'Proteção solar facial FPS50. 50ml.',                                  2,'unidade',   200, 350,  35, 10, 0, 0, 1),
('Creme Anti-estrias 150ml',               '5900000000159', 'Centella Asiática',        'Prevenção e tratamento de estrias. 150ml.',                           2,'frasco',    180, 300,  30,  8, 0, 0, 1),
('Sabonete Líquido Íntimo 200ml',          '5900000000160', 'Ácido Láctico',            'Higiene íntima com pH balanceado. 200ml.',                            5,'frasco',    100, 180,  50, 12, 0, 0, 1),

-- ── HIGIENE BEBÉ (cat 5) ──────────────────────────────────────
('Creme para Assadura Bepanthen 30g',      '5900000000161', 'Dexpantenol',              'Creme preventivo de assaduras. Bisnaga 30g.',                         5,'unidade',   160, 270,  50, 12, 0, 0, 1),
('Talco Infantil Johnson 200g',            '5900000000162', 'Talco',                    'Talco absorvente para bebé. 200g.',                                   5,'unidade',   120, 200,  60, 15, 0, 0, 1),
('Soro Fisiológico Nasal Bebé 30ml',       '5900000000163', 'Cloreto de Sódio 0.9%',   'Lavagem nasal pediátrica. Frasco 30ml.',                              5,'frasco',    80,  140,  80, 20, 0, 0, 1),
('Chupeta Ortodôntica 0-6 meses',          '5900000000164', 'Chupeta',                  'Chupeta ortodôntica silicone. 0-6 meses.',                            5,'unidade',   80,  150,  40, 10, 0, 0, 1),

-- ── ANTIVIRAIS (cat 16) ───────────────────────────────────────
('Aciclovir 200mg Comprimidos 25un',       '5900000000165', 'Aciclovir',                'Antiviral para herpes simples. 25 comprimidos.',                      16,'caixa',     120, 210,  40, 10, 1, 0, 1),
('Aciclovir 400mg Comprimidos 21un',       '5900000000166', 'Aciclovir',                'Antiviral para herpes zoster. 21 comprimidos.',                       16,'caixa',     180, 300,  30,  8, 1, 0, 1),

-- ── OUTROS IMPORTANTES ────────────────────────────────────────
('Cloranfenicol 250mg Cápsulas 20un',      '5900000000167', 'Cloranfenicol',            'Antibiótico de reserva. 20 cápsulas.',                               7, 'caixa',     80,  150,  25,  6, 1, 0, 1),
('Heparina Sódica 5000UI/ml 5ml',          '5900000000168', 'Heparina',                 'Anticoagulante injectável. Frasco 5ml.',                              12,'frasco',    300, 500,  15,  5, 1, 0, 1),
('Varfarina 5mg Comprimidos 28un',         '5900000000169', 'Varfarina',                'Anticoagulante oral. 28 comprimidos.',                                12,'caixa',     100, 180,  20,  5, 1, 0, 1),
('Ácido Tranexâmico 500mg 10un',           '5900000000170', 'Ácido Tranexâmico',        'Antifibrinolítico para hemorragias. 10 comprimidos.',                 12,'caixa',     120, 200,  20,  5, 1, 0, 1),
('Ocitocina 10UI/ml Ampolas 5un',          '5900000000171', 'Ocitocina',                'Hormona uterotónica. 5 ampolas.',                                    17,'caixa',     200, 350,  15,  5, 1, 0, 1),
('Sulfato de Magnésio 50% Ampolas 5un',    '5900000000172', 'Sulfato de Magnésio',      'Anticonvulsivante e tocolítico. 5 ampolas.',                          18,'caixa',     150, 260,  15,  5, 1, 0, 1),
('Misoprostol 200mcg Comprimidos 4un',     '5900000000173', 'Misoprostol',              'Prostaglandina para HPP e indução. 4 comprimidos.',                   17,'caixa',     200, 350,  20,  5, 1, 0, 1),
('Ergometrina 0.2mg Ampola',               '5900000000174', 'Ergometrina',              'Uterotónico para hemorragia pós-parto.',                             17,'unidade',   80,  150,  20,  5, 1, 0, 1),
('Nevirapina 200mg Comprimidos 60un',      '5900000000175', 'Nevirapina',               'Antirretroviral INNTR. 60 comprimidos.',                              16,'caixa',     300, 500,  20,  5, 1, 0, 1),
('Efavirenz 600mg Comprimidos 30un',       '5900000000176', 'Efavirenz',                'Antirretroviral INNTR. 30 comprimidos.',                              16,'caixa',     350, 580,  20,  5, 1, 0, 1),
('Tenofovir+Lamivudina+Efavirenz 30un',    '5900000000177', 'TDF+3TC+EFV',              'Antirretroviral combinado 1ª linha. 30 comprimidos.',                 16,'caixa',     400, 650,  25,  6, 1, 0, 1),
('Cotrimoxazol 480mg Comprimidos 100un',   '5900000000178', 'Cotrimoxazol',             'Profilaxia para imunodeprimidos. 100 comprimidos.',                   7, 'caixa',     150, 260,  40, 10, 1, 0, 1),
('Fluoxetina 20mg Cápsulas 30un',          '5900000000179', 'Fluoxetina',               'Antidepressivo ISRS. 30 cápsulas.',                                  18,'caixa',     100, 180,  30,  8, 1, 0, 1),
('Sertralina 50mg Comprimidos 30un',       '5900000000180', 'Sertralina',               'Antidepressivo ISRS. 30 comprimidos.',                               18,'caixa',     120, 210,  25,  6, 1, 0, 1),
('Alprazolam 0.5mg Comprimidos 20un',      '5900000000181', 'Alprazolam',               'Benzodiazepina ansiolítica. 20 comprimidos.',                         18,'caixa',     80,  150,  20,  5, 1, 1, 1),
('Risperidona 2mg Comprimidos 20un',       '5900000000182', 'Risperidona',              'Antipsicótico atípico. 20 comprimidos.',                              18,'caixa',     120, 210,  20,  5, 1, 0, 1),
('Cloroquina 150mg+Primaquina Comp',       '5900000000183', 'Cloroquina+Primaquina',    'Antimalárico combinado. 14 comprimidos.',                             11,'caixa',     100, 180,  35,  8, 1, 0, 1),
('Coartem 80/480mg Comprimidos 6un',       '5900000000184', 'Artemeter+Lumefantrina',   'Antimalárico pediátrico 15-24kg. 6 comprimidos.',                     11,'caixa',     120, 200,  40, 10, 1, 0, 1),
('Artesunato 50mg Comprimidos 12un',       '5900000000185', 'Artesunato',               'Antimalárico monoderapia emergência. 12 comprimidos.',                11,'caixa',     100, 180,  30,  8, 1, 0, 1),
('Nitrofurantoína 100mg Cápsulas 30un',    '5900000000186', 'Nitrofurantoína',           'Antibacteriano urinário. 30 cápsulas.',                              7, 'caixa',     120, 210,  35,  8, 1, 0, 1),
('Norfloxacina 400mg Comprimidos 14un',    '5900000000187', 'Norfloxacina',             'Antibacteriano urinário. 14 comprimidos.',                            7, 'caixa',     100, 180,  40, 10, 1, 0, 1),
('Fenazopiridina 100mg Comprimidos 6un',   '5900000000188', 'Fenazopiridina',           'Analgésico urinário. 6 comprimidos.',                                14,'caixa',     60,  110,  50, 12, 0, 0, 1),
('Doxazosina 4mg Comprimidos 30un',        '5900000000189', 'Doxazosina',               'Alfa-bloqueador para HBP. 30 comprimidos.',                          16,'caixa',     150, 260,  25,  6, 1, 0, 1),
('Tamsulosina 0.4mg Cápsulas 30un',        '5900000000190', 'Tamsulosina',              'Alfa-bloqueador selectivo para HBP. 30 cápsulas.',                   16,'caixa',     200, 350,  20,  5, 1, 0, 1),
('Finasterida 5mg Comprimidos 30un',       '5900000000191', 'Finasterida',              'Para hiperplasia benigna prostática. 30 comprimidos.',               16,'caixa',     180, 300,  20,  5, 1, 0, 1),
('Colchicina 0.5mg Comprimidos 20un',      '5900000000192', 'Colchicina',               'Para gota aguda. 20 comprimidos.',                                    8,'caixa',     100, 180,  25,  6, 1, 0, 1),
('Alopurinol 300mg Comprimidos 30un',      '5900000000193', 'Alopurinol',               'Para gota crónica. 30 comprimidos.',                                  8,'caixa',     80,  150,  35,  8, 1, 0, 1),
('Levotiroxina 50mcg Comprimidos 30un',    '5900000000194', 'Levotiroxina',             'Hormona tiroideia. 30 comprimidos.',                                 17,'caixa',     80,  150,  40, 10, 1, 0, 1),
('Levotiroxina 100mcg Comprimidos 30un',   '5900000000195', 'Levotiroxina',             'Hormona tiroideia dose alta. 30 comprimidos.',                       17,'caixa',     100, 180,  30,  8, 1, 0, 1),
('Carvedilol 6.25mg Comprimidos 30un',     '5900000000196', 'Carvedilol',               'Beta-bloqueador não selectivo. 30 comprimidos.',                     16,'caixa',     120, 210,  30,  8, 1, 0, 1),
('Clonazepam 2mg Comprimidos 20un',        '5900000000197', 'Clonazepam',               'Benzodiazepina antiepiléptica. 20 comprimidos.',                      18,'caixa',     80,  150,  20,  5, 1, 1, 1),
('Lorazepam 1mg Comprimidos 20un',         '5900000000198', 'Lorazepam',                'Benzodiazepina ansiolítica. 20 comprimidos.',                         18,'caixa',     90,  160,  20,  5, 1, 1, 1),
('Teste de Gravidez HCG',                  '5900000000199', 'Teste HCG',                'Teste rápido de gravidez urinário.',                                  6,'unidade',   50,  90,  150, 30, 0, 0, 1),
('Teste Rápido Malária (RDT)',              '5900000000200', 'Teste RDT',                'Teste rápido para diagnóstico de malária.',                           6,'unidade',   60,  110, 200, 50, 0, 0, 1);

-- ================================================================
-- ADICIONAR LOTES A TODOS OS PRODUTOS
-- ================================================================
INSERT INTO lotes (produto_id, numero_lote, quantidade, validade, data_entrada)
SELECT
    id,
    CONCAT('LOT-', LPAD(id, 4, '0'), '-2025'),
    estoque_actual,
    DATE_ADD(CURDATE(), INTERVAL FLOOR(180 + RAND() * 540) DAY),
    CURDATE()
FROM produtos
WHERE estoque_actual > 0
  AND id NOT IN (SELECT DISTINCT produto_id FROM lotes);
