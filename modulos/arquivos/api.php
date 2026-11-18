<?php
require_once __DIR__ . '/../../includes/api.php';

const MIMES = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'txt' => 'text/plain',
];

function buscarArquivo($conn, $id, $usuarioId, $comConteudo) {
    $campos = $comConteudo ? 'a.nome, a.arquivo' : 'a.nome';
    $r = consultar($conn, "SELECT $campos FROM arquivos a
        JOIN prontuario p ON p.prontuario_id = a.prontuario_id
        JOIN usuario_propriedade up ON up.prop_id = p.prop_id
        WHERE a.arquivo_id = ? AND up.usuario_id = ?", 'ii', [$id, $usuarioId]);
    if (!$r) erro('Arquivo não encontrado.', 404);
    return $r[0];
}

switch ($acao) {
    case 'listar':
        $prop = exigirPropriedade();
        sucesso(['dados' => consultar($conn, 'SELECT a.arquivo_id AS id, a.nome, p.nome AS prontuario, t.nome AS tipo, a.data_upload, LENGTH(a.arquivo) AS bytes
            FROM arquivos a
            JOIN prontuario p ON p.prontuario_id = a.prontuario_id
            LEFT JOIN tipagem t ON t.tipo_id = a.tipo_id
            WHERE p.prop_id = ? ORDER BY a.arquivo_id DESC', 'i', [$prop])]);

    case 'salvar':
        $prop = exigirPropriedade();
        $pront = campoInteiro('prontuario_id', 'Ocorrência', true);
        if (!existeNaPropriedade($conn, 'prontuario', 'prontuario_id', $pront, $prop)) erro('A ocorrência informada não pertence a esta propriedade.');
        $tipo = campoInteiro('tipo_id', 'Tipo');
        exigirTipo($conn, $tipo, 'arquivo');
        $nome = campoTexto('nome', 'Nome', 100);

        if (empty($_FILES['arquivo']) || $_FILES['arquivo']['error'] === UPLOAD_ERR_NO_FILE) erro('Selecione um arquivo para enviar.');
        $f = $_FILES['arquivo'];
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
            erro('O arquivo excede o limite do PHP (upload_max_filesize = ' . ini_get('upload_max_filesize') . '). Aumente no php.ini e reinicie o Apache.');
        }
        if ($f['error'] !== UPLOAD_ERR_OK) erro('Falha no envio do arquivo (código ' . $f['error'] . ').');
        if ($f['size'] <= 0) erro('O arquivo está vazio.');
        if ($f['size'] > TAMANHO_MAX_ARQUIVO) {
            erro('O arquivo excede o limite de ' . floor(TAMANHO_MAX_ARQUIVO / 1048576) . ' MB.');
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!isset(MIMES[$ext])) erro('Tipo de arquivo não permitido. Use: ' . implode(', ', array_keys(MIMES)) . '.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if ($mime !== MIMES[$ext]) erro('O conteúdo do arquivo não corresponde à extensão informada.');

        $nome = $nome ?? basename($f['name']);
        if (strtolower(pathinfo($nome, PATHINFO_EXTENSION)) !== $ext) $nome .= '.' . $ext;
        if (mb_strlen($nome) > 100) erro('O nome do arquivo aceita no máximo 100 caracteres.');

        $maxPacote = (int)consultar($conn, 'SELECT @@max_allowed_packet AS m')[0]['m'];
        if ($f['size'] + 4096 > $maxPacote) {
            erro('O MySQL aceita no máximo ' . round($maxPacote / 1048576, 1) . ' MB por envio (max_allowed_packet). Aumente no my.ini e reinicie o MySQL.');
        }

        $conteudo = file_get_contents($f['tmp_name']);
        $stmt = $conn->prepare('INSERT INTO arquivos (prontuario_id, tipo_id, nome, arquivo, data_upload) VALUES (?, ?, ?, ?, CURDATE())');
        $nulo = null;
        $stmt->bind_param('iisb', $pront, $tipo, $nome, $nulo);
        $stmt->send_long_data(3, $conteudo);
        $stmt->execute();
        $stmt->close();
        sucesso(['mensagem' => 'Arquivo enviado com sucesso.']);

    case 'excluir':
        $id = campoInteiro('id', 'Arquivo', true);
        buscarArquivo($conn, $id, $usuarioId, false);
        executar($conn, 'DELETE FROM arquivos WHERE arquivo_id = ?', 'i', [$id]);
        sucesso(['mensagem' => 'Arquivo excluído.']);

    case 'baixar':
        $arq = buscarArquivo($conn, campoInteiro('id', 'Arquivo', true), $usuarioId, true);
        $ext = strtolower(pathinfo($arq['nome'], PATHINFO_EXTENSION));
        $mime = MIMES[$ext] ?? 'application/octet-stream';
        if ($ext === 'txt') $mime .= '; charset=utf-8';
        $inline = entrada('ver') === '1' && isset(MIMES[$ext]);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode($arq['nome']));
        header('Content-Length: ' . strlen($arq['arquivo']));
        header('X-Content-Type-Options: nosniff');
        echo $arq['arquivo'];
        exit;

    default:
        erro('Ação inválida.');
}
