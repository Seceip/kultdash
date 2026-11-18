USE db_kultive;
SET SQL_SAFE_UPDATES = 0;

ALTER TABLE arquivos MODIFY arquivo MEDIUMBLOB NOT NULL;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = 'db_kultive' AND TABLE_NAME = 'tipagem' AND COLUMN_NAME = 'categoria');
SET @sql := IF(@existe = 0,
               'ALTER TABLE tipagem ADD COLUMN categoria VARCHAR(20) NOT NULL DEFAULT ''geral''',
               'SELECT 1');
PREPARE ajuste FROM @sql;
EXECUTE ajuste;
DEALLOCATE PREPARE ajuste;

UPDATE tipagem SET categoria = 'animal'     WHERE categoria = 'geral' AND nome IN ('Bovino','Suíno','Ave','Equino');
UPDATE tipagem SET categoria = 'maquina'    WHERE categoria = 'geral' AND nome IN ('Trator','Colheitadeira','Pulverizador','Implemento');
UPDATE tipagem SET categoria = 'area'       WHERE categoria = 'geral' AND nome IN ('Soja','Milho','Pastagem');
UPDATE tipagem SET categoria = 'arquivo'    WHERE categoria = 'geral' AND nome IN ('Documento','Imagem');
UPDATE tipagem SET categoria = 'ocorrencia' WHERE categoria = 'geral' AND nome IN ('Manutenção','Sanitário','Plantio');
UPDATE tipagem SET categoria = 'usuario'    WHERE categoria = 'geral' AND nome = 'Administrador';

INSERT INTO tipagem (nome, definicao, categoria)
SELECT v.nome, v.definicao, v.categoria FROM (
    SELECT 'Bovino' AS nome, 'Animal: gado bovino' AS definicao, 'animal' AS categoria
    UNION ALL SELECT 'Suíno', 'Animal: suínos', 'animal'
    UNION ALL SELECT 'Ave', 'Animal: aves', 'animal'
    UNION ALL SELECT 'Equino', 'Animal: cavalos e similares', 'animal'
    UNION ALL SELECT 'Trator', 'Máquina: trator agrícola', 'maquina'
    UNION ALL SELECT 'Colheitadeira', 'Máquina: colheitadeira', 'maquina'
    UNION ALL SELECT 'Pulverizador', 'Equipamento: pulverizador', 'maquina'
    UNION ALL SELECT 'Implemento', 'Equipamento: implemento agrícola', 'maquina'
    UNION ALL SELECT 'Soja', 'Área de cultivo: soja', 'area'
    UNION ALL SELECT 'Milho', 'Área de cultivo: milho', 'area'
    UNION ALL SELECT 'Pastagem', 'Área de cultivo: pastagem', 'area'
    UNION ALL SELECT 'Manutenção', 'Ocorrência: manutenção', 'ocorrencia'
    UNION ALL SELECT 'Sanitário', 'Ocorrência: manejo sanitário', 'ocorrencia'
    UNION ALL SELECT 'Plantio', 'Ocorrência: plantio ou colheita', 'ocorrencia'
    UNION ALL SELECT 'Documento', 'Arquivo: documento', 'arquivo'
    UNION ALL SELECT 'Imagem', 'Arquivo: foto ou imagem', 'arquivo'
    UNION ALL SELECT 'Administrador', 'Usuário: administrador do sistema', 'usuario'
) AS v
WHERE NOT EXISTS (SELECT 1 FROM tipagem t WHERE t.nome = v.nome AND t.categoria = v.categoria);

INSERT INTO usuario (nome, usuario, senha, email, tipo_id)
SELECT 'Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kultdash.local',
       (SELECT tipo_id FROM tipagem WHERE nome = 'Administrador' AND categoria = 'usuario' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE usuario = 'admin');

SET SQL_SAFE_UPDATES = 1;
