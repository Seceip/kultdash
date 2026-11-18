<?php
require_once __DIR__ . '/../../includes/api.php';
$prop = exigirPropriedade();

switch ($acao) {
    case 'listar':
        sucesso(['dados' => consultar($conn, "SELECT p.prontuario_id AS id, p.nome, p.data_ocorrencia, p.situacao_atual,
                COALESCE(u.nome, u.usuario) AS responsavel,
                COALESCE(CONCAT('Animal #', p.animal_id), CONCAT('Máquina: ', m.nome), CONCAT('Área: ', ac.nome)) AS vinculo
            FROM prontuario p
            JOIN usuario u ON u.usuario_id = p.usuario_id
            LEFT JOIN maquinas_equipamentos m ON m.maquina_equip_id = p.maquina_equip_id
            LEFT JOIN area_cultivo ac ON ac.area_id = p.area_id
            WHERE p.prop_id = ? ORDER BY p.data_ocorrencia DESC, p.prontuario_id DESC", 'i', [$prop])]);

    case 'obter':
        sucesso(['dados' => obterRegistro($conn, 'prontuario', 'prontuario_id', campoInteiro('id', 'Ocorrência', true), $prop)]);

    case 'salvar':
        $id = campoInteiro('id', 'Ocorrência');
        $nome = campoTexto('nome', 'Nome', 100, true);
        $descricao = campoTexto('descricao', 'Descrição', 500);
        $data = campoDataHora('data_ocorrencia', 'Data da ocorrência', true);
        $situacao = campoTexto('situacao_atual', 'Situação atual', 30);
        $tipo = campoInteiro('tipo_id', 'Tipo');
        exigirTipo($conn, $tipo);
        $animal = campoInteiro('animal_id', 'Animal');
        $maquina = campoInteiro('maquina_equip_id', 'Máquina');
        $area = campoInteiro('area_id', 'Área');
        if ($animal !== null && !existeNaPropriedade($conn, 'animais', 'animal_id', $animal, $prop)) erro('O animal informado não pertence a esta propriedade.');
        if ($maquina !== null && !existeNaPropriedade($conn, 'maquinas_equipamentos', 'maquina_equip_id', $maquina, $prop)) erro('A máquina informada não pertence a esta propriedade.');
        if ($area !== null && !existeNaPropriedade($conn, 'area_cultivo', 'area_id', $area, $prop)) erro('A área informada não pertence a esta propriedade.');
        if ($id) {
            obterRegistro($conn, 'prontuario', 'prontuario_id', $id, $prop);
            executar($conn, 'UPDATE prontuario SET animal_id = ?, maquina_equip_id = ?, area_id = ?, descricao = ?, data_ocorrencia = ?, nome = ?, situacao_atual = ?, tipo_id = ?
                WHERE prontuario_id = ? AND prop_id = ?',
                'iiissssiii', [$animal, $maquina, $area, $descricao, $data, $nome, $situacao, $tipo, $id, $prop]);
        } else {
            executar($conn, 'INSERT INTO prontuario (prop_id, usuario_id, animal_id, maquina_equip_id, area_id, descricao, data_ocorrencia, nome, situacao_atual, tipo_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'iiiiissssi', [$prop, $usuarioId, $animal, $maquina, $area, $descricao, $data, $nome, $situacao, $tipo]);
        }
        sucesso(['mensagem' => 'Ocorrência salva com sucesso.']);

    case 'excluir':
        $id = campoInteiro('id', 'Ocorrência', true);
        obterRegistro($conn, 'prontuario', 'prontuario_id', $id, $prop);
        $conn->begin_transaction();
        executar($conn, 'DELETE FROM arquivos WHERE prontuario_id = ?', 'i', [$id]);
        executar($conn, 'DELETE FROM prontuario WHERE prontuario_id = ? AND prop_id = ?', 'ii', [$id, $prop]);
        $conn->commit();
        sucesso(['mensagem' => 'Ocorrência excluída (arquivos anexados também).']);

    default:
        erro('Ação inválida.');
}
