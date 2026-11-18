<?php
$titulo = 'Resumo geral da propriedade';
$i = consultar($conn, "SELECT
    (SELECT COUNT(*) FROM area_cultivo WHERE prop_id = ?) AS areas,
    (SELECT COUNT(*) FROM animais WHERE prop_id = ?) AS animais,
    (SELECT COUNT(*) FROM maquinas_equipamentos WHERE prop_id = ?) AS maquinas,
    (SELECT COUNT(*) FROM prontuario WHERE prop_id = ?) AS ocorrencias,
    (SELECT COUNT(*) FROM arquivos a JOIN prontuario p ON p.prontuario_id = a.prontuario_id WHERE p.prop_id = ?) AS arquivos",
    'iiiii', array_fill(0, 5, $propId))[0];
$linhas = [
    'Nome da propriedade' => $prop['nome'],
    'Situação atual' => $prop['situacao_atual'],
    'Tamanho' => fmtHa($prop['tamanho']),
    'Áreas de cultivo' => $i['areas'],
    'Animais' => $i['animais'],
    'Máquinas e equipamentos' => $i['maquinas'],
    'Ocorrências' => $i['ocorrencias'],
    'Arquivos' => $i['arquivos'],
];
?>
<page backcolor="#FEFEFE" backtop="5mm" backbottom="20mm" footer="date;time;page" style="font-size: 12pt">
    <?php include __DIR__ . '/_cabecalho.php'; ?>
    <table cellspacing="0" cellpadding="6" style="width: 100%; font-size: 12pt; border: solid 1px #999999;">
        <thead>
            <tr style="font-weight: bold; background: #dfe9df;">
                <td style="width: 60%; border: solid 1px #999999;">Indicador</td>
                <td style="width: 40%; border: solid 1px #999999;">Valor</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($linhas as $rotulo => $valor) { ?>
            <tr>
                <td style="width: 60%; border: solid 1px #999999;"><?= h($rotulo) ?></td>
                <td style="width: 40%; border: solid 1px #999999;"><?= h($valor) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</page>
