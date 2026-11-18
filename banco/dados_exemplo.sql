USE db_kultive;

SET @u := (SELECT usuario_id FROM usuario WHERE usuario = 'admin' LIMIT 1);
SET @t_bovino  := (SELECT tipo_id FROM tipagem WHERE nome = 'Bovino' LIMIT 1);
SET @t_suino   := (SELECT tipo_id FROM tipagem WHERE nome = 'Suíno' LIMIT 1);
SET @t_ave     := (SELECT tipo_id FROM tipagem WHERE nome = 'Ave' LIMIT 1);
SET @t_equino  := (SELECT tipo_id FROM tipagem WHERE nome = 'Equino' LIMIT 1);
SET @t_trator  := (SELECT tipo_id FROM tipagem WHERE nome = 'Trator' LIMIT 1);
SET @t_colh    := (SELECT tipo_id FROM tipagem WHERE nome = 'Colheitadeira' LIMIT 1);
SET @t_pulv    := (SELECT tipo_id FROM tipagem WHERE nome = 'Pulverizador' LIMIT 1);
SET @t_impl    := (SELECT tipo_id FROM tipagem WHERE nome = 'Implemento' LIMIT 1);
SET @t_soja    := (SELECT tipo_id FROM tipagem WHERE nome = 'Soja' LIMIT 1);
SET @t_milho   := (SELECT tipo_id FROM tipagem WHERE nome = 'Milho' LIMIT 1);
SET @t_pasto   := (SELECT tipo_id FROM tipagem WHERE nome = 'Pastagem' LIMIT 1);
SET @t_manut   := (SELECT tipo_id FROM tipagem WHERE nome = 'Manutenção' LIMIT 1);
SET @t_sanit   := (SELECT tipo_id FROM tipagem WHERE nome = 'Sanitário' LIMIT 1);
SET @t_plantio := (SELECT tipo_id FROM tipagem WHERE nome = 'Plantio' LIMIT 1);

INSERT INTO propriedade (nome, situacao_atual, tamanho) VALUES ('Fazenda Santa Clara', 'Ativa', 2350.00);
SET @p1 := LAST_INSERT_ID();
INSERT INTO usuario_propriedade (usuario_id, prop_id) VALUES (@u, @p1);

INSERT INTO area_cultivo (prop_id, tipo_id, tamanho, situacao_atual, nome, conteudo) VALUES
(@p1, @t_soja,  850.00, 'Plantada',   'Talhão 1 - Soja',     'Soja safra 2026/27'),
(@p1, @t_soja,  620.50, 'Plantada',   'Talhão 2 - Soja',     'Soja safra 2026/27'),
(@p1, @t_milho, 380.00, 'Em preparo', 'Talhão 3 - Milho',    'Milho segunda safra'),
(@p1, @t_pasto, 400.00, 'Em uso',     'Pasto Norte',         'Braquiária'),
(@p1, @t_pasto, 99.50,  'Em descanso','Pasto Sul',           'Braquiária');

INSERT INTO animais (prop_id, tipo_id, data_compra, data_nasci, situacao_atual) VALUES
(@p1, @t_bovino, '2024-03-10', '2022-05-14 00:00:00', 'Ativo'),
(@p1, @t_bovino, '2024-03-10', '2022-07-02 00:00:00', 'Ativo'),
(@p1, @t_bovino, '2024-08-21', '2023-01-19 00:00:00', 'Em tratamento'),
(@p1, @t_bovino, NULL,         '2024-02-11 00:00:00', 'Ativo'),
(@p1, @t_bovino, NULL,         '2024-04-03 00:00:00', 'Ativo'),
(@p1, @t_bovino, '2025-01-15', '2023-09-30 00:00:00', 'Vendido'),
(@p1, @t_suino,  '2025-06-05', '2025-02-20 00:00:00', 'Ativo'),
(@p1, @t_suino,  '2025-06-05', '2025-02-20 00:00:00', 'Ativo'),
(@p1, @t_ave,    '2026-01-12', '2025-11-01 00:00:00', 'Ativo'),
(@p1, @t_ave,    '2026-01-12', '2025-11-01 00:00:00', 'Ativo'),
(@p1, @t_ave,    '2026-01-12', '2025-11-01 00:00:00', 'Ativo'),
(@p1, @t_equino, '2023-05-30', '2020-10-08 00:00:00', 'Ativo');

INSERT INTO maquinas_equipamentos (prop_id, nome, tipo_id, data_adquirido, situacao_atual) VALUES
(@p1, 'Trator John Deere 6110',   @t_trator, '2021-04-12 00:00:00', 'Operacional'),
(@p1, 'Trator Massey 4297',       @t_trator, '2018-09-03 00:00:00', 'Em manutenção'),
(@p1, 'Colheitadeira New Holland',@t_colh,   '2022-02-18 00:00:00', 'Operacional'),
(@p1, 'Pulverizador Jacto 600',   @t_pulv,   '2023-07-25 00:00:00', 'Operacional'),
(@p1, 'Plantadeira 16 linhas',    @t_impl,   '2020-08-14 00:00:00', 'Parada'),
(@p1, 'Grade aradora',            @t_impl,   '2019-03-30 00:00:00', 'Operacional');

SET @an1  := (SELECT MIN(animal_id) FROM animais WHERE prop_id = @p1 AND tipo_id = @t_bovino);
SET @an3  := (SELECT animal_id FROM animais WHERE prop_id = @p1 AND situacao_atual = 'Em tratamento' LIMIT 1);
SET @maq2 := (SELECT maquina_equip_id FROM maquinas_equipamentos WHERE prop_id = @p1 AND nome = 'Trator Massey 4297' LIMIT 1);
SET @maq3 := (SELECT maquina_equip_id FROM maquinas_equipamentos WHERE prop_id = @p1 AND nome = 'Colheitadeira New Holland' LIMIT 1);
SET @maq4 := (SELECT maquina_equip_id FROM maquinas_equipamentos WHERE prop_id = @p1 AND nome = 'Pulverizador Jacto 600' LIMIT 1);
SET @ar1  := (SELECT area_id FROM area_cultivo WHERE prop_id = @p1 AND nome = 'Talhão 1 - Soja' LIMIT 1);
SET @ar3  := (SELECT area_id FROM area_cultivo WHERE prop_id = @p1 AND nome = 'Talhão 3 - Milho' LIMIT 1);

INSERT INTO prontuario (prop_id, usuario_id, animal_id, maquina_equip_id, area_id, descricao, data_ocorrencia, nome, situacao_atual, tipo_id) VALUES
(@p1, @u, NULL, NULL,  @ar1, 'Plantio do talhão 1 concluído.',                         DATE_SUB(NOW(), INTERVAL 5 MONTH), 'Plantio da soja - Talhão 1',        'Concluída', @t_plantio),
(@p1, @u, NULL, @maq3, NULL, 'Revisão geral antes da safra.',                          DATE_SUB(NOW(), INTERVAL 5 MONTH), 'Revisão da colheitadeira',          'Concluída', @t_manut),
(@p1, @u, @an1, NULL,  NULL, 'Vacinação do rebanho contra febre aftosa.',              DATE_SUB(NOW(), INTERVAL 4 MONTH), 'Vacinação do rebanho',              'Concluída', @t_sanit),
(@p1, @u, NULL, @maq4, NULL, 'Troca de bicos e calibração.',                           DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Calibração do pulverizador',        'Concluída', @t_manut),
(@p1, @u, NULL, @maq2, NULL, 'Vazamento no sistema hidráulico. Aguardando peça.',      DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Falha hidráulica do trator Massey', 'Pendente',  @t_manut),
(@p1, @u, NULL, NULL,  @ar3, 'Preparo do solo e correção de acidez.',                  DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Preparo do solo - Talhão 3',        'Concluída', @t_plantio),
(@p1, @u, @an3, NULL,  NULL, 'Animal com sinais de verminose. Tratamento iniciado.',   DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Tratamento de bovino',              'Em andamento', @t_sanit),
(@p1, @u, NULL, NULL,  NULL, 'Vistoria geral das cercas e porteiras.',                 DATE_SUB(NOW(), INTERVAL 20 DAY),  'Vistoria das cercas',               'Concluída', NULL),
(@p1, @u, NULL, @maq2, NULL, 'Peça recebida; reparo agendado.',                        DATE_SUB(NOW(), INTERVAL 6 DAY),   'Reparo agendado do trator Massey',  'Em andamento', @t_manut),
(@p1, @u, NULL, NULL,  @ar1, 'Aplicação de fungicida no talhão 1.',                    DATE_SUB(NOW(), INTERVAL 2 DAY),   'Aplicação de fungicida',            'Concluída', @t_plantio);

INSERT INTO propriedade (nome, situacao_atual, tamanho) VALUES ('Sítio Boa Vista', 'Ativa', 48.50);
SET @p2 := LAST_INSERT_ID();
INSERT INTO usuario_propriedade (usuario_id, prop_id) VALUES (@u, @p2);

INSERT INTO area_cultivo (prop_id, tipo_id, tamanho, situacao_atual, nome, conteudo) VALUES
(@p2, @t_milho, 20.00, 'Plantada', 'Roça do Milho', 'Milho verde'),
(@p2, @t_pasto, 25.00, 'Em uso',   'Pasto da Frente', 'Capim Mombaça');

INSERT INTO animais (prop_id, tipo_id, data_compra, data_nasci, situacao_atual) VALUES
(@p2, @t_bovino, '2025-02-10', '2023-06-18 00:00:00', 'Ativo'),
(@p2, @t_bovino, '2025-02-10', '2023-08-05 00:00:00', 'Ativo'),
(@p2, @t_equino, NULL,         '2021-04-22 00:00:00', 'Ativo');

INSERT INTO maquinas_equipamentos (prop_id, nome, tipo_id, data_adquirido, situacao_atual) VALUES
(@p2, 'Microtrator Yanmar', @t_trator, '2022-10-10 00:00:00', 'Operacional'),
(@p2, 'Pulverizador costal', @t_pulv,  '2024-01-20 00:00:00', 'Operacional');

INSERT INTO prontuario (prop_id, usuario_id, animal_id, maquina_equip_id, area_id, descricao, data_ocorrencia, nome, situacao_atual, tipo_id) VALUES
(@p2, @u, NULL, NULL, NULL, 'Limpeza do pasto da frente.', DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Roçada do pasto', 'Concluída', NULL),
(@p2, @u, NULL, NULL, NULL, 'Vermifugação dos bovinos.',   DATE_SUB(NOW(), INTERVAL 3 DAY),   'Vermifugação',    'Concluída', @t_sanit);

