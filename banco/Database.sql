CREATE DATABASE IF NOT EXISTS db_kultive CHARACTER SET utf8mb4;
USE db_kultive;
CREATE TABLE IF NOT EXISTS tipagem
(
    tipo_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    definicao VARCHAR(200) NOT NULL
);
CREATE TABLE IF NOT EXISTS propriedade 
(
    prop_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    situacao_atual VARCHAR(30) NOT NULL,
    tamanho DECIMAL(10,2) NOT NULL
);
CREATE TABLE IF NOT EXISTS usuario 
(
    usuario_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100),
    usuario VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    tipo_id INT,
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS usuario_propriedade
(
    usuario_id INT NOT NULL,
    prop_id INT NOT NULL,
    PRIMARY KEY (usuario_id, prop_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id)
);
CREATE TABLE IF NOT EXISTS animais 
(
    animal_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    tipo_id INT NOT NULL,
    data_compra DATE,
    data_nasci DATETIME,
    situacao_atual VARCHAR(30),
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS maquinas_equipamentos 
(
    maquina_equip_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    data_adquirido DATETIME,
    nome VARCHAR(100) NOT NULL,
    situacao_atual VARCHAR(30) NOT NULL,
    tipo_id INT,
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS area_cultivo 
(
    area_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    tipo_id INT NOT NULL,
    tamanho DECIMAL(10,2) NOT NULL,
    situacao_atual VARCHAR(30),
    nome VARCHAR(100) NOT NULL,
    conteudo VARCHAR(100),
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);
CREATE TABLE IF NOT EXISTS prontuario 
(
    prontuario_id INT PRIMARY KEY AUTO_INCREMENT,
    prop_id INT NOT NULL,
    usuario_id INT NOT NULL,
    animal_id INT,
    maquina_equip_id INT,
    area_id INT,
    descricao VARCHAR(500),
    data_ocorrencia DATETIME,
    nome VARCHAR(100) NOT NULL,
    caminho VARCHAR(255),
    situacao_atual VARCHAR(30),
    tipo_id INT,
    FOREIGN KEY (prop_id) REFERENCES propriedade(prop_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id),
    FOREIGN KEY (animal_id) REFERENCES animais(animal_id),
    FOREIGN KEY (maquina_equip_id) REFERENCES maquinas_equipamentos(maquina_equip_id),
    FOREIGN KEY (area_id) REFERENCES area_cultivo(area_id)
);

CREATE TABLE IF NOT EXISTS arquivos 
(
    arquivo_id INT PRIMARY KEY AUTO_INCREMENT,
    prontuario_id INT NOT NULL,
    tipo_id INT,
    nome VARCHAR(100) NOT NULL,
    arquivo MEDIUMBLOB NOT NULL,
    data_upload DATE NOT NULL,
    FOREIGN KEY (prontuario_id) REFERENCES prontuario(prontuario_id),
    FOREIGN KEY (tipo_id) REFERENCES tipagem(tipo_id)
);

ALTER TABLE arquivos MODIFY arquivo MEDIUMBLOB NOT NULL;

INSERT INTO tipagem (nome, definicao)
SELECT v.nome, v.definicao FROM (
    SELECT 'Bovino' AS nome, 'Animal: gado bovino' AS definicao
    UNION ALL SELECT 'Suíno', 'Animal: suínos'
    UNION ALL SELECT 'Ave', 'Animal: aves'
    UNION ALL SELECT 'Equino', 'Animal: cavalos e similares'
    UNION ALL SELECT 'Trator', 'Máquina: trator agrícola'
    UNION ALL SELECT 'Colheitadeira', 'Máquina: colheitadeira'
    UNION ALL SELECT 'Pulverizador', 'Equipamento: pulverizador'
    UNION ALL SELECT 'Implemento', 'Equipamento: implemento agrícola'
    UNION ALL SELECT 'Sem Talhão', 'Área de cultivo: sem divisão por talhão'
    UNION ALL SELECT 'Talhão', 'Área de cultivo: dividio por talhão'
    UNION ALL SELECT 'Manutenção', 'Ocorrência: manutenção'
    UNION ALL SELECT 'Sanitário', 'Ocorrência: manejo sanitário'
    UNION ALL SELECT 'Plantio', 'Ocorrência: plantio ou colheita'
    UNION ALL SELECT 'Documento', 'Arquivo: documento'
    UNION ALL SELECT 'Imagem', 'Arquivo: foto ou imagem'
    UNION ALL SELECT 'Administrador', 'Usuário: administrador do sistema'
) AS v
WHERE NOT EXISTS (SELECT 1 FROM tipagem t WHERE t.nome = v.nome);

INSERT INTO usuario (nome, usuario, senha, email, tipo_id)
SELECT 'Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kultdash.local',
       (SELECT tipo_id FROM tipagem WHERE nome = 'Administrador' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE usuario = 'admin');

INSERT INTO tipagem (nome, definicao)
SELECT v.nome, v.definicao
FROM (
    SELECT 'Gerente' AS nome, 'Usuário: gerente da propriedade' AS definicao
    UNION ALL SELECT 'Veterinário', 'Usuário: responsável pela saúde animal'
    UNION ALL SELECT 'Agrônomo', 'Usuário: responsável pela agricultura'
    UNION ALL SELECT 'Operador', 'Usuário: operador de máquinas'
) v
WHERE NOT EXISTS (
    SELECT 1 FROM tipagem t WHERE t.nome = v.nome
);

INSERT INTO propriedade
(prop_id, nome, situacao_atual, tamanho)
VALUES
(1, 'Fazenda Santa Helena', 'Ativa', 850.00),
(2, 'Fazenda Boa Esperança', 'Ativa', 420.50),
(3, 'Sítio Recanto Verde', 'Em expansão', 125.00);

INSERT INTO usuario
(usuario_id, nome, usuario, senha, email, tipo_id)
VALUES
(2, 'Carlos Mendes', 'carlos.gerente',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4KoEa3Ro9llC/.at2.uheWG/igi',
 'carlos@kultdash.local',
 (SELECT tipo_id FROM tipagem WHERE nome = 'Gerente' LIMIT 1)),

(3, 'Mariana Oliveira', 'mariana.vet',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4KoEa3Ro9llC/.at2.uheWG/igi',
 'mariana@kultdash.local',
 (SELECT tipo_id FROM tipagem WHERE nome = 'Veterinário' LIMIT 1)),

(4, 'Roberto Santos', 'roberto.agro',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4KoEa3Ro9llC/.at2.uheWG/igi',
 'roberto@kultdash.local',
 (SELECT tipo_id FROM tipagem WHERE nome = 'Agrônomo' LIMIT 1)),

(5, 'João Ferreira', 'joao.operador',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4KoEa3Ro9llC/.at2.uheWG/igi',
 'joao@kultdash.local',
 (SELECT tipo_id FROM tipagem WHERE nome = 'Operador' LIMIT 1));

INSERT INTO usuario_propriedade (usuario_id, prop_id)
VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(4, 3),
(5, 1),
(5, 2);

INSERT INTO animais
(animal_id, prop_id, tipo_id, data_compra, data_nasci, situacao_atual)
VALUES
(1, 1, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2023-04-10', '2022-01-15 08:00:00', 'Ativo'),
(2, 1, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2023-05-12', '2022-03-20 09:00:00', 'Ativo'),
(3, 1, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2024-01-18', '2023-02-10 07:30:00', 'Ativo'),
(4, 1, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2024-02-20', '2023-05-11 08:30:00', 'Em tratamento'),
(5, 1, (SELECT tipo_id FROM tipagem WHERE nome='Equino' LIMIT 1), '2022-08-15', '2020-06-10 10:00:00', 'Ativo'),
(6, 2, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2023-06-10', '2022-04-12 08:00:00', 'Ativo'),
(7, 2, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2023-09-05', '2022-07-15 08:00:00', 'Ativo'),
(8, 2, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2024-03-12', '2023-01-18 07:00:00', 'Ativo'),
(9, 2, (SELECT tipo_id FROM tipagem WHERE nome='Suíno' LIMIT 1), '2024-05-20', '2024-02-10 06:00:00', 'Ativo'),
(10, 2, (SELECT tipo_id FROM tipagem WHERE nome='Ave' LIMIT 1), '2024-06-10', '2024-05-01 06:00:00', 'Ativo'),
(11, 3, (SELECT tipo_id FROM tipagem WHERE nome='Bovino' LIMIT 1), '2024-02-15', '2023-03-10 08:00:00', 'Ativo'),
(12, 3, (SELECT tipo_id FROM tipagem WHERE nome='Equino' LIMIT 1), '2023-11-10', '2020-08-15 09:00:00', 'Ativo');

INSERT INTO maquinas_equipamentos
(maquina_equip_id, prop_id, data_adquirido, nome, situacao_atual, tipo_id)
VALUES
(1, 1, '2021-03-15 10:00:00', 'Trator John Deere 6110J', 'Operacional',
 (SELECT tipo_id FROM tipagem WHERE nome='Trator' LIMIT 1)),

(2, 1, '2022-08-20 10:00:00', 'Colheitadeira Case IH 2566', 'Operacional',
 (SELECT tipo_id FROM tipagem WHERE nome='Colheitadeira' LIMIT 1)),

(3, 1, '2023-01-10 09:00:00', 'Pulverizador Jacto Uniport', 'Manutenção',
 (SELECT tipo_id FROM tipagem WHERE nome='Pulverizador' LIMIT 1)),

(4, 1, '2020-06-12 08:00:00', 'Grade Aradora 18 discos', 'Operacional',
 (SELECT tipo_id FROM tipagem WHERE nome='Implemento' LIMIT 1)),

(5, 2, '2022-05-10 10:00:00', 'Trator Massey Ferguson 4275', 'Operacional',
 (SELECT tipo_id FROM tipagem WHERE nome='Trator' LIMIT 1)),

(6, 2, '2023-04-18 11:00:00', 'Pulverizador Agrícola 600L', 'Operacional',
 (SELECT tipo_id FROM tipagem WHERE nome='Pulverizador' LIMIT 1)),

(7, 3, '2024-02-10 09:00:00', 'Trator New Holland TT4', 'Operacional',
 (SELECT tipo_id FROM tipagem WHERE nome='Trator' LIMIT 1)),

(8, 3, '2024-04-15 10:00:00', 'Plantadeira 7 linhas', 'Em manutenção',
 (SELECT tipo_id FROM tipagem WHERE nome='Implemento' LIMIT 1));

INSERT INTO area_cultivo
(area_id, prop_id, tipo_id, tamanho, situacao_atual, nome, conteudo)
VALUES
(1, 1, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 120.00, 'Em produção', 'Talhão 01', 'Soja'),
(2, 1, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 100.00, 'Em produção', 'Talhão 02', 'Milho'),
(3, 1, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 85.00, 'Preparação', 'Talhão 03', 'Soja'),
(4, 1, (SELECT tipo_id FROM tipagem WHERE nome='Sem Talhão' LIMIT 1), 200.00, 'Pastagem', 'Área de Pastagem', 'Pasto para bovinos'),
(5, 2, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 80.00, 'Em produção', 'Talhão 01', 'Milho'),
(6, 2, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 65.00, 'Em produção', 'Talhão 02', 'Soja'),
(7, 2, (SELECT tipo_id FROM tipagem WHERE nome='Sem Talhão' LIMIT 1), 150.00, 'Pastagem', 'Área de Pastagem', 'Pasto para bovinos'),
(8, 3, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 35.00, 'Em produção', 'Talhão 01', 'Café'),
(9, 3, (SELECT tipo_id FROM tipagem WHERE nome='Talhão' LIMIT 1), 20.00, 'Preparação', 'Talhão 02', 'Milho');

INSERT INTO prontuario
(prontuario_id, prop_id, usuario_id, animal_id, maquina_equip_id,
 area_id, descricao, data_ocorrencia, nome, caminho, situacao_atual, tipo_id)
VALUES

(1, 1, 3, 1, NULL, NULL,
 'Aplicação de vacina contra febre aftosa no lote de bovinos.',
 '2026-01-10 09:00:00', 'Vacinação do rebanho', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Sanitário' LIMIT 1)),

(2, 1, 3, 4, NULL, NULL,
 'Animal apresentou sintomas de infecção. Iniciado acompanhamento veterinário.',
 '2026-02-05 10:30:00', 'Tratamento bovino 04', NULL, 'Em andamento',
 (SELECT tipo_id FROM tipagem WHERE nome='Sanitário' LIMIT 1)),

(3, 1, 5, NULL, 1, NULL,
 'Troca de óleo e filtros do trator John Deere.',
 '2026-02-12 08:00:00', 'Revisão preventiva do trator', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Manutenção' LIMIT 1)),

(4, 1, 5, NULL, 3, NULL,
 'Pulverizador apresentou falha no sistema de pressão.',
 '2026-03-02 14:00:00', 'Reparo do pulverizador', NULL, 'Em andamento',
 (SELECT tipo_id FROM tipagem WHERE nome='Manutenção' LIMIT 1)),

(5, 1, 4, NULL, NULL, 1,
 'Plantio de soja realizado no Talhão 01.',
 '2026-03-05 07:00:00', 'Plantio de soja - Talhão 01', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Plantio' LIMIT 1)),

(6, 1, 4, NULL, NULL, 2,
 'Adubação de cobertura realizada na área de milho.',
 '2026-03-15 08:00:00', 'Adubação de milho', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Plantio' LIMIT 1)),

(7, 1, 2, 2, NULL, NULL,
 'Pesagem e avaliação periódica do desenvolvimento do animal.',
 '2026-04-02 09:30:00', 'Pesagem do rebanho', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Sanitário' LIMIT 1)),

(8, 1, 5, NULL, 2, NULL,
 'Inspeção geral da colheitadeira antes do início da safra.',
 '2026-04-10 10:00:00', 'Inspeção da colheitadeira', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Manutenção' LIMIT 1)),

(9, 2, 3, 6, NULL, NULL,
 'Realizada vermifugação preventiva no rebanho.',
 '2026-01-20 08:30:00', 'Vermifugação bovina', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Sanitário' LIMIT 1)),

(10, 2, 4, NULL, NULL, 5,
 'Plantio de milho realizado no Talhão 01.',
 '2026-02-15 07:00:00', 'Plantio de milho', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Plantio' LIMIT 1)),

(11, 2, 5, NULL, 5, NULL,
 'Manutenção preventiva e troca de filtros.',
 '2026-03-08 13:00:00', 'Revisão do trator Massey', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Manutenção' LIMIT 1)),

(12, 2, 2, 7, NULL, NULL,
 'Avaliação de saúde e condição corporal dos bovinos.',
 '2026-04-12 09:00:00', 'Avaliação sanitária', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Sanitário' LIMIT 1)),

(13, 3, 4, NULL, NULL, 8,
 'Adubação do cafezal e controle de plantas invasoras.',
 '2026-03-18 08:00:00', 'Manejo do cafezal', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Plantio' LIMIT 1)),

(14, 3, 5, NULL, 8, NULL,
 'Plantadeira apresentou desgaste em componentes. Aguardando peças.',
 '2026-04-20 14:30:00', 'Manutenção da plantadeira', NULL, 'Pendente',
 (SELECT tipo_id FROM tipagem WHERE nome='Manutenção' LIMIT 1)),

(15, 3, 2, 11, NULL, NULL,
 'Avaliação veterinária de rotina do rebanho.',
 '2026-05-05 10:00:00', 'Consulta veterinária', NULL, 'Concluído',
 (SELECT tipo_id FROM tipagem WHERE nome='Sanitário' LIMIT 1));

UPDATE usuario
SET senha = (
    SELECT senha
    FROM (
        SELECT senha
        FROM usuario
        WHERE usuario = 'admin'
    ) AS admin_hash
)
WHERE usuario IN (
    'carlos.gerente',
    'mariana.vet',
    'roberto.agro',
    'joao.operador'
);