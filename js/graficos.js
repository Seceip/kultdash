const COR_TEXTO = '#cfe3cf';
const COR_GRADE = '#2a3a2a';
const CORES = ['#7ed957', '#3fb0c9', '#e3a72f', '#c96f3f', '#9b7ed9', '#d95f8e', '#5fd9b0', '#d9d95f'];

const GRAFICOS = [
    { id: 'g-animais', titulo: 'Quantidade de animais por tipo', chave: 'animais', forma: 'pizza', cab: ['Tipo', 'Animais'] },
    { id: 'g-maquinas', titulo: 'Máquinas e equipamentos por situação', chave: 'maquinas', forma: 'colunas', cab: ['Situação', 'Equipamentos'] },
    { id: 'g-areas', titulo: 'Tamanho das áreas de cultivo por tipo', chave: 'areas', forma: 'barras', cab: ['Tipo', 'Tamanho (ha)'] },
    { id: 'g-ocorrencias', titulo: 'Ocorrências registradas por mês', chave: 'ocorrencias', forma: 'linhas', cab: ['Mês', 'Ocorrências'] },
];

let dadosGraficos = null;
let promessaGoogle = null;

function carregarGoogleCharts() {
    if (!promessaGoogle) {
        promessaGoogle = new Promise((resolve, reject) => {
            if (typeof google === 'undefined' || !google.charts) {
                reject(new Error('Não foi possível carregar o Google Charts. Verifique a conexão com a internet.'));
                return;
            }
            google.charts.load('current', { packages: ['corechart'], language: 'pt-BR' });
            google.charts.setOnLoadCallback(resolve);
        }).catch((e) => { promessaGoogle = null; throw e; });
    }
    return promessaGoogle;
}

async function abrirGraficos() {
    if (!estado.propriedade) return abrirAviso('Gráficos', 'Cadastre ou selecione uma propriedade primeiro.');
    estado.modulo = null;
    abrirModal(`Gráficos — ${estado.propriedade.nome}`, `<div class="graficos">${GRAFICOS.map((g) =>
        `<div class="grafico"><h4>${g.titulo}</h4><div class="grafico-corpo" id="${g.id}"><p class="vazio">Carregando...</p></div></div>`).join('')}</div>`);
    try {
        const [dados] = await Promise.all([api('graficos/dados_graficos.php'), carregarGoogleCharts()]);
        dadosGraficos = dados;
        desenharGraficos();
    } catch (e) {
        mensagem('erro', e.message);
    }
}

function desenharGraficos() {
    GRAFICOS.forEach((g) => {
        const el = document.getElementById(g.id);
        if (!el || !dadosGraficos) return;
        const linhas = dadosGraficos[g.chave];
        if (!linhas.length) {
            el.innerHTML = '<p class="vazio">Não existem dados para este gráfico.</p>';
            return;
        }
        const rotulo = (l) => {
            if (g.chave !== 'ocorrencias') return l.descricao;
            const [ano, mes] = l.periodo.split('-');
            return `${mes}/${ano}`;
        };
        const tabela = google.visualization.arrayToDataTable([g.cab, ...linhas.map((l) => [rotulo(l), Number(l.total)])]);
        const eixo = { textStyle: { color: COR_TEXTO }, gridlines: { color: COR_GRADE }, baselineColor: COR_GRADE };
        const opcoes = {
            backgroundColor: 'transparent',
            colors: CORES,
            legend: g.forma === 'pizza' ? { position: 'right', textStyle: { color: COR_TEXTO } } : { position: 'none' },
            hAxis: { ...eixo },
            vAxis: { ...eixo },
            chartArea: { left: 50, top: 12, width: '85%', height: '72%' },
        };
        let grafico;
        if (g.forma === 'pizza') {
            opcoes.chartArea = { left: 10, top: 10, width: '90%', height: '88%' };
            opcoes.pieSliceBorderColor = '#0e140f';
            opcoes.pieSliceTextStyle = { color: '#06100a' };
            grafico = new google.visualization.PieChart(el);
        } else if (g.forma === 'colunas') {
            opcoes.vAxis = { ...eixo, format: '0', viewWindow: { min: 0 } };
            grafico = new google.visualization.ColumnChart(el);
        } else if (g.forma === 'barras') {
            opcoes.chartArea = { left: 110, top: 12, width: '75%', height: '72%' };
            opcoes.hAxis = { ...eixo, viewWindow: { min: 0 } };
            grafico = new google.visualization.BarChart(el);
        } else {
            opcoes.vAxis = { ...eixo, format: '0', viewWindow: { min: 0 } };
            opcoes.pointSize = 5;
            grafico = new google.visualization.LineChart(el);
        }
        grafico.draw(tabela, opcoes);
    });
}

let temporizadorResize = null;
window.addEventListener('resize', () => {
    clearTimeout(temporizadorResize);
    temporizadorResize = setTimeout(() => { if (document.getElementById('g-animais')) desenharGraficos(); }, 200);
});
