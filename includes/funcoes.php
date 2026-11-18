<?php
require_once __DIR__ . '/categorias.php';

function responder($dados, $codigo = 200) {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}
function erro($mensagem, $codigo = 400) {
    responder(['ok' => false, 'erro' => $mensagem], $codigo);
}
function sucesso($extra = []) {
    responder(array_merge(['ok' => true], $extra));
}

function consultar($conn, $sql, $tipos = '', $params = []) {
    $stmt = $conn->prepare($sql);
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $linhas = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $linhas;
}
function executar($conn, $sql, $tipos = '', $params = []) {
    $stmt = $conn->prepare($sql);
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $r = ['id' => $stmt->insert_id, 'linhas' => $stmt->affected_rows];
    $stmt->close();
    return $r;
}

function entrada($nome) {
    return $_POST[$nome] ?? ($_GET[$nome] ?? '');
}
function campoTexto($nome, $rotulo, $max, $obrigatorio = false) {
    $v = trim((string)entrada($nome));
    if ($v === '') {
        if ($obrigatorio) erro("Informe o campo $rotulo.");
        return null;
    }
    if (mb_strlen($v) > $max) erro("O campo $rotulo aceita no máximo $max caracteres.");
    return $v;
}
function campoInteiro($nome, $rotulo, $obrigatorio = false) {
    $v = trim((string)entrada($nome));
    if ($v === '') {
        if ($obrigatorio) erro("Informe o campo $rotulo.");
        return null;
    }
    if (!ctype_digit($v)) erro("Valor inválido para $rotulo.");
    return (int)$v;
}
function campoDecimal($nome, $rotulo) {
    $v = str_replace(',', '.', trim((string)entrada($nome)));
    if ($v === '') erro("Informe o campo $rotulo.");
    if (!is_numeric($v) || (float)$v <= 0 || (float)$v > 99999999.99) {
        erro("O campo $rotulo deve ser um número maior que zero.");
    }
    return round((float)$v, 2);
}
function campoData($nome, $rotulo, $obrigatorio = false) {
    $v = trim((string)entrada($nome));
    if ($v === '') {
        if ($obrigatorio) erro("Informe o campo $rotulo.");
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $v);
    if (!$d || $d->format('Y-m-d') !== $v) erro("Data inválida no campo $rotulo.");
    return $v;
}
function campoDataHora($nome, $rotulo, $obrigatorio = false) {
    $v = str_replace('T', ' ', trim((string)entrada($nome)));
    if ($v === '') {
        if ($obrigatorio) erro("Informe o campo $rotulo.");
        return null;
    }
    if (strlen($v) === 16) $v .= ':00';
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $v);
    if (!$d || $d->format('Y-m-d H:i:s') !== $v) erro("Data/hora inválida no campo $rotulo.");
    return $v;
}

function usuarioTemPropriedade($conn, $usuarioId, $propId) {
    $r = consultar($conn, 'SELECT 1 FROM usuario_propriedade WHERE usuario_id = ? AND prop_id = ?', 'ii', [$usuarioId, $propId]);
    return count($r) > 0;
}
function exigirAcesso($conn, $usuarioId, $propId) {
    if (!usuarioTemPropriedade($conn, $usuarioId, $propId)) {
        erro('Você não tem acesso a esta propriedade.', 403);
    }
}
function propriedadeSelecionada($conn, $usuarioId) {
    $id = (int)($_SESSION['prop_id'] ?? 0);
    if ($id && usuarioTemPropriedade($conn, $usuarioId, $id)) return $id;
    $r = consultar($conn, 'SELECT p.prop_id FROM propriedade p JOIN usuario_propriedade up ON up.prop_id = p.prop_id WHERE up.usuario_id = ? ORDER BY p.nome LIMIT 1', 'i', [$usuarioId]);
    if ($r) {
        $_SESSION['prop_id'] = (int)$r[0]['prop_id'];
        return $_SESSION['prop_id'];
    }
    unset($_SESSION['prop_id']);
    return null;
}
function exigirPropriedade() {
    global $propId;
    if (!$propId) erro('Cadastre ou selecione uma propriedade primeiro.', 409);
    return $propId;
}
function obterRegistro($conn, $tabela, $pk, $id, $propId) {
    $r = consultar($conn, "SELECT * FROM $tabela WHERE $pk = ? AND prop_id = ?", 'ii', [$id, $propId]);
    if (!$r) erro('Registro não encontrado.', 404);
    return $r[0];
}
function existeNaPropriedade($conn, $tabela, $pk, $id, $propId) {
    return count(consultar($conn, "SELECT 1 FROM $tabela WHERE $pk = ? AND prop_id = ?", 'ii', [$id, $propId])) > 0;
}
function excluirRegistro($conn, $tabela, $pk, $id, $propId) {
    obterRegistro($conn, $tabela, $pk, $id, $propId);
    executar($conn, "DELETE FROM $tabela WHERE $pk = ? AND prop_id = ?", 'ii', [$id, $propId]);
}
function consultarTipos($conn, $categoria = null) {
    $base = 'SELECT tipo_id AS id, nome AS label, definicao AS title FROM tipagem';
    if ($categoria === null) {
        $nomes = TIPOS_POR_CATEGORIA['usuario'] ?? [];
        if (!$nomes) return consultar($conn, "$base ORDER BY nome");
        $filtro = 'NOT IN';
    } else {
        $nomes = TIPOS_POR_CATEGORIA[$categoria] ?? [];
        if (!$nomes) return [];
        $filtro = 'IN';
    }
    $marcas = implode(',', array_fill(0, count($nomes), '?'));
    return consultar($conn, "$base WHERE nome $filtro ($marcas) ORDER BY nome", str_repeat('s', count($nomes)), $nomes);
}

function exigirTipo($conn, $tipoId, $categoria = null) {
    if ($tipoId === null) return;
    $ids = array_map('intval', array_column(consultarTipos($conn, $categoria), 'id'));
    if (!in_array($tipoId, $ids, true)) erro('Tipo inválido para este cadastro.');
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmtData($v) { return $v ? date('d/m/Y', strtotime($v)) : '-'; }
function fmtDataHora($v) { return $v ? date('d/m/Y H:i', strtotime($v)) : '-'; }
function fmtNumero($v) { return number_format((float)$v, 2, ',', '.'); }
function fmtHa($v) { return rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ',') . ' ha'; }
