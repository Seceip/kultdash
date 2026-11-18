<?php
require_once __DIR__ . '/../../includes/api.php';

function listarPropriedades($conn, $usuarioId) {
    return consultar($conn, 'SELECT p.prop_id AS id, p.nome, p.situacao_atual, p.tamanho
        FROM propriedade p JOIN usuario_propriedade up ON up.prop_id = p.prop_id
        WHERE up.usuario_id = ? ORDER BY p.nome', 'i', [$usuarioId]);
}

switch ($acao) {
    case 'listar':
        $dados = listarPropriedades($conn, $usuarioId);
        foreach ($dados as &$l) {
            $l['selecionada'] = ((int)$l['id'] === (int)$propId);
        }
        unset($l);
        sucesso(['dados' => $dados]);

    case 'obter':
        $id = campoInteiro('id', 'Propriedade', true);
        exigirAcesso($conn, $usuarioId, $id);
        $r = consultar($conn, 'SELECT prop_id, nome, situacao_atual, tamanho FROM propriedade WHERE prop_id = ?', 'i', [$id]);
        if (!$r) erro('Propriedade não encontrada.', 404);
        sucesso(['dados' => $r[0]]);

    case 'salvar':
        $id = campoInteiro('id', 'Propriedade');
        $nome = campoTexto('nome', 'Nome', 100, true);
        $situacao = campoTexto('situacao_atual', 'Situação atual', 30, true);
        $tamanho = campoDecimal('tamanho', 'Tamanho');
        if ($id) {
            exigirAcesso($conn, $usuarioId, $id);
            executar($conn, 'UPDATE propriedade SET nome = ?, situacao_atual = ?, tamanho = ? WHERE prop_id = ?', 'ssdi', [$nome, $situacao, $tamanho, $id]);
        } else {
            $conn->begin_transaction();
            $novo = executar($conn, 'INSERT INTO propriedade (nome, situacao_atual, tamanho) VALUES (?, ?, ?)', 'ssd', [$nome, $situacao, $tamanho])['id'];
            executar($conn, 'INSERT INTO usuario_propriedade (usuario_id, prop_id) VALUES (?, ?)', 'ii', [$usuarioId, $novo]);
            $conn->commit();
            if (!$propId) $_SESSION['prop_id'] = $novo;
        }
        sucesso(['mensagem' => 'Propriedade salva com sucesso.']);

    case 'excluir':
        $id = campoInteiro('id', 'Propriedade', true);
        exigirAcesso($conn, $usuarioId, $id);
        $outros = consultar($conn, 'SELECT COUNT(*) AS n FROM usuario_propriedade WHERE prop_id = ? AND usuario_id <> ?', 'ii', [$id, $usuarioId]);
        if ((int)$outros[0]['n'] > 0) {
            erro('Esta propriedade está vinculada a outros usuários e não pode ser excluída.', 409);
        }
        try {
            $conn->begin_transaction();
            executar($conn, 'DELETE FROM usuario_propriedade WHERE prop_id = ? AND usuario_id = ?', 'ii', [$id, $usuarioId]);
            executar($conn, 'DELETE FROM propriedade WHERE prop_id = ?', 'i', [$id]);
            $conn->commit();
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            if ($e->getCode() == 1451) {
                erro('Exclua antes as áreas, animais, máquinas e ocorrências desta propriedade.', 409);
            }
            throw $e;
        }
        if ((int)($_SESSION['prop_id'] ?? 0) === $id) unset($_SESSION['prop_id']);
        sucesso(['mensagem' => 'Propriedade excluída.']);

    case 'selecionar':
        $id = campoInteiro('id', 'Propriedade', true);
        exigirAcesso($conn, $usuarioId, $id);
        $_SESSION['prop_id'] = $id;
        sucesso();

    case 'resumo':
        $propriedade = null;
        $ind = ['areas' => 0, 'animais' => 0, 'maquinas' => 0, 'ocorrencias' => 0, 'arquivos' => 0, 'area_cultivada' => 0, 'ocorrencias_mes' => 0];
        $ultima = null;
        if ($propId) {
            $propriedade = consultar($conn, 'SELECT prop_id AS id, nome, situacao_atual, tamanho FROM propriedade WHERE prop_id = ?', 'i', [$propId])[0];
            $ind = consultar($conn, "SELECT
                (SELECT COUNT(*) FROM area_cultivo WHERE prop_id = ?) AS areas,
                (SELECT COUNT(*) FROM animais WHERE prop_id = ?) AS animais,
                (SELECT COUNT(*) FROM maquinas_equipamentos WHERE prop_id = ?) AS maquinas,
                (SELECT COUNT(*) FROM prontuario WHERE prop_id = ?) AS ocorrencias,
                (SELECT COUNT(*) FROM arquivos a JOIN prontuario p ON p.prontuario_id = a.prontuario_id WHERE p.prop_id = ?) AS arquivos,
                (SELECT COALESCE(SUM(tamanho), 0) FROM area_cultivo WHERE prop_id = ?) AS area_cultivada,
                (SELECT COUNT(*) FROM prontuario WHERE prop_id = ? AND YEAR(data_ocorrencia) = YEAR(CURDATE()) AND MONTH(data_ocorrencia) = MONTH(CURDATE())) AS ocorrencias_mes",
                'iiiiiii', array_fill(0, 7, $propId))[0];
            $u = consultar($conn, 'SELECT nome, data_ocorrencia FROM prontuario WHERE prop_id = ? ORDER BY data_ocorrencia DESC, prontuario_id DESC LIMIT 1', 'i', [$propId]);
            $ultima = $u ? $u[0] : null;
        }
        sucesso([
            'propriedade' => $propriedade,
            'indicadores' => $ind,
            'ultima_ocorrencia' => $ultima,
            'propriedades' => listarPropriedades($conn, $usuarioId),
            'selecionada' => $propId,
        ]);

    default:
        erro('Ação inválida.');
}
