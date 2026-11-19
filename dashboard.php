<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cenas.php';
$usuario = exigirLoginPagina($conn);
$csrf = tokenCsrf();
$abertura = !empty($_SESSION['abertura']);
unset($_SESSION['abertura']);

$icones = [
    'areas' => '<rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/>',
    'animais' => '<circle cx="6" cy="10" r="2"/><circle cx="10" cy="6" r="2"/><circle cx="15" cy="6" r="2"/><circle cx="19" cy="10" r="2"/><path d="M7 18c0-3 3-6 5-6s5 3 5 6c0 2-2 3-5 3s-5-1-5-3z"/>',
    'maquinas' => '<circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="7.5" stroke-dasharray="3.2 2.6" stroke-width="3"/>',
    'prontuarios' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M8.5 10h7M8.5 14h7M8.5 17.5h4"/>',
    'arquivos' => '<path d="M3 6h6l2 2h10v11H3z"/>',
    'graficos' => '<path d="M4 20V10M10 20V4M16 20v-8M21 20H3"/>',
    'propriedades' => '<path d="M3 11l9-7 9 7v9H3z"/><path d="M10 20v-6h4v6"/>',
    'relatorios' => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h7M9 16h7"/>',
    'area' => '<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>',
    'cultivada' => '<path d="M5 19C5 9 11 5 19 5c0 8-4 14-14 14zM5 19l7-7"/>',
    'calendario' => '<rect x="4" y="5" width="16" height="15" rx="1"/><path d="M4 10h16M9 3v4M15 3v4"/>',
    'som' => '<path d="M4 9v6h4l5 4V5L8 9z"/><path d="M16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11"/>',
    'mudo' => '<path d="M4 9v6h4l5 4V5L8 9z"/><path d="M17 9l5 6M22 9l-5 6"/>',
];
function icone($nome, $classe = 'ico') {
    global $icones;
    return '<svg class="' . $classe . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $icones[$nome] . '</svg>';
}
function cartao($chave, $nome, $idNumero, $rotulo, $lado, $fixo = '') {
    if ($idNumero !== '') {
        $valor = '<b id="' . $idNumero . '">0</b> ';
    } elseif ($fixo !== '') {
        $valor = '<b>' . $fixo . '</b> ';
    } else {
        $valor = '';
    }
    $horizontal = ($lado === 'topo' || $lado === 'base') ? ' horizontal' : '';
    return '<button type="button" class="modulo' . $horizontal . '" data-abrir="' . $chave . '" data-lado="' . $lado . '" data-som="' . $chave . '">'
        . '<span class="m-arte">' . cena($chave) . '</span>'
        . '<span class="m-texto"><span class="m-nome">' . $nome . '</span>'
        . '<span class="m-linha">' . $valor . $rotulo . '</span></span></button>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h($csrf) ?>">
    <title>KultDash - Central de Operações Agrícolas</title>
    <link rel="icon" href="img/logo_kultdash.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
<?php if ($abertura) { ?>
    <link rel="stylesheet" href="css/abertura.css?v=<?= filemtime(__DIR__ . '/css/abertura.css') ?>">
    <script>document.documentElement.classList.add('abertura');</script>
<?php } ?>
</head>
<body>
<?php if ($abertura) { ?>
<div id="abertura" aria-hidden="true">
    <div class="ab-sol"></div>
    <div class="ab-campo"></div>
    <div class="ab-particulas"></div>
    <div class="ab-centro">
        <img class="ab-logo" src="img/logo_kultdash.png" alt="">
        <div class="ab-titulo">KultDash</div>
        <p class="ab-sub">Do plantio à colheita, tudo em um só painel</p>
    </div>
    <p class="ab-dica">pressione qualquer tecla para pular</p>
</div>
<?php } ?>
<div class="app">

    <header class="topo">
        <span class="logo-olho" aria-hidden="true">
            <img class="lo-contorno" src="img/olho_contorno.png" alt="">
            <img class="lo-iris" src="img/olho_iris.png" alt="">
        </span>
        <div class="titulo">
            <h1><span class="marca">KULTDASH</span> · CENTRAL DE OPERAÇÕES AGRÍCOLAS</h1>
            <p>SISTEMA INTEGRADO DE GESTÃO DA PROPRIEDADE</p>
        </div>
        <div class="topo-campo">
            <small>PROPRIEDADE</small>
            <span class="topo-linha">
                <select id="selProp" aria-label="Propriedade exibida"></select>
                <button type="button" class="btn-icone" data-abrir="propriedades" title="Gerenciar propriedades" aria-label="Gerenciar propriedades"><?= icone('propriedades') ?></button>
            </span>
        </div>
        <div class="topo-campo"><small>DATA E HORA</small><strong id="relogio">--</strong></div>
        <div class="topo-campo"><small>USUÁRIO</small><strong><?= h($usuario['nome'] ?: $usuario['usuario']) ?></strong></div>
        <button type="button" id="btnSom" class="btn-icone" title="Ligar ou desligar os sons" aria-label="Ligar ou desligar os sons" aria-pressed="false"><?= icone('som', 'ico som-ico') ?><?= icone('mudo', 'ico mudo-ico') ?></button>
        <a class="btn" href="logout.php">SAIR</a>
    </header>

    <main class="palco">

        <aside class="painel">
            <section class="caixa">
                <h3>RESUMO DA PROPRIEDADE</h3>
                <div class="linha"><?= icone('area') ?><span>ÁREA TOTAL</span><strong id="p-area">0</strong></div>
                <div class="linha"><?= icone('areas') ?><span>ÁREAS DE CULTIVO</span><strong id="p-areas">0</strong></div>
                <div class="linha"><?= icone('animais') ?><span>ANIMAIS</span><strong id="p-animais">0</strong></div>
                <div class="linha"><?= icone('maquinas') ?><span>MÁQUINAS E EQUIP.</span><strong id="p-maquinas">0</strong></div>
                <div class="linha"><?= icone('prontuarios') ?><span>OCORRÊNCIAS</span><strong id="p-ocorrencias">0</strong></div>
            </section>
            <section class="caixa">
                <h3>ÚLTIMA ATUALIZAÇÃO</h3>
                <div class="bloco"><strong id="p-atualizacao">--</strong><small>Dados consultados no banco</small></div>
            </section>
            <section class="caixa">
                <h3>ÚLTIMA OCORRÊNCIA</h3>
                <div class="bloco ocorr"><i class="ponto"></i><div><strong id="p-ult-nome">--</strong><small id="p-ult-data"></small></div></div>
                <button type="button" class="btn largo" data-abrir="prontuarios">VER TODAS</button>
            </section>
        </aside>

        <section class="centro" id="centro">
            <svg class="linhas" id="linhas" aria-hidden="true"></svg>
            <svg class="fiacao" id="fiacao" aria-hidden="true"></svg>

            <div class="col col-esq">
                <?= cartao('areas', 'ÁREAS DE CULTIVO', 'n-areas', 'CADASTRADAS', 'esq') ?>
                <?= cartao('animais', 'ANIMAIS', 'n-animais', 'CADASTRADOS', 'esq') ?>
                <?= cartao('maquinas', 'MÁQUINAS E EQUIPAMENTOS', 'n-maquinas', 'EQUIPAMENTOS', 'esq') ?>
            </div>

            <div class="slot-centro">
                <div class="frame anel" role="button" tabindex="0" data-abrir="propriedades" data-som="propriedade" title="Gerenciar propriedades" aria-label="Gerenciar propriedades">
                    <div class="frame-borda">
                        <div class="frame-in">
                            <p class="f-etiqueta">PROPRIEDADE</p>
                            <h2 id="c-nome">CARREGANDO...</h2>
                            <div class="f-cena"><?= cena('centro') ?></div>
                            <div class="f-rodape">
                                <div><small>ÁREA TOTAL</small><strong id="c-tamanho">--</strong></div>
                                <div><small>SITUAÇÃO</small><strong id="c-situacao" class="f-status">--</strong></div>
                                <div class="f-acao"><small>PROPRIEDADES</small><strong>GERENCIAR <i>›</i></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col-dir">
                <?= cartao('prontuarios', 'PRONTUÁRIOS E OCORRÊNCIAS', 'n-ocorrencias', 'REGISTROS', 'dir') ?>
                <?= cartao('arquivos', 'ARQUIVOS', 'n-arquivos', 'ARQUIVOS', 'dir') ?>
                <?= cartao('relatorios', 'RELATÓRIOS EM PDF', '', 'RELATÓRIOS', 'dir', '4') ?>
            </div>

            <div class="slot-base"><?= cartao('graficos', 'INDICADORES E GRÁFICOS', '', 'GRÁFICOS', 'base', '4') ?></div>
        </section>

        <aside class="painel">
            <section class="caixa">
                <h3>RESUMO DOS INDICADORES</h3>
                <div class="linha"><?= icone('cultivada') ?><span>ÁREA CULTIVADA</span><strong id="i-area-cultivada">0</strong></div>
                <div class="linha"><?= icone('calendario') ?><span>OCORRÊNCIAS NO MÊS</span><strong id="i-ocorrencias-mes">0</strong></div>
                <div class="linha"><?= icone('arquivos') ?><span>ARQUIVOS</span><strong id="i-arquivos">0</strong></div>
            </section>
            <section class="caixa">
                <h3>ACESSO RÁPIDO</h3>
                <button type="button" class="acesso" data-abrir="propriedades"><?= icone('propriedades') ?><span>PROPRIEDADES<small>Cadastrar e selecionar</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="prontuarios"><?= icone('prontuarios') ?><span>OCORRÊNCIAS<small>Histórico de registros</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="arquivos"><?= icone('arquivos') ?><span>ARQUIVOS<small>Documentos e imagens</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="graficos"><?= icone('graficos') ?><span>GRÁFICOS<small>Indicadores dinâmicos</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="relatorios"><?= icone('relatorios') ?><span>RELATÓRIOS<small>Gerar PDF</small></span><i>›</i></button>
            </section>
        </aside>

    </main>

    <footer class="rodape">
        <span>SISTEMA <strong>v1.0.0</strong></span>
        <span class="rodape-centro">
            <span id="status-conexao" class="status">● Verificando conexão...</span>
        </span>
        <span id="rodape-prop">--</span>
    </footer>
</div>

<div id="modal" class="modal oculto" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
    <div class="modal-caixa">
        <div class="modal-topo">
            <h2 id="modalTitulo"></h2>
            <button type="button" id="modalFechar" class="fechar" aria-label="Fechar">×</button>
        </div>
        <div id="modalMsg"></div>
        <div id="modalCorpo" class="modal-corpo"></div>
    </div>
</div>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script src="js/script.js?v=<?= filemtime(__DIR__ . '/js/script.js') ?>"></script>
<script src="js/graficos.js?v=<?= filemtime(__DIR__ . '/js/graficos.js') ?>"></script>
<script src="js/linhas.js?v=<?= filemtime(__DIR__ . '/js/linhas.js') ?>"></script>
<script src="js/sons.js?v=<?= filemtime(__DIR__ . '/js/sons.js') ?>"></script>
<script src="js/olho.js?v=<?= filemtime(__DIR__ . '/js/olho.js') ?>"></script>
<?php if ($abertura) { ?>
<script src="js/abertura.js?v=<?= filemtime(__DIR__ . '/js/abertura.js') ?>"></script>
<?php } ?>
</body>
</html>
