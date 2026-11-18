<?php
require_once __DIR__ . '/../../includes/api.php';
$prop = exigirPropriedade();

switch ($acao) {
    case 'listar':
        sucesso(['dados' => consultar($conn, 'SELECT a.animal_id AS id, t.nome AS tipo, a.data_compra, a.data_nasci, a.situacao_atual
            FROM animais a JOIN tipagem t ON t.tipo_id = a.tipo_id
            WHERE a.prop_id = ? ORDER BY a.animal_id DESC', 'i', [$prop])]);

    case 'obter':
        sucesso(['dados' => obterRegistro($conn, 'animais', 'animal_id', campoInteiro('id', 'Animal', true), $prop)]);

    case 'salvar':
        $id = campoInteiro('id', 'Animal');
        $tipo = campoInteiro('tipo_id', 'Tipo', true);
        exigirTipo($conn, $tipo, 'animal');
        $compra = campoData('data_compra', 'Data de compra');
        $nasci = campoData('data_nasci', 'Data de nascimento');
        $situacao = campoTexto('situacao_atual', 'Situação atual', 30);
        if ($id) {
            obterRegistro($conn, 'animais', 'animal_id', $id, $prop);
            executar($conn, 'UPDATE animais SET tipo_id = ?, data_compra = ?, data_nasci = ?, situacao_atual = ? WHERE animal_id = ? AND prop_id = ?',
                'isssii', [$tipo, $compra, $nasci, $situacao, $id, $prop]);
        } else {
            executar($conn, 'INSERT INTO animais (prop_id, tipo_id, data_compra, data_nasci, situacao_atual) VALUES (?, ?, ?, ?, ?)',
                'iisss', [$prop, $tipo, $compra, $nasci, $situacao]);
        }
        sucesso(['mensagem' => 'Animal salvo com sucesso.']);

    case 'excluir':
        excluirRegistro($conn, 'animais', 'animal_id', campoInteiro('id', 'Animal', true), $prop);
        sucesso(['mensagem' => 'Animal excluído.']);

    default:
        erro('Ação inválida.');
}
