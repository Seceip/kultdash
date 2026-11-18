USE db_kultive;

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
    UNION ALL SELECT 'Soja', 'Área de cultivo: soja'
    UNION ALL SELECT 'Milho', 'Área de cultivo: milho'
    UNION ALL SELECT 'Pastagem', 'Área de cultivo: pastagem'
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
