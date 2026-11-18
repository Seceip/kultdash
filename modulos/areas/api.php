<?php
require_once __DIR__ . '/../../includes/api.php';
$prop = exigirPropriedade();

switch ($acao) {
    case 'listar':
        sucesso(['dados' => consultar($conn, 'SELECT a.area_id AS id, a.nome, t.nome AS tipo, a.tamanho, a.situacao_atual, a.conteudo
            FROM area_cultivo a JOIN tipagem t ON t.tipo_id = a.tipo_id
            WHERE a.prop_id = ? ORDER BY a.nome', 'i', [$prop])]);

    case 'obter':
        sucesso(['dados' => obterRegistro($conn, 'area_cultivo', 'area_id', campoInteiro('id', 'Área', true), $prop)]);

    case 'salvar':
        $id = campoInteiro('id', 'Área');
        $nome = campoTexto('nome', 'Nome', 100, true);
        $tipo = campoInteiro('tipo_id', 'Tipo', true);
        exigirTipo($conn, $tipo, 'area');
        $tamanho = campoDecimal('tamanho', 'Tamanho');
        $situacao = campoTexto('situacao_atual', 'Situação atual', 30);
        $conteudo = campoTexto('conteudo', 'Conteúdo', 100);
        if ($id) {
            obterRegistro($conn, 'area_cultivo', 'area_id', $id, $prop);
            executar($conn, 'UPDATE area_cultivo SET nome = ?, tipo_id = ?, tamanho = ?, situacao_atual = ?, conteudo = ? WHERE area_id = ? AND prop_id = ?',
                'sidssii', [$nome, $tipo, $tamanho, $situacao, $conteudo, $id, $prop]);
        } else {
            executar($conn, 'INSERT INTO area_cultivo (prop_id, tipo_id, tamanho, situacao_atual, nome, conteudo) VALUES (?, ?, ?, ?, ?, ?)',
                'iidsss', [$prop, $tipo, $tamanho, $situacao, $nome, $conteudo]);
        }
        sucesso(['mensagem' => 'Área de cultivo salva com sucesso.']);

    case 'excluir':
        excluirRegistro($conn, 'area_cultivo', 'area_id', campoInteiro('id', 'Área', true), $prop);
        sucesso(['mensagem' => 'Área de cultivo excluída.']);

    default:
        erro('Ação inválida.');
}
