const SONS = {
    propriedade: 'sons/propriedade.mp3',
    propriedades: 'sons/propriedades.mp3',
    areas: 'sons/areas.mp3',
    animais: 'sons/animais.mp3',
    maquinas: 'sons/maquinas.mp3',
    prontuarios: 'sons/ocorrencias.mp3',
    arquivos: 'sons/arquivos.mp3',
    relatorios: 'sons/relatorios.mp3',
    graficos: 'sons/graficos.mp3',
    clique: 'sons/clique.mp3',
};
const VOLUME_SONS = 0.6;

(function () {
    const botao = document.getElementById('btnSom');
    const audios = {};
    const indisponiveis = new Set();
    let tocando = null;
    let mudo = false;
    try { mudo = localStorage.getItem('kultdash_mudo') === '1'; } catch (e) { mudo = false; }

    function obter(chave) {
        if (!SONS[chave] || indisponiveis.has(chave)) return null;
        if (!audios[chave]) {
            const a = new Audio(SONS[chave]);
            a.addEventListener('error', () => indisponiveis.add(chave));
            audios[chave] = a;
        }
        return audios[chave];
    }

    function parar() {
        if (tocando) {
            tocando.pause();
            tocando = null;
        }
    }

    function tocar(chave, exclusivo) {
        if (mudo) return;
        const a = obter(chave);
        if (!a) return;
        if (exclusivo) parar();
        a.volume = VOLUME_SONS;
        a.currentTime = 0;
        const p = a.play();
        if (p && p.catch) p.catch(() => {});
        if (exclusivo) tocando = a;
    }

    function atualizarBotao() {
        if (!botao) return;
        botao.classList.toggle('mudo', mudo);
        botao.setAttribute('aria-pressed', mudo ? 'true' : 'false');
    }

    // Som ao passar o mouse: só com mouse de verdade. No toque o navegador simula "hover" e isso
    // atrapalhava os cliques seguintes (e deixava o som preso até tocar em outro lugar).
    document.querySelectorAll('[data-som]').forEach((el) => {
        el.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') tocar(el.dataset.som, true); });
        el.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') parar(); });
    });
    document.querySelectorAll('[data-abrir]').forEach((el) => {
        el.addEventListener('click', () => tocar('clique', false));
    });
    if (botao) {
        botao.addEventListener('click', () => {
            mudo = !mudo;
            try { localStorage.setItem('kultdash_mudo', mudo ? '1' : '0'); } catch (e) {}
            if (mudo) parar();
            atualizarBotao();
        });
    }
    atualizarBotao();
})();
