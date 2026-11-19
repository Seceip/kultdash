const UNIDADE_TAMANHO = ' ha';
const TAMANHO_MAX_ARQUIVO = 10 * 1024 * 1024;

const CSRF = document.querySelector('meta[name="csrf"]').content;
const $ = (seletor) => document.querySelector(seletor);
const estado = { propriedade: null, modulo: null, lista: [] };

const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const fmtNum = (n) => Number(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtTamanho = (n) => Number(n).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + UNIDADE_TAMANHO;
const fmtData = (s) => { if (!s) return '—'; const [a, m, d] = s.slice(0, 10).split('-'); return `${d}/${m}/${a}`; };
const fmtDataHora = (s) => (s ? `${fmtData(s)} ${s.slice(11, 16)}` : '—');
const fmtBytes = (b) => (b < 1024 ? `${b} B` : b < 1048576 ? `${(b / 1024).toFixed(1).replace('.', ',')} KB` : `${(b / 1048576).toFixed(1).replace('.', ',')} MB`);

async function api(url, dados = null) {
    const opcoes = {};
    if (dados) {
        let fd = dados;
        if (!(dados instanceof FormData)) {
            fd = new FormData();
            Object.entries(dados).forEach(([k, v]) => fd.append(k, v));
        }
        fd.append('csrf', CSRF);
        opcoes.method = 'POST';
        opcoes.body = fd;
    }
    let resp;
    try {
        resp = await fetch(url, opcoes);
    } catch (e) {
        throw new Error('Falha de conexão com o servidor.');
    }
    if (resp.status === 401) {
        window.location.href = 'login.php';
        throw new Error('Sessão expirada.');
    }
    let json;
    try {
        json = await resp.json();
    } catch (e) {
        throw new Error('Resposta inválida do servidor.');
    }
    if (!json.ok) throw new Error(json.erro || 'Erro ao processar a requisição.');
    return json;
}

function abrirModal(titulo, html = '') {
    $('#modalTitulo').textContent = titulo;
    $('#modalMsg').innerHTML = '';
    $('#modalCorpo').innerHTML = html;
    $('#modalCorpo').scrollTop = 0;
    $('#modal').classList.remove('oculto');
}
function fecharModal() {
    $('#modal').classList.add('oculto');
    $('#modalCorpo').innerHTML = '';
    estado.modulo = null;
}
function mensagem(tipo, texto) {
    $('#modalMsg').innerHTML = texto ? `<div class="msg ${tipo}">${esc(texto)}</div>` : '';
}
function abrirAviso(titulo, texto) {
    abrirModal(titulo);
    mensagem('erro', texto);
}
$('#modalFechar').onclick = fecharModal;
$('#modal').addEventListener('mousedown', (e) => { if (e.target.id === 'modal') fecharModal(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') fecharModal(); });

const MODULOS = {
    propriedades: {
        titulo: 'Propriedades', singular: 'propriedade', url: 'modulos/propriedades/api.php',
        confirmaExcluir: 'Excluir esta propriedade? Ela só pode ser excluída se não tiver registros vinculados.',
        colunas: [['nome', 'Nome'], ['situacao_atual', 'Situação'], ['tamanho', 'Tamanho', fmtTamanho], ['selecionada', 'Em uso', (v) => (v ? '● Sim' : '')]],
        campos: [
            { n: 'nome', r: 'Nome', t: 'text', max: 100, obrig: true },
            { n: 'situacao_atual', r: 'Situação atual', t: 'text', max: 30, obrig: true },
            { n: 'tamanho', r: 'Tamanho (ha)', t: 'number', step: '0.01', min: '0.01', obrig: true },
        ],
        extras: [{
            rotulo: 'Selecionar',
            mostrar: (l) => !l.selecionada,
            fn: async (l) => {
                await api(MODULOS.propriedades.url, { acao: 'selecionar', id: l.id });
                await carregarResumo();
                await mostrarLista('Propriedade selecionada.');
            },
        }],
    },
    areas: {
        titulo: 'Áreas de cultivo', singular: 'área', url: 'modulos/areas/api.php',
        colunas: [['id', '#'], ['nome', 'Nome'], ['tipo', 'Tipo'], ['tamanho', 'Tamanho', fmtTamanho], ['situacao_atual', 'Situação'], ['conteudo', 'Conteúdo']],
        campos: [
            { n: 'nome', r: 'Nome', t: 'text', max: 100, obrig: true },
            { n: 'tipo_id', r: 'Tipo', t: 'select', lista: 'tipos&categoria=area', obrig: true },
            { n: 'tamanho', r: 'Tamanho (ha)', t: 'number', step: '0.01', min: '0.01', obrig: true },
            { n: 'situacao_atual', r: 'Situação atual', t: 'text', max: 30 },
            { n: 'conteudo', r: 'Conteúdo', t: 'text', max: 100, largo: true },
        ],
    },
    animais: {
        titulo: 'Animais', singular: 'animal', url: 'modulos/animais/api.php',
        colunas: [['id', 'Identificação', (v) => `Animal #${v}`], ['tipo', 'Tipo'], ['data_compra', 'Compra', fmtData], ['data_nasci', 'Nascimento', fmtData], ['situacao_atual', 'Situação']],
        campos: [
            { n: 'tipo_id', r: 'Tipo', t: 'select', lista: 'tipos&categoria=animal', obrig: true },
            { n: 'situacao_atual', r: 'Situação atual', t: 'text', max: 30 },
            { n: 'data_compra', r: 'Data de compra', t: 'date' },
            { n: 'data_nasci', r: 'Data de nascimento', t: 'date' },
        ],
    },
    maquinas: {
        titulo: 'Máquinas e equipamentos', singular: 'máquina/equipamento', url: 'modulos/maquinas/api.php',
        colunas: [['id', '#'], ['nome', 'Nome'], ['tipo', 'Tipo'], ['data_adquirido', 'Aquisição', fmtData], ['situacao_atual', 'Situação']],
        campos: [
            { n: 'nome', r: 'Nome', t: 'text', max: 100, obrig: true },
            { n: 'tipo_id', r: 'Tipo', t: 'select', lista: 'tipos&categoria=maquina' },
            { n: 'data_adquirido', r: 'Data de aquisição', t: 'date' },
            { n: 'situacao_atual', r: 'Situação atual', t: 'text', max: 30, obrig: true },
        ],
    },
    prontuarios: {
        titulo: 'Prontuários e ocorrências', singular: 'ocorrência', url: 'modulos/prontuarios/api.php',
        confirmaExcluir: 'Excluir esta ocorrência e os arquivos anexados a ela?',
        colunas: [['nome', 'Nome'], ['data_ocorrencia', 'Data', fmtDataHora], ['situacao_atual', 'Situação'], ['vinculo', 'Vínculo'], ['responsavel', 'Responsável']],
        campos: [
            { n: 'nome', r: 'Nome', t: 'text', max: 100, obrig: true },
            { n: 'data_ocorrencia', r: 'Data da ocorrência', t: 'datetime-local', obrig: true },
            { n: 'situacao_atual', r: 'Situação', t: 'text', max: 30 },
            { n: 'tipo_id', r: 'Tipo', t: 'select', lista: 'tipos' },
            { n: 'animal_id', r: 'Animal (opcional)', t: 'select', lista: 'animais' },
            { n: 'maquina_equip_id', r: 'Máquina/equipamento (opcional)', t: 'select', lista: 'maquinas' },
            { n: 'area_id', r: 'Área de cultivo (opcional)', t: 'select', lista: 'areas' },
            { n: 'descricao', r: 'Descrição', t: 'textarea', max: 500, largo: true },
        ],
    },
    arquivos: {
        titulo: 'Arquivos', singular: 'arquivo', url: 'modulos/arquivos/api.php',
        semEditar: true, semVer: true,
        colunas: [['nome', 'Nome'], ['prontuario', 'Ocorrência'], ['tipo', 'Tipo'], ['data_upload', 'Enviado em', fmtData], ['bytes', 'Tamanho', fmtBytes]],
        campos: [
            { n: 'prontuario_id', r: 'Ocorrência', t: 'select', lista: 'prontuarios', obrig: true },
            { n: 'tipo_id', r: 'Tipo', t: 'select', lista: 'tipos&categoria=arquivo' },
            { n: 'nome', r: 'Nome do arquivo', t: 'text', max: 100, dica: 'Se vazio, usa o nome do arquivo enviado.' },
            { n: 'arquivo', r: 'Arquivo', t: 'file', obrig: true, accept: '.pdf,.jpg,.jpeg,.png,.txt',
              dica: `PDF, JPG, PNG ou TXT, até ${Math.floor(TAMANHO_MAX_ARQUIVO / 1048576)} MB.` },
        ],
        extras: [
            { rotulo: 'Ver', href: (l) => `modulos/arquivos/api.php?acao=baixar&id=${l.id}&ver=1` },
            { rotulo: 'Baixar', href: (l) => `modulos/arquivos/api.php?acao=baixar&id=${l.id}` },
        ],
    },
};

function abrirModulo(chave) {
    const m = MODULOS[chave];
    if (!estado.propriedade && chave !== 'propriedades') {
        abrirAviso(m.titulo, 'Cadastre ou selecione uma propriedade primeiro (botão "Gerenciar propriedades").');
        return;
    }
    estado.modulo = chave;
    abrirModal(m.titulo + (chave !== 'propriedades' ? ` — ${estado.propriedade.nome}` : ''));
    mostrarLista();
}

async function mostrarLista(msgSucesso = '') {
    const m = MODULOS[estado.modulo];
    $('#modalCorpo').innerHTML = '<p class="vazio">Carregando...</p>';
    try {
        const j = await api(`${m.url}?acao=listar`);
        estado.lista = j.dados;
    } catch (e) {
        $('#modalCorpo').innerHTML = '';
        mensagem('erro', e.message);
        return;
    }
    mensagem(msgSucesso ? 'ok' : '', msgSucesso);

    const cab = m.colunas.map((c) => `<th>${esc(c[1])}</th>`).join('') + '<th></th>';
    const linhas = estado.lista.map((l, i) => {
        const cels = m.colunas.map((c) => `<td>${esc(c[2] ? c[2](l[c[0]]) : (l[c[0]] ?? '—'))}</td>`).join('');
        let botoes = '';
        (m.extras || []).forEach((x, k) => {
            if (x.mostrar && !x.mostrar(l)) return;
            botoes += x.href
                ? `<a class="btn pequeno" target="_blank" rel="noopener" href="${esc(x.href(l))}">${x.rotulo}</a>`
                : `<button type="button" class="btn pequeno" data-extra="${k}" data-i="${i}">${x.rotulo}</button>`;
        });
        if (!m.semVer) botoes += `<button type="button" class="btn pequeno" data-ver="${l.id}">Ver</button>`;
        if (!m.semEditar) botoes += `<button type="button" class="btn pequeno" data-editar="${l.id}">Editar</button>`;
        botoes += `<button type="button" class="btn pequeno perigo" data-excluir="${l.id}">Excluir</button>`;
        return `<tr class="${l.selecionada ? 'destaque' : ''}">${cels}<td><div class="acoes">${botoes}</div></td></tr>`;
    }).join('');

    $('#modalCorpo').innerHTML = `
        <div class="barra"><span>${estado.lista.length} registro(s)</span>
            <button type="button" class="btn principal" id="btnNovo">Adicionar ${esc(m.singular)}</button></div>
        ${estado.lista.length
            ? `<table class="tabela"><thead><tr>${cab}</tr></thead><tbody>${linhas}</tbody></table>`
            : '<p class="vazio">Nenhum registro cadastrado.</p>'}`;

    $('#btnNovo').onclick = () => mostrarForm(null);
    $('#modalCorpo').querySelectorAll('[data-ver]').forEach((b) => (b.onclick = () => mostrarForm(b.dataset.ver, true)));
    $('#modalCorpo').querySelectorAll('[data-editar]').forEach((b) => (b.onclick = () => mostrarForm(b.dataset.editar)));
    $('#modalCorpo').querySelectorAll('[data-excluir]').forEach((b) => (b.onclick = () => excluir(b.dataset.excluir)));
    $('#modalCorpo').querySelectorAll('[data-extra]').forEach((b) => (b.onclick = async () => {
        try { await m.extras[b.dataset.extra].fn(estado.lista[b.dataset.i]); } catch (e) { mensagem('erro', e.message); }
    }));
}

function campoHtml(c, valor, opcoes, somenteLeitura) {
    const v = valor ?? '';
    const req = c.obrig ? 'required' : '';
    let controle;
    if (c.t === 'select') {
        const itens = (opcoes || []).map((o) =>
            `<option value="${esc(o.id)}" title="${esc(o.title || '')}" ${String(o.id) === String(v) ? 'selected' : ''}>${esc(o.label)}</option>`).join('');
        controle = `<select name="${c.n}" ${req}><option value="">${c.obrig ? 'Selecione...' : '— Nenhum —'}</option>${itens}</select>`;
    } else if (c.t === 'textarea') {
        controle = `<textarea name="${c.n}" rows="3" maxlength="${c.max}">${esc(v)}</textarea>`;
    } else if (c.t === 'file') {
        controle = `<input type="file" name="${c.n}" accept="${c.accept || ''}" ${req}>`;
    } else {
        const extra = (c.max ? ` maxlength="${c.max}"` : '') + (c.step ? ` step="${c.step}"` : '') + (c.min ? ` min="${c.min}"` : '');
        controle = `<input type="${c.t}" name="${c.n}" value="${esc(v)}" ${req}${extra}>`;
    }
    return `<label class="campo ${c.largo ? 'largo' : ''}">${esc(c.r)}${c.obrig && !somenteLeitura ? ' *' : ''}${controle}${c.dica ? `<small>${esc(c.dica)}</small>` : ''}</label>`;
}

async function mostrarForm(id, somenteLeitura = false) {
    const m = MODULOS[estado.modulo];
    mensagem('', '');
    let registro = {};
    let opcoes = {};
    try {
        const listas = [...new Set(m.campos.filter((c) => c.t === 'select').map((c) => c.lista))];
        const [reg, ...resp] = await Promise.all([
            id ? api(`${m.url}?acao=obter&id=${id}`) : Promise.resolve({ dados: {} }),
            ...listas.map((l) => api(`modulos/opcoes.php?lista=${l}`)),
        ]);
        registro = reg.dados;
        listas.forEach((l, i) => (opcoes[l] = resp[i].dados));
    } catch (e) {
        mensagem('erro', e.message);
        return;
    }

    const campos = m.campos.map((c) => {
        let v = registro[c.n];
        if (c.t === 'date' && v) v = v.slice(0, 10);
        if (c.t === 'datetime-local' && v) v = v.replace(' ', 'T').slice(0, 16);
        return campoHtml(c, v, opcoes[c.lista], somenteLeitura);
    }).join('');

    const titulo = somenteLeitura ? `Detalhes: ${m.singular}` : (id ? `Editar ${m.singular}` : `Adicionar ${m.singular}`);
    $('#modalCorpo').innerHTML = `
        <h3 style="margin-bottom:.8rem;font-size:.95rem">${esc(titulo)}</h3>
        <form class="form" id="formModulo" novalidate>
            <fieldset ${somenteLeitura ? 'disabled' : ''}>${campos}</fieldset>
            <div class="rodape-form">
                <button type="button" class="btn" id="btnVoltar">${somenteLeitura ? 'Voltar' : 'Cancelar'}</button>
                ${somenteLeitura ? '<button type="button" class="btn principal" id="btnEditar">Editar</button>' : '<button type="submit" class="btn principal" id="btnSalvar">Salvar</button>'}
            </div>
        </form>`;

    $('#btnVoltar').onclick = () => mostrarLista();
    if (somenteLeitura) { $('#btnEditar').onclick = () => mostrarForm(id); return; }

    const form = $('#formModulo');
    form.onsubmit = async (ev) => {
        ev.preventDefault();
        if (!form.reportValidity()) return;
        const fd = new FormData(form);
        const arq = m.campos.find((c) => c.t === 'file');
        if (arq && fd.get(arq.n).size > TAMANHO_MAX_ARQUIVO) {
            mensagem('erro', `O arquivo excede o limite de ${Math.floor(TAMANHO_MAX_ARQUIVO / 1048576)} MB.`);
            return;
        }
        fd.append('acao', 'salvar');
        if (id) fd.append('id', id);
        $('#btnSalvar').disabled = true;
        try {
            const j = await api(m.url, fd);
            await carregarResumo();
            await mostrarLista(j.mensagem);
        } catch (e) {
            mensagem('erro', e.message);
            $('#btnSalvar').disabled = false;
        }
    };
}

async function excluir(id) {
    const m = MODULOS[estado.modulo];
    if (!confirm(m.confirmaExcluir || 'Excluir este registro?')) return;
    try {
        const j = await api(m.url, { acao: 'excluir', id });
        await carregarResumo();
        await mostrarLista(j.mensagem);
    } catch (e) {
        mensagem('erro', e.message);
    }
}

const RELATORIOS = [
    { k: 'resumo', t: 'Resumo geral da propriedade', d: 'Situação, tamanho e quantidade de áreas, animais, equipamentos, ocorrências e arquivos.' },
    { k: 'animais', t: 'Relatório de animais', d: 'Identificação, tipo, datas de compra e nascimento e situação atual.' },
    { k: 'areas', t: 'Relatório de áreas de cultivo', d: 'Identificação, nome, tipo, tamanho, situação e conteúdo.' },
    { k: 'maquinas', t: 'Relatório de máquinas e equipamentos', d: 'Identificação, nome, tipo, data de aquisição e situação atual.' },
];
function abrirRelatorios() {
    if (!estado.propriedade) return abrirAviso('Relatórios', 'Cadastre ou selecione uma propriedade primeiro.');
    abrirModal(`Relatórios em PDF — ${estado.propriedade.nome}`, `<div class="relatorios">${RELATORIOS.map((r) =>
        `<div class="rel"><div><strong>${esc(r.t)}</strong><p>${esc(r.d)}</p></div>
         <a class="btn principal" href="relatorios/pdf.php?tipo=${r.k}" target="_blank" rel="noopener">Gerar PDF</a></div>`).join('')}</div>`);
}

function abrir(chave) {
    if (chave === 'graficos') return abrirGraficos();
    if (chave === 'relatorios') return abrirRelatorios();
    abrirModulo(chave);
}
document.querySelectorAll('[data-abrir]').forEach((b) => (b.onclick = () => abrir(b.dataset.abrir)));

const texto = (id, valor) => { $('#' + id).textContent = valor; };

async function carregarResumo() {
    const status = $('#status-conexao');
    try {
        const j = await api('modulos/propriedades/api.php?acao=resumo');
        const p = j.propriedade;
        const ind = j.indicadores;
        estado.propriedade = p;

        texto('c-nome', p ? p.nome.toUpperCase() : 'NENHUMA PROPRIEDADE');
        texto('c-situacao', p ? p.situacao_atual : '—');
        texto('c-tamanho', p ? fmtTamanho(p.tamanho) : '—');
        texto('rodape-prop', p ? `${p.nome} · ID ${p.id}` : 'Sem propriedade selecionada');

        const sel = $('#selProp');
        sel.innerHTML = j.propriedades.length
            ? j.propriedades.map((x) => `<option value="${x.id}" ${x.id == j.selecionada ? 'selected' : ''}>${esc(x.nome)}</option>`).join('')
            : '<option value="">Nenhuma propriedade</option>';

        texto('p-area', p ? fmtTamanho(p.tamanho) : '0');
        texto('p-areas', ind.areas);
        texto('p-animais', ind.animais);
        texto('p-maquinas', ind.maquinas);
        texto('p-ocorrencias', ind.ocorrencias);
        texto('n-areas', ind.areas);
        texto('n-animais', ind.animais);
        texto('n-maquinas', ind.maquinas);
        texto('n-ocorrencias', ind.ocorrencias);
        texto('n-arquivos', ind.arquivos);
        texto('i-area-cultivada', fmtTamanho(ind.area_cultivada));
        texto('i-ocorrencias-mes', ind.ocorrencias_mes);
        texto('i-arquivos', ind.arquivos);

        texto('p-atualizacao', new Date().toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'medium' }));
        if (j.ultima_ocorrencia) {
            texto('p-ult-nome', j.ultima_ocorrencia.nome);
            texto('p-ult-data', fmtDataHora(j.ultima_ocorrencia.data_ocorrencia));
        } else {
            texto('p-ult-nome', 'Nenhuma ocorrência registrada.');
            texto('p-ult-data', '');
        }
        status.textContent = '● Conectado ao banco de dados';
        status.className = 'status ok';
    } catch (e) {
        status.textContent = '● Sem conexão: ' + e.message;
        status.className = 'status falha';
    }
    desenharLinhas();
}

$('#selProp').onchange = async (e) => {
    if (!e.target.value) return;
    try {
        await api(MODULOS.propriedades.url, { acao: 'selecionar', id: e.target.value });
    } catch (err) {
        abrirAviso('Propriedades', err.message);
    }
    await carregarResumo();
};

function desenharLinhas() {
    const svg = $('#linhas');
    if (getComputedStyle(svg).display === 'none') return;
    const centro = $('#centro').getBoundingClientRect();
    const anel = $('.anel').getBoundingClientRect();
    const cx = anel.left + anel.width / 2 - centro.left;
    const cy = anel.top + anel.height / 2 - centro.top;
    const raio = anel.width / 2;
    let html = '';
    document.querySelectorAll('.modulo').forEach((card) => {
        const esq = !!card.closest('.col-esq');
        const b = card.getBoundingClientRect();
        const x1 = (esq ? b.right : b.left) - centro.left;
        const y1 = b.top + b.height / 2 - centro.top;
        const xm = x1 + (esq ? 18 : -18);
        const dx = xm - cx, dy = y1 - cy, dist = Math.hypot(dx, dy) || 1;
        const px = cx + (dx / dist) * raio, py = cy + (dy / dist) * raio;
        html += `<path d="M${x1} ${y1} L${xm} ${y1} L${px} ${py}"/><circle cx="${px}" cy="${py}" r="3"/>`;
    });
    svg.innerHTML = html;
}
window.addEventListener('resize', desenharLinhas);

function atualizarRelogio() {
    texto('relogio', new Date().toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).replace(',', ''));
}
atualizarRelogio();
setInterval(atualizarRelogio, 15000);
carregarResumo();
