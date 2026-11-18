<?php
ini_set('display_errors', '0');
require_once __DIR__ . '/../includes/auth.php';

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

$usuario = exigirLoginPagina($conn);
$propId = propriedadeSelecionada($conn, (int)$usuario['usuario_id']);
if (!$propId) {
    exit('Cadastre ou selecione uma propriedade antes de gerar relatórios.');
}

$conteudos = [
    'resumo' => 'conteudo_resumo.php',
    'animais' => 'conteudo_animais.php',
    'areas' => 'conteudo_areas.php',
    'maquinas' => 'conteudo_maquinas.php',
];
$tipo = $_GET['tipo'] ?? '';
if (!isset($conteudos[$tipo])) {
    exit('Relatório inválido.');
}
if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
    exit('HTML2PDF não instalado. Na pasta do projeto, execute: composer require spipu/html2pdf:5.2.8');
}
require __DIR__ . '/../vendor/autoload.php';

$prop = consultar($conn, 'SELECT prop_id, nome, situacao_atual, tamanho FROM propriedade WHERE prop_id = ?', 'i', [$propId])[0];

try {
    ob_start();
    include __DIR__ . '/' . $conteudos[$tipo];
    $content = ob_get_clean();

    $html2pdf = new Html2Pdf('P', 'A4', 'pt');
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->writeHTML($content);
    $html2pdf->output('relatorio_' . $tipo . '.pdf');
} catch (Html2PdfException $e) {
    $html2pdf->clean();
    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}
