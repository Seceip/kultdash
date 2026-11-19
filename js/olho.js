// Logo animado: a íris (com a planta) olha de um lado para o outro de tempos em tempos
// e o olho pisca bem raramente. O quadro da propriedade também responde ao teclado.
(function () {
    const olho = document.querySelector('.logo-olho');
    const reduzir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (olho && !reduzir) {
        const sorteio = (a, b) => a + Math.random() * (b - a);
        // deslocamentos da íris em % do tamanho do logo: [horizontal, vertical]
        const posicoes = [[-3.8, 0], [3.8, 0], [-2.4, -1.4], [2.6, -1.2], [-3.2, 1], [3.2, 1.2], [0, 0]];
        let ultima = 6;

        function olhar() {
            if (!document.hidden) {
                let i;
                do { i = Math.floor(Math.random() * posicoes.length); } while (i === ultima);
                ultima = i;
                olho.style.setProperty('--ox', posicoes[i][0] + '%');
                olho.style.setProperty('--oy', posicoes[i][1] + '%');
            }
            setTimeout(olhar, sorteio(1500, 4200));
        }

        function piscarUmaVez() {
            olho.classList.add('pisca');
            setTimeout(() => olho.classList.remove('pisca'), 280);
        }

        function piscar() {
            if (!document.hidden) {
                piscarUmaVez();
                if (Math.random() < 0.2) setTimeout(piscarUmaVez, 460); // às vezes pisca duas vezes
            }
            setTimeout(piscar, sorteio(9000, 20000));
        }

        setTimeout(olhar, 1800);
        setTimeout(piscar, sorteio(6000, 12000));
    }

    // Quadro central da propriedade funciona como botão: Enter e Espaço abrem igual ao clique.
    const quadro = document.querySelector('.frame[data-abrir]');
    if (quadro) {
        quadro.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                quadro.click();
            }
        });
    }
})();
