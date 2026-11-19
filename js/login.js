(function () {
    const raiz = document.documentElement;
    const term = document.getElementById('term');
    const caixaBarra = document.getElementById('caixaBarra');
    const preench = document.getElementById('barraPreench');
    const pct = document.getElementById('barraPct');
    const campoEmail = document.getElementById('email');
    const reduzido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const botaoVer = document.getElementById('verSenha');
    const campoSenha = document.getElementById('senha');
    botaoVer.addEventListener('click', () => {
        const mostrar = campoSenha.type === 'password';
        campoSenha.type = mostrar ? 'text' : 'password';
        botaoVer.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
        botaoVer.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        campoSenha.focus({ preventScroll: true });
        const fim = campoSenha.value.length;
        campoSenha.setSelectionRange(fim, fim);
    });

    let pulado = false;
    let ligado = false;
    const pausa = (ms) => new Promise((ok) => setTimeout(ok, pulado ? 0 : ms));
    const somBoot = new Audio('sons/login.mp3');
    let somTocou = false;

    const LINHAS = [
        ['VISIONDASH SYSTEM v9.3.5', null],
        ['> iniciando núcleo do sistema ............ ', 'OK'],
        ['> carregando módulos de interface ........ ', 'OK'],
        ['> montando painéis e gráficos ............ ', 'OK'],
        ['> iniciando serviço de autenticação ...... ', 'OK'],
        ['> verificando ambiente seguro ............ ', 'OK'],
        ['> sistema pronto', null],
    ];

    async function escrever([texto, status]) {
        const linha = document.createElement('div');
        term.appendChild(linha);
        for (let i = 1; i <= texto.length && !pulado; i++) {
            linha.textContent = texto.slice(0, i);
            await pausa(6);
        }
        linha.textContent = texto;
        if (status) {
            const marca = document.createElement('span');
            marca.className = 'ok';
            marca.textContent = `[ ${status} ]`;
            linha.appendChild(marca);
        }
        await pausa(60);
    }

    async function carregar() {
        caixaBarra.classList.add('visivel');
        let p = 0;
        while (p < 100) {
            p = Math.min(100, p + 2 + Math.random() * 6);
            preench.style.width = p + '%';
            pct.textContent = Math.floor(p) + '%';
            if (p >= 75 && !somTocou) {
            somTocou = true;
            somBoot.play().catch(() => {});
            }
            await pausa(50);
        }
    }

    async function ligar() {
        if (ligado) return;
        ligado = true;
        raiz.classList.remove('boot');
        raiz.classList.add('ligando');
        await new Promise((ok) => setTimeout(ok, 950));
        raiz.classList.remove('ligando');
        raiz.classList.add('pronto');
        setTimeout(() => campoEmail.focus({ preventScroll: true }), 700);
    }

    async function sequencia() {
        for (const linha of LINHAS) await escrever(linha);
        await carregar();
        await pausa(250);
        await ligar();
    }

    function fundoVivo() {
        const tela = document.getElementById('fundo');
        const ctx = tela.getContext('2d');
        let largura = 0, altura = 0, pontos = [];

        function ajustar() {
            const d = window.devicePixelRatio || 1;
            largura = window.innerWidth;
            altura = window.innerHeight;
            tela.width = largura * d;
            tela.height = altura * d;
            ctx.setTransform(d, 0, 0, d, 0, 0);
            const n = Math.round((largura * altura) / 26000);
            pontos = Array.from({ length: n }, () => ({
                x: Math.random() * largura, y: Math.random() * altura,
                vx: (Math.random() - 0.5) * 0.16, vy: (Math.random() - 0.5) * 0.16,
                r: Math.random() * 1.2 + 0.4,
            }));
        }

        function quadro() {
            ctx.clearRect(0, 0, largura, altura);
            for (const p of pontos) {
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0) p.x = largura; else if (p.x > largura) p.x = 0;
                if (p.y < 0) p.y = altura; else if (p.y > altura) p.y = 0;
                ctx.fillStyle = 'rgba(130, 160, 255, 0.35)';
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fill();
            }
            for (let i = 0; i < pontos.length; i++) {
                for (let j = i + 1; j < pontos.length; j++) {
                    const dx = pontos[i].x - pontos[j].x, dy = pontos[i].y - pontos[j].y;
                    const dist = Math.hypot(dx, dy);
                    if (dist < 130) {
                        ctx.strokeStyle = `rgba(120, 110, 255, ${(1 - dist / 130) * 0.1})`;
                        ctx.beginPath();
                        ctx.moveTo(pontos[i].x, pontos[i].y);
                        ctx.lineTo(pontos[j].x, pontos[j].y);
                        ctx.stroke();
                    }
                }
            }
            if (!reduzido) requestAnimationFrame(quadro);
        }

        ajustar();
        window.addEventListener('resize', ajustar);
        quadro();
    }

    fundoVivo();

    if (!raiz.classList.contains('boot')) {
        raiz.classList.add('pronto');
        return;
    }
    if (reduzido) {
        raiz.classList.remove('boot');
        raiz.classList.add('pronto');
        return;
    }
    const pular = () => { if (raiz.classList.contains('boot')) pulado = false; };
    document.addEventListener('keydown', pular);
    document.addEventListener('pointerdown', pular);
    sequencia();
})();
