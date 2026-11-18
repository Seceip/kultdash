<?php
require_once __DIR__ . '/../includes/api.php';
$prop = exigirPropriedade();

$animais = consultar($conn, 'SELECT t.nome AS descricao, COUNT(*) AS total
    FROM animais a JOIN tipagem t ON t.tipo_id = a.tipo_id
    WHERE a.prop_id = ? GROUP BY t.tipo_id, t.nome ORDER BY total DESC', 'i', [$prop]);

$maquinas = consultar($conn, "SELECT COALESCE(situacao_atual, 'Não informada') AS descricao, COUNT(*) AS total
    FROM maquinas_equipamentos WHERE prop_id = ? GROUP BY descricao ORDER BY total DESC", 'i', [$prop]);

$areas = consultar($conn, 'SELECT t.nome AS descricao, SUM(a.tamanho) AS total
    FROM area_cultivo a JOIN tipagem t ON t.tipo_id = a.tipo_id
    WHERE a.prop_id = ? GROUP BY t.tipo_id, t.nome ORDER BY total DESC', 'i', [$prop]);

$ocorrencias = consultar($conn, "SELECT DATE_FORMAT(data_ocorrencia, '%Y-%m') AS periodo, COUNT(*) AS total
    FROM prontuario WHERE prop_id = ? AND data_ocorrencia IS NOT NULL
    GROUP BY periodo ORDER BY periodo", 'i', [$prop]);

$numerico = function ($linhas) {
    foreach ($linhas as &$linha) {
        $linha['total'] = (float)$linha['total'];
    }
    return $linhas;
};

sucesso([
    'animais' => $numerico($animais),
    'maquinas' => $numerico($maquinas),
    'areas' => $numerico($areas),
    'ocorrencias' => $numerico($ocorrencias),
]);
