<?php
date_default_timezone_set('America/Sao_Paulo');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

const DB_HOST = '127.0.0.1';
const DB_USUARIO = 'root';
const DB_SENHA = 'root';
const DB_NOME = 'db_kultive';
const DB_PORTA = 3306;

const TAMANHO_MAX_ARQUIVO = 10 * 1024 * 1024;

try {
    $conn = new mysqli(DB_HOST, DB_USUARIO, DB_SENHA, DB_NOME, DB_PORTA);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    if (defined('RESPOSTA_JSON')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'erro' => 'Sem conexão com o banco de dados.']);
    } else {
        echo 'Não foi possível conectar ao banco "' . DB_NOME . '". Verifique se o MySQL está ativo e confira host, usuário, senha e porta em conexao.php.';
    }
    exit;
}
