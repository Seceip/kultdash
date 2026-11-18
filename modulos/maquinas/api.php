<?php
require_once __DIR__ . '/../../includes/api.php';
$prop = exigirPropriedade();

switch ($acao) {
    case 'listar':
        sucesso(['dados' => consultar($conn, 'SELECT m.maquina_equip_id AS id, m.nome, t.nome AS tipo, m.data_adquirido, m.situacao_atual
            FROM maquinas_equipamentos m LEFT JOIN tipagem t ON t.tipo_id = m.tipo_id
            WHERE m.prop_id = ? ORDER BY m.nome', 'i', [$prop])]);

    case 'obter':
        sucesso(['dados' => obterRegistro($conn, 'maquinas_equipamentos', 'maquina_equip_id', campoInteiro('id', 'Máquina', true), $prop)]);

    case 'salvar':
        $id = campoInteiro('id', 'Máquina');
        $nome = campoTexto('nome', 'Nome', 100, true);
        $tipo = campoInteiro('tipo_id', 'Tipo');
        exigirTipo($conn, $tipo, 'maquina');
        $data = campoData('data_adquirido', 'Data de aquisição');
        $situacao = campoTexto('situacao_atual', 'Situação atual', 30, true);
        if ($id) {
            obterRegistro($conn, 'maquinas_equipamentos', 'maquina_equip_id', $id, $prop);
            executar($conn, 'UPDATE maquinas_equipamentos SET nome = ?, tipo_id = ?, data_adquirido = ?, situacao_atual = ? WHERE maquina_equip_id = ? AND prop_id = ?',
                'sissii', [$nome, $tipo, $data, $situacao, $id, $prop]);
        } else {
            executar($conn, 'INSERT INTO maquinas_equipamentos (prop_id, nome, tipo_id, data_adquirido, situacao_atual) VALUES (?, ?, ?, ?, ?)',
                'isiss', [$prop, $nome, $tipo, $data, $situacao]);
        }
        sucesso(['mensagem' => 'Máquina/equipamento salvo com sucesso.']);

    case 'excluir':
        excluirRegistro($conn, 'maquinas_equipamentos', 'maquina_equip_id', campoInteiro('id', 'Máquina', true), $prop);
        sucesso(['mensagem' => 'Máquina/equipamento excluído.']);

    default:
        erro('Ação inválida.');
}
