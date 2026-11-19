<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$email = '';
$enviou = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($enviou) {
    $email = trim((string)($_POST['email'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    if (!hash_equals(tokenCsrf(), (string)($_POST['csrf'] ?? ''))) {
        $erro = 'Sessão inválida. Recarregue a página e tente novamente.';
    } elseif ($email === '' || $senha === '') {
        $erro = 'Informe o e-mail e a senha.';
    } else {
        $usuarios = consultar($conn, 'SELECT usuario_id, senha FROM usuario WHERE LOWER(TRIM(email)) = ?', 's', [mb_strtolower($email)]);
        foreach ($usuarios as $u) {
            if (password_verify($senha, trim($u['senha']))) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int)$u['usuario_id'];
                $_SESSION['abertura'] = 1;
                unset($_SESSION['prop_id']);
                header('Location: dashboard.php');
                exit;
            }
        }
        usleep(500000);
        $erro = 'E-mail ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VisionDash - Entrar</title>
    <link rel="icon" href="img/logo_vision.png" type="image/png">
    <link rel="stylesheet" href="css/login.css?v=<?= filemtime(__DIR__ . '/css/login.css') ?>">
    <script>if (<?= $enviou ? 'false' : 'true' ?>) document.documentElement.classList.add('boot');</script>
</head>
<body>

<div id="terminal" aria-hidden="true">
    <div id="term"></div>
    <div id="caixaBarra">
        <div class="barra"><span id="barraPreench"></span></div>
        <span id="barraPct">0%</span>
    </div>
    <p id="dica">pressione qualquer tecla para pular</p>
</div>

<div id="tela">
    <div class="brilho brilho-a"></div>
    <div class="brilho brilho-b"></div>
    <canvas id="fundo"></canvas>
    <div class="rolagem"></div>
    <div class="scan"></div>
    <div class="cintila"></div>
    <div class="vinheta"></div>

    <main class="painel-login">
        <div class="item" style="--i:0"><img class="logo-login" src="img/logo_vision.png" alt="VisionDash"></div>
        <h1 class="item marca" style="--i:1">VisionDash</h1>

        <form method="post" class="form-login" autocomplete="on">
            <input type="hidden" name="csrf" value="<?= h(tokenCsrf()) ?>">
            <?php if ($erro !== '') { ?>
            <div class="erro"><?= h($erro) ?></div>
            <?php } ?>
            <label class="item campo" style="--i:2">E-mail
                <input type="email" id="email" name="email" value="<?= h($email) ?>" autocomplete="username" required>
            </label>
            <div class="item campo" style="--i:3">
                <label for="senha">Senha</label>
                <div class="senha-wrap">
                    <input type="password" id="senha" name="senha" autocomplete="current-password" required>
                    <button type="button" id="verSenha" class="ver-senha" aria-label="Mostrar senha" aria-pressed="false">
                        <svg class="aberto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="fechado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.9 17.9A10.8 10.8 0 0 1 12 19C5.5 19 1.5 12 1.5 12a19 19 0 0 1 4.6-5.4M9.9 5.2A10 10 0 0 1 12 5c6.5 0 10.5 7 10.5 7a19 19 0 0 1-2.2 3.2M1 1l22 22"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="item botao" style="--i:4">Prosseguir <span aria-hidden="true">→</span></button>
        </form>
    </main>
</div>

<script src="js/login.js?v=<?= filemtime(__DIR__ . '/js/login.js') ?>"></script>
</body>
</html>
