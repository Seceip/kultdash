<?php
$titulo = 'Relatório de áreas de cultivo';
$rows = consultar($conn, 'SELECT a.area_id, a.nome, t.nome AS tipo, a.tamanho, a.situacao_atual, a.conteudo
    FROM area_cultivo a JOIN tipagem t ON t.tipo_id = a.tipo_id
    WHERE a.prop_id = ? ORDER BY a.area_id', 'i', [$propId]);
?>
<page backcolor="#FEFEFE" backtop="5mm" backbottom="20mm" footer="date;time;page" style="font-size: 11pt">
    <?php include __DIR__ . '/_cabecalho.php'; ?>
    <table cellspacing="0" cellpadding="5" style="width: 100%; font-size: 10pt; border: solid 1px #999999;">
        <thead>
            <tr style="font-weight: bold; background: #dfe9df;">
                <td style="width: 12%; border: solid 1px #999999;">Identificação</td>
                <td style="width: 22%; border: solid 1px #999999;">Nome</td>
                <td style="width: 18%; border: solid 1px #999999;">Tipo</td>
                <td style="width: 14%; border: solid 1px #999999;">Tamanho (ha)</td>
                <td style="width: 16%; border: solid 1px #999999;">Situação atual</td>
                <td style="width: 18%; border: solid 1px #999999;">Conteúdo</td>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rows) { ?>
            <tr><td colspan="6" style="width: 100%; border: solid 1px #999999;">Nenhuma área de cultivo cadastrada.</td></tr>
            <?php } foreach ($rows as $l) { ?>
            <tr>
                <td style="width: 12%; border: solid 1px #999999;">Área #<?= (int)$l['area_id'] ?></td>
                <td style="width: 22%; border: solid 1px #999999;"><?= h($l['nome']) ?></td>
                <td style="width: 18%; border: solid 1px #999999;"><?= h($l['tipo']) ?></td>
                <td style="width: 14%; border: solid 1px #999999;"><?= fmtHa($l['tamanho']) ?></td>
                <td style="width: 16%; border: solid 1px #999999;"><?= h($l['situacao_atual'] ?: '-') ?></td>
                <td style="width: 18%; border: solid 1px #999999;"><?= h($l['conteudo'] ?: '-') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
    <span style="font-size: 10pt;">Total de áreas: <?= count($rows) ?></span>
</page>
