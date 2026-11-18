<?php
require_once __DIR__ . '/includes/auth.php';
$usuario = exigirLoginPagina($conn);
$csrf = tokenCsrf();

$icones = [
    'areas' => '<rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/>',
    'animais' => '<circle cx="6" cy="10" r="2"/><circle cx="10" cy="6" r="2"/><circle cx="15" cy="6" r="2"/><circle cx="19" cy="10" r="2"/><path d="M7 18c0-3 3-6 5-6s5 3 5 6c0 2-2 3-5 3s-5-1-5-3z"/>',
    'maquinas' => '<circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="7.5" stroke-dasharray="3.2 2.6" stroke-width="3"/>',
    'prontuarios' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M8.5 10h7M8.5 14h7M8.5 17.5h4"/>',
    'arquivos' => '<path d="M3 6h6l2 2h10v11H3z"/>',
    'graficos' => '<path d="M4 20V10M10 20V4M16 20v-8M21 20H3"/>',
    'propriedades' => '<path d="M3 11l9-7 9 7v9H3z"/><path d="M10 20v-6h4v6"/>',
    'relatorios' => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 12h7M9 16h7"/>',
];
function icone($nome) {
    global $icones;
    return '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $icones[$nome] . '</svg>';
}

$colunaEsq = [
    ['areas', 'ÁREAS DE CULTIVO', 'n-areas', 'ÁREAS'],
    ['animais', 'ANIMAIS', 'n-animais', 'ANIMAIS'],
    ['maquinas', 'MÁQUINAS E EQUIPAMENTOS', 'n-maquinas', 'EQUIPAMENTOS'],
];
$colunaDir = [
    ['prontuarios', 'PRONTUÁRIOS E OCORRÊNCIAS', 'n-ocorrencias', 'REGISTROS'],
    ['arquivos', 'ARQUIVOS', 'n-arquivos', 'ARQUIVOS'],
    ['graficos', 'INDICADORES', '', 'GRÁFICOS'],
];
function cartao($c) {
    $numero = $c[2] ? '<span class="m-num" id="' . $c[2] . '">0</span>' : '<span class="m-num">4</span>';
    return '<button type="button" class="modulo" data-abrir="' . $c[0] . '">'
        . '<span class="m-nome">' . $c[1] . '</span>'
        . '<span class="m-corpo"><span class="m-ico">' . icone($c[0]) . '</span>' . $numero
        . '<span class="m-rot">' . $c[3] . '</span><span class="seta">›</span></span></button>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h($csrf) ?>">
    <title>KultDash - Central de Operações Agrícolas</title>
    <link rel="icon" href="img/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
</head>
<body>
<div class="app">

    <header class="topo caixa">
        <img src="img/logo.svg" alt="" class="logo">
        <div class="titulo">
            <h1>KULTDASH · CENTRAL DE OPERAÇÕES AGRÍCOLAS</h1>
            <p>Sistema integrado de gestão da propriedade</p>
        </div>
        <div class="topo-info"><small>DATA E HORA</small><strong id="relogio">--</strong></div>
        <div class="topo-info"><small>USUÁRIO</small><strong><?= h($usuario['nome'] ?: $usuario['usuario']) ?></strong></div>
        <a class="btn" href="logout.php">Sair</a>
    </header>

    <main class="palco">

        <aside class="painel">
            <div class="caixa">
                <h3>RESUMO DA PROPRIEDADE</h3>
                <div class="linha"><span>Área total</span><strong id="p-area">0</strong></div>
                <div class="linha"><span>Áreas de cultivo</span><strong id="p-areas">0</strong></div>
                <div class="linha"><span>Animais</span><strong id="p-animais">0</strong></div>
                <div class="linha"><span>Máquinas e equipamentos</span><strong id="p-maquinas">0</strong></div>
                <div class="linha"><span>Ocorrências</span><strong id="p-ocorrencias">0</strong></div>
            </div>
            <div class="caixa">
                <h3>ÚLTIMA ATUALIZAÇÃO</h3>
                <div class="bloco"><strong id="p-atualizacao">--</strong><small>Dados consultados no banco</small></div>
            </div>
            <div class="caixa">
                <h3>ÚLTIMA OCORRÊNCIA</h3>
                <div class="bloco"><strong id="p-ult-nome">--</strong><small id="p-ult-data"></small></div>
                <button type="button" class="btn largo" data-abrir="prontuarios">Ver todas</button>
            </div>
        </aside>

        <section class="centro" id="centro">
            <svg class="linhas" id="linhas" aria-hidden="true"></svg>

            <div class="col col-esq"><?php foreach ($colunaEsq as $c) echo cartao($c); ?></div>

            <div class="no-central">
                <div class="anel">
                    <div class="circulo">
                        <img src="img/logo.svg" alt="" class="circ-logo">
                        <h2 id="c-nome">CARREGANDO...</h2>
                        <p class="circ-sub">PROPRIEDADE</p>
                        <p class="circ-status">SITUAÇÃO: <strong id="c-situacao">--</strong></p>
                        <p class="circ-status">TAMANHO: <strong id="c-tamanho">--</strong></p>
                    </div>
                </div>
                <div class="seletor">
                    <select id="selProp" aria-label="Propriedade exibida"></select>
                    <button type="button" class="btn" data-abrir="propriedades">Gerenciar propriedades</button>
                </div>
            </div>

            <div class="col col-dir"><?php foreach ($colunaDir as $c) echo cartao($c); ?></div>
        </section>

        <aside class="painel">
            <div class="caixa">
                <h3>RESUMO DOS INDICADORES</h3>
                <div class="linha"><span>Área cultivada (soma)</span><strong id="i-area-cultivada">0</strong></div>
                <div class="linha"><span>Ocorrências no mês</span><strong id="i-ocorrencias-mes">0</strong></div>
                <div class="linha"><span>Arquivos armazenados</span><strong id="i-arquivos">0</strong></div>
            </div>
            <div class="caixa">
                <h3>ACESSOS RÁPIDOS</h3>
                <button type="button" class="acesso" data-abrir="propriedades"><?= icone('propriedades') ?><span>Propriedades<small>Cadastrar e selecionar</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="prontuarios"><?= icone('prontuarios') ?><span>Ocorrências<small>Histórico de registros</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="arquivos"><?= icone('arquivos') ?><span>Arquivos<small>Documentos e imagens</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="graficos"><?= icone('graficos') ?><span>Gráficos<small>Indicadores dinâmicos</small></span><i>›</i></button>
                <button type="button" class="acesso" data-abrir="relatorios"><?= icone('relatorios') ?><span>Relatórios<small>Gerar PDF</small></span><i>›</i></button>
            </div>
        </aside>

    </main>

    <footer class="rodape caixa">
        <span>SISTEMA <strong>v1.0.0</strong></span>
        <span id="status-conexao" class="status">● Verificando conexão...</span>
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
</body>
</html>
