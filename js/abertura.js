(function () {
    const raiz = document.documentElement;
    const abertura = document.getElementById('abertura');
    if (!abertura) {
        raiz.classList.remove('abertura');
        return;
    }
    const reduzido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cores = ['#b6f23a', '#ffd000', '#7be04a', '#ffae1a'];
    const area = abertura.querySelector('.ab-particulas');

    for (let i = 0; i < 28; i++) {
        const p = document.createElement('span');
        p.className = 'ab-particula';
        p.style.cssText = `--x:${Math.random() * 100}%;--t:${2 + Math.random() * 4}px;--c:${cores[i % cores.length]};` +
            `--d:${6 + Math.random() * 6}s;--a:${-Math.random() * 8}s;--dx:${(Math.random() - 0.5) * 12}vw`;
        area.appendChild(p);
    }

    let encerrado = false;
    function encerrar() {
        if (encerrado) return;
        encerrado = true;
        abertura.classList.add('fim');
        raiz.classList.remove('abertura');
        setTimeout(() => {
            abertura.remove();
            window.dispatchEvent(new Event('resize'));
        }, 950);
    }

    document.addEventListener('keydown', encerrar);
    document.addEventListener('pointerdown', encerrar);
    if (reduzido) {
        encerrar();
        return;
    }
    setTimeout(encerrar, 3600);
})();
