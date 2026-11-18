<?php
$titulo = 'Relatório de máquinas e equipamentos';
$rows = consultar($conn, 'SELECT m.maquina_equip_id, m.nome, t.nome AS tipo, m.data_adquirido, m.situacao_atual
    FROM maquinas_equipamentos m LEFT JOIN tipagem t ON t.tipo_id = m.tipo_id
    WHERE m.prop_id = ? ORDER BY m.maquina_equip_id', 'i', [$propId]);
?>
<page backcolor="#FEFEFE" backtop="5mm" backbottom="20mm" footer="date;time;page" style="font-size: 11pt">
    <?php include __DIR__ . '/_cabecalho.php'; ?>
    <table cellspacing="0" cellpadding="5" style="width: 100%; font-size: 10pt; border: solid 1px #999999;">
        <thead>
            <tr style="font-weight: bold; background: #dfe9df;">
                <td style="width: 14%; border: solid 1px #999999;">Identificação</td>
                <td style="width: 28%; border: solid 1px #999999;">Nome</td>
                <td style="width: 20%; border: solid 1px #999999;">Tipo</td>
                <td style="width: 19%; border: solid 1px #999999;">Data de aquisição</td>
                <td style="width: 19%; border: solid 1px #999999;">Situação atual</td>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rows) { ?>
            <tr><td colspan="5" style="width: 100%; border: solid 1px #999999;">Nenhuma máquina ou equipamento cadastrado.</td></tr>
            <?php } foreach ($rows as $l) { ?>
            <tr>
                <td style="width: 14%; border: solid 1px #999999;">Equip. #<?= (int)$l['maquina_equip_id'] ?></td>
                <td style="width: 28%; border: solid 1px #999999;"><?= h($l['nome']) ?></td>
                <td style="width: 20%; border: solid 1px #999999;"><?= h($l['tipo'] ?: '-') ?></td>
                <td style="width: 19%; border: solid 1px #999999;"><?= fmtData($l['data_adquirido']) ?></td>
                <td style="width: 19%; border: solid 1px #999999;"><?= h($l['situacao_atual']) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
    <span style="font-size: 10pt;">Total de máquinas e equipamentos: <?= count($rows) ?></span>
</page>
