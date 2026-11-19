<?php
require_once __DIR__ . '/includes/auth.php';

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Disponível apenas em localhost.');
}

$email = trim((string)($_POST['email'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');
$linhas = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $linhas = consultar($conn, 'SELECT usuario_id, email, senha FROM usuario ORDER BY usuario_id');
}
$sim = '<b style="color:#0a0">SIM</b>';
$nao = '<b style="color:#c00">NÃO</b>';
$hashPadrao = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Diagnóstico de login</title></head>
<body style="font-family:Arial,sans-serif;max-width:60rem;margin:2rem auto;padding:0 1rem">
<h2>Diagnóstico de login (apague este arquivo depois)</h2>
<p>PHP <?= h(PHP_VERSION) ?> · Banco: <?= h(DB_NOME) ?> em <?= h(DB_HOST) ?>:<?= h(DB_PORTA) ?></p>
<p>O PHP consegue verificar o hash padrão com a senha "password"? <?= password_verify('password', $hashPadrao) ? $sim : $nao ?></p>
<form method="post">
    <p>E-mail: <input name="email" value="<?= h($email) ?>" size="35"></p>
    <p>Senha: <input name="senha" type="text" value="<?= h($senha) ?>" size="35"></p>
    <button>Testar</button>
</form>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
<h3>Usuários na tabela</h3>
<table border="1" cellpadding="6" cellspacing="0">
<tr><th>id</th><th>e-mail no banco (entre colchetes)</th><th>tamanho do e-mail</th><th>tamanho do hash (esperado: 60)</th><th>início do hash</th><th>e-mail confere?</th><th>senha confere?</th></tr>
<?php foreach ($linhas as $l) { ?>
<tr>
    <td><?= (int)$l['usuario_id'] ?></td>
    <td>[<?= h($l['email']) ?>]</td>
    <td><?= strlen($l['email']) ?></td>
    <td><?= strlen($l['senha']) ?></td>
    <td><?= h(substr($l['senha'], 0, 12)) ?></td>
    <td><?= mb_strtolower(trim($l['email'])) === mb_strtolower($email) ? $sim : $nao ?></td>
    <td><?= password_verify($senha, trim($l['senha'])) ? $sim : $nao ?></td>
</tr>
<?php } ?>
</table>
<?php if (!$linhas) { ?><p><b>A tabela usuario está vazia.</b> Importe banco/dados_iniciais.sql.</p><?php } ?>
<?php } ?>
</body>
</html>
