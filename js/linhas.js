(function () {
    const centro = document.getElementById('centro');
    const svg = document.getElementById('fiacao');
    const quadro = document.querySelector('.frame-borda');
    if (!centro || !svg || !quadro) return;

    function no(x, y) {
        return `<rect class="no" x="${(x - 2.5).toFixed(1)}" y="${(y - 2.5).toFixed(1)}" width="5" height="5"/>`;
    }

    function desenhar() {
        if (getComputedStyle(svg).display === 'none') return;
        const c = centro.getBoundingClientRect();
        const q = quadro.getBoundingClientRect();
        const qx = q.left - c.left, qy = q.top - c.top;
        let html = '';
        document.querySelectorAll('[data-lado]').forEach((card) => {
            const b = card.getBoundingClientRect();
            const lado = card.dataset.lado;
            let pontos;
            if (lado === 'esq' || lado === 'dir') {
                const x1 = (lado === 'esq' ? b.right : b.left) - c.left;
                const y1 = b.top + b.height / 2 - c.top;
                const x2 = lado === 'esq' ? qx : qx + q.width;
                const y2 = Math.min(Math.max(y1, qy + 30), qy + q.height - 30);
                const xm = (x1 + x2) / 2;
                pontos = [[x1, y1], [xm, y1], [xm, y2], [x2, y2]];
            } else {
                const x1 = b.left + b.width / 2 - c.left;
                const y1 = (lado === 'topo' ? b.bottom : b.top) - c.top;
                const y2 = lado === 'topo' ? qy : qy + q.height;
                pontos = [[x1, y1], [x1, y2]];
            }
            const d = 'M' + pontos.map((p) => p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' L');
            html += `<path d="${d}"/>` + no(...pontos[0]) + no(...pontos[pontos.length - 1]);
        });
        svg.innerHTML = html;
    }

    window.addEventListener('resize', desenhar);
    window.addEventListener('load', desenhar);
    if (window.ResizeObserver) new ResizeObserver(desenhar).observe(centro);
    desenhar();
})();
