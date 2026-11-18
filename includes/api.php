<?php
define('RESPOSTA_JSON', true);
ini_set('display_errors', '0');
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

set_exception_handler(function ($e) {
    $codigo = 500;
    $msg = 'Erro interno ao processar a requisição.';
    if ($e instanceof mysqli_sql_exception) {
        if ($e->getCode() == 1451) {
            $codigo = 409;
            $msg = 'Não é possível excluir: existem registros vinculados a este item.';
        } elseif ($e->getCode() == 1452) {
            $codigo = 400;
            $msg = 'Vínculo inválido com outro registro.';
        }
    }
    responder(['ok' => false, 'erro' => $msg], $codigo);
});

$usuario = usuarioLogado($conn);
if (!$usuario) erro('Sessão expirada. Acesse o sistema novamente.', 401);
$usuarioId = (int)$usuario['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        erro('O envio excede o limite do PHP (post_max_size = ' . ini_get('post_max_size') . '). Aumente no php.ini e reinicie o Apache.');
    }
    if (!hash_equals(tokenCsrf(), (string)($_POST['csrf'] ?? ''))) {
        erro('Requisição inválida (token de segurança).', 403);
    }
}

$acao = (string)entrada('acao');
$propId = propriedadeSelecionada($conn, $usuarioId);
