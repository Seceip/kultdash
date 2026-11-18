<?php
require_once __DIR__ . '/../includes/api.php';

$lista = (string)entrada('lista');

if ($lista === 'tipos') {
    $categoria = (string)entrada('categoria');
    if ($categoria === '') {
        $d = consultar($conn, "SELECT tipo_id AS id, nome AS label, definicao AS title FROM tipagem WHERE categoria <> 'usuario' ORDER BY nome");
    } else {
        $d = consultar($conn, 'SELECT tipo_id AS id, nome AS label, definicao AS title FROM tipagem WHERE categoria = ? ORDER BY nome', 's', [$categoria]);
    }
    sucesso(['dados' => $d]);
}

$prop = exigirPropriedade();
switch ($lista) {
    case 'animais':
        $d = consultar($conn, "SELECT a.animal_id AS id, CONCAT('Animal #', a.animal_id, ' - ', t.nome) AS label
            FROM animais a JOIN tipagem t ON t.tipo_id = a.tipo_id WHERE a.prop_id = ? ORDER BY a.animal_id DESC", 'i', [$prop]);
        break;
    case 'maquinas':
        $d = consultar($conn, 'SELECT maquina_equip_id AS id, nome AS label FROM maquinas_equipamentos WHERE prop_id = ? ORDER BY nome', 'i', [$prop]);
        break;
    case 'areas':
        $d = consultar($conn, 'SELECT area_id AS id, nome AS label FROM area_cultivo WHERE prop_id = ? ORDER BY nome', 'i', [$prop]);
        break;
    case 'prontuarios':
        $d = consultar($conn, "SELECT prontuario_id AS id, CONCAT('#', prontuario_id, ' - ', nome) AS label
            FROM prontuario WHERE prop_id = ? ORDER BY data_ocorrencia DESC, prontuario_id DESC", 'i', [$prop]);
        break;
    default:
        erro('Lista inválida.');
}
sucesso(['dados' => $d]);
