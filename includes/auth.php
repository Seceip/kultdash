<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/funcoes.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const LOGIN_ATIVO = false;
const USUARIO_TESTE = 'admin';

function usuarioLogado($conn) {
    if (empty($_SESSION['usuario_id']) && !LOGIN_ATIVO) {
        $u = consultar($conn, 'SELECT usuario_id FROM usuario WHERE usuario = ? LIMIT 1', 's', [USUARIO_TESTE]);
        if (!$u) $u = consultar($conn, 'SELECT usuario_id FROM usuario ORDER BY usuario_id LIMIT 1');
        if ($u) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$u[0]['usuario_id'];
        }
    }
    if (empty($_SESSION['usuario_id'])) return null;
    $r = consultar($conn, 'SELECT usuario_id, nome, usuario FROM usuario WHERE usuario_id = ?', 'i', [$_SESSION['usuario_id']]);
    return $r ? $r[0] : null;
}

function urlRaiz() {
    $doc = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT']));
    $raiz = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
    return substr($raiz, strlen($doc)) . '/';
}

function exigirLoginPagina($conn) {
    $u = usuarioLogado($conn);
    if ($u) return $u;
    if (LOGIN_ATIVO) {
        header('Location: ' . urlRaiz() . 'login.php');
        exit;
    }
    http_response_code(500);
    exit('Nenhum usuário cadastrado. Importe banco/dados_iniciais.sql no banco de dados.');
}

function tokenCsrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
