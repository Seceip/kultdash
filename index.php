<?php
include 'includes/base.php';
$u = lista($conn, "select nome, usuario from usuario where usuario_id=?", 'i', [$usuario_id]);
$nomeUsuario = $u ? ($u[0]['nome'] ?: $u[0]['usuario']) : 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <title>KultDash</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div id="app">
            <header>
                <div class="logo">🌱 <b>KultDash</b></div>
                <div class="titulo">
                    <div><b>CENTRO DE OPERAÇÕES AGRÍCOLAS</b></div>
                    <small>Sistema integrado de gestão da propriedade</small>
                </div>
                <div class="info"><small>DATA E HORA</small><div id="data-hora"></div></div>
                <div class="info"><small>USUÁRIO</small><div><?= h($nomeUsuario) ?></div></div>
                <div class="info"><small>PROPRIEDADE</small>
                    <select id="sel-propriedade" class="form-control input-sm"></select>
                </div>
                <a href="logout.php" class="btn btn-default btn-sm">Sair</a>
            </header>

            <main>
                <aside>
                    <div class="painel">
                        <h5>RESUMO DA PROPRIEDADE</h5>
                        <div class="linha"><span>Área total</span><b id="prop-tamanho2">-</b></div>
                        <div class="linha"><span>Áreas de cultivo</span><b class="qtd-areas">0</b></div>
                        <div class="linha"><span>Animais</span><b class="qtd-animais">0</b></div>
                        <div class="linha"><span>Equipamentos</span><b class="qtd-maquinas">0</b></div>
                        <div class="linha"><span>Ocorrências</span><b class="qtd-ocorrencias">0</b></div>
                    </div>
                    <div class="painel">
                        <h5>ÚLTIMA ATUALIZAÇÃO</h5>
                        <div id="ultima-atualizacao">-</div>
                    </div>
                    <div class="painel">
                        <h5>ÚLTIMA OCORRÊNCIA</h5>
                        <div id="ultima-ocorrencia">-</div>
                        <button class="btn btn-default btn-sm btn-block" style="margin-top:8px" data-modulo="prontuarios">VER TODAS</button>
                    </div>
                </aside>

                <section id="palco">
                    <svg id="linhas" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <line x1="19" y1="16" x2="50" y2="50"/>
                        <line x1="13" y1="50" x2="50" y2="50"/>
                        <line x1="19" y1="84" x2="50" y2="50"/>
                        <line x1="81" y1="16" x2="50" y2="50"/>
                        <line x1="87" y1="50" x2="50" y2="50"/>
                        <line x1="81" y1="84" x2="50" y2="50"/>
                    </svg>

                    <div id="propriedade">
                        <div class="simbolo">🌱</div>
                        <h2 id="prop-nome">-</h2>
                        <div class="suave">PROPRIEDADE SELECIONADA</div>
                        <div>STATUS: <b id="prop-situacao" class="verde">-</b></div>
                        <div>TAMANHO: <b id="prop-tamanho">-</b></div>
                    </div>

                    <button class="modulo" style="left:19%; top:16%" data-modulo="areas">
                        <span class="icone">🌾</span>
                        <span class="nome">ÁREAS DE CULTIVO</span><span class="qtd qtd-areas">0</span>
                    </button>
                    <button class="modulo" style="left:13%; top:50%" data-modulo="animais">
                        <span class="icone">🐄</span>
                        <span class="nome">ANIMAIS</span><span class="qtd qtd-animais">0</span>
                    </button>
                    <button class="modulo" style="left:19%; top:84%" data-modulo="maquinas">
                        <span class="icone">🚜</span>
                        <span class="nome">MÁQUINAS E EQUIP.</span><span class="qtd qtd-maquinas">0</span>
                    </button>
                    <button class="modulo" style="left:81%; top:16%" data-modulo="prontuarios">
                        <span class="icone">📋</span>
                        <span class="nome">PRONTUÁRIOS</span><span class="qtd qtd-ocorrencias">0</span>
                    </button>
                    <button class="modulo" style="left:87%; top:50%" data-modulo="arquivos">
                        <span class="icone">📁</span>
                        <span class="nome">ARQUIVOS</span><span class="qtd qtd-arquivos">0</span>
                    </button>
                    <button class="modulo abre-graficos" style="left:81%; top:84%">
                        <span class="icone">📊</span>
                        <span class="nome">INDICADORES</span><span class="qtd">4 gráficos</span>
                    </button>
                </section>

                <aside>
                    <div class="painel">
                        <h5>RESUMO DOS INDICADORES</h5>
                        <div class="linha"><span>Situação</span><b class="verde" id="ind-situacao">-</b></div>
                        <div class="linha"><span>Ocorrências</span><b class="qtd-ocorrencias">0</b></div>
                        <div class="linha"><span>Arquivos</span><b class="qtd-arquivos">0</b></div>
                    </div>
                    <div class="painel">
                        <h5>ACESSOS RÁPIDOS</h5>
                        <button class="acesso" data-modulo="propriedades">🏡 Propriedades</button>
                        <button class="acesso" data-modulo="prontuarios">⚠️ Ocorrências</button>
                        <button class="acesso abre-graficos">📊 Gráficos</button>
                        <button class="acesso abre-relatorios">📄 Relatórios PDF</button>
                    </div>
                </aside>
            </main>

            <footer>
                <span>KultDash v1.0</span>
                <span>Conexão: <b id="status-conexao" class="verde">Online</b></span>
                <span>Central de Operações Agrícolas</span>
            </footer>
        </div>

        <!-- Modal dos módulos (lista + formulário no mesmo modal) -->
        <div class="modal fade" id="modal-modulo" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="titulo-modulo"></h4>
                    </div>
                    <div class="modal-body">
                        <div id="vista-lista">
                            <div class="clearfix" style="margin-bottom:10px">
                                <button type="button" id="btn-adicionar" class="btn btn-success pull-right">Adicionar</button>
                            </div>
                            <div style="max-height:50vh; overflow:auto">
                                <table class="table table-bordered table-striped">
                                    <thead></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <ul id="pagination" class="pagination-sm"></ul>
                        </div>
                        <div id="vista-form" style="display:none">
                            <h4 id="titulo-form"></h4>
                            <form id="form-modulo" enctype="multipart/form-data"></form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal dos gráficos -->
        <div class="modal fade" id="modal-graficos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Gráficos da propriedade</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6"><div id="chart_animais" class="grafico"></div></div>
                            <div class="col-md-6"><div id="chart_maquinas" class="grafico"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><div id="chart_areas" class="grafico"></div></div>
                            <div class="col-md-6"><div id="chart_ocorrencias" class="grafico"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal dos relatórios -->
        <div class="modal fade" id="modal-relatorios" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Relatórios em PDF</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <tr><td>Resumo geral da propriedade</td>
                                <td><a class="btn btn-success btn-sm btn-pdf" target="_blank" data-relatorio="propriedade">Gerar PDF</a></td></tr>
                            <tr><td>Relatório de animais</td>
                                <td><a class="btn btn-success btn-sm btn-pdf" target="_blank" data-relatorio="animais">Gerar PDF</a></td></tr>
                            <tr><td>Relatório de áreas de cultivo</td>
                                <td><a class="btn btn-success btn-sm btn-pdf" target="_blank" data-relatorio="areas">Gerar PDF</a></td></tr>
                            <tr><td>Relatório de máquinas e equipamentos</td>
                                <td><a class="btn btn-success btn-sm btn-pdf" target="_blank" data-relatorio="maquinas">Gerar PDF</a></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twbs-pagination/1.3.1/jquery.twbsPagination.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
        <script src="https://www.gstatic.com/charts/loader.js"></script>
        <script src="js/script.js"></script>
        <script src="js/graficos.js"></script>
    </body>
</html>