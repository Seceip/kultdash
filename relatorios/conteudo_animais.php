<?php
$titulo = 'Relatório de animais';
$rows = consultar($conn, 'SELECT a.animal_id, t.nome AS tipo, a.data_compra, a.data_nasci, a.situacao_atual
    FROM animais a JOIN tipagem t ON t.tipo_id = a.tipo_id
    WHERE a.prop_id = ? ORDER BY a.animal_id', 'i', [$propId]);
?>
<page backcolor="#FEFEFE" backtop="5mm" backbottom="20mm" footer="date;time;page" style="font-size: 11pt">
    <?php include __DIR__ . '/_cabecalho.php'; ?>
    <table cellspacing="0" cellpadding="5" style="width: 100%; font-size: 10pt; border: solid 1px #999999;">
        <thead>
            <tr style="font-weight: bold; background: #dfe9df;">
                <td style="width: 26%; border: solid 1px #999999;">Identificação</td>
                <td style="width: 20%; border: solid 1px #999999;">Tipo</td>
                <td style="width: 18%; border: solid 1px #999999;">Data de compra</td>
                <td style="width: 18%; border: solid 1px #999999;">Data de nascimento</td>
                <td style="width: 18%; border: solid 1px #999999;">Situação atual</td>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rows) { ?>
            <tr><td colspan="5" style="width: 100%; border: solid 1px #999999;">Nenhum animal cadastrado.</td></tr>
            <?php } foreach ($rows as $l) { ?>
            <tr>
                <td style="width: 26%; border: solid 1px #999999;">
                    <barcode dimension="1D" type="C128" value="<?= sprintf('%06d', (int)$l['animal_id']) ?>" label="label" style="width: 30mm; height: 8mm; color: #000000; font-size: 3mm" />
                </td>
                <td style="width: 20%; border: solid 1px #999999;"><?= h($l['tipo']) ?></td>
                <td style="width: 18%; border: solid 1px #999999;"><?= fmtData($l['data_compra']) ?></td>
                <td style="width: 18%; border: solid 1px #999999;"><?= fmtData($l['data_nasci']) ?></td>
                <td style="width: 18%; border: solid 1px #999999;"><?= h($l['situacao_atual'] ?: '-') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
    <span style="font-size: 10pt;">Total de animais: <?= count($rows) ?></span>
</page>
