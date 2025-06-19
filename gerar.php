<?php

/**
 * gerar.php
 * Gera uma assinatura em PNG com QR Code vCard e fundo branco.
 */

/*************** AUTOLOAD & CONFIGURAÇÃO ***************/
require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

/****************** FUNÇÕES AUXILIARES ******************/
function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

function wrapText($image, string $text, string $fontFile, int $fontSize, int $maxWidth): array
{
    $words = preg_split('/\s+/', $text);
    $lines = [];
    $current = '';
    foreach ($words as $word) {
        $test = $current === '' ? $word : $current . ' ' . $word;
        $box  = imagettfbbox($fontSize, 0, $fontFile, $test);
        $w    = $box[2] - $box[0];
        if ($w > $maxWidth && $current !== '') {
            $lines[] = $current;
            $current = $word;
        } else {
            $current = $test;
        }
    }
    if ($current !== '') {
        $lines[] = $current;
    }
    return $lines;
}

/****************** LEITURA DAS ENTRADAS ******************/
$empresas     = require __DIR__ . '/empresas.php';

$empresaKey   = $_POST['empresa']        ?? '';
$primeiroNome = trim($_POST['primeiro_nome'] ?? '');
$sobrenome    = trim($_POST['sobrenome']     ?? '');
$cargo        = trim($_POST['cargo']        ?? '');
$email        = trim($_POST['email']        ?? '');
$telefone     = trim($_POST['telefone']     ?? '');
$endereco     = $empresas[$empresaKey]['endereco'] ?? '';
$site         = $empresas[$empresaKey]['site']     ?? '';

if (! isset($empresas[$empresaKey])) {
    die('Empresa inválida.');
}

$nomeCompleto = trim("$primeiroNome $sobrenome");

/****************** MONTAR VCARD (com CRLF) ******************/
$vcardLines = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    "N:$sobrenome;$primeiroNome;;;",
    "FN:$nomeCompleto",
    "ORG:{$empresas[$empresaKey]['nome']}",
    "TITLE:$cargo",
    "TEL;TYPE=CELL:$telefone",
    "EMAIL:$email",
    "ADR;TYPE=WORK:;;{$endereco};;;;",
    "URL:{$site}",
    'END:VCARD',
];
$vcard = implode("\r\n", $vcardLines) . "\r\n";

/****************** GERAR QR CODE (com margem e tamanho dinâmico) ******************/
$len = strlen($vcard);
if ($len > 300) {
    $qrSide = 200;
} elseif ($len > 200) {
    $qrSide = 180;
} else {
    $qrSide = 120;
}

$qr = Builder::create()
    ->writer(new PngWriter())
    ->data($vcard)
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
    ->size(300)   // QR inicial em 300×300 px
    ->margin(4)   // quiet-zone mínima
    ->build();

$qrGd = imagecreatefromstring($qr->getString());
if (! $qrGd) {
    die('Falha ao gerar o QR Code.');
}

/****************** CARREGAR A BASE PNG ******************/
$basePath = $empresas[$empresaKey]['base'];
if (! file_exists($basePath)) {
    die('Imagem base não encontrada.');
}

$imagemBase = imagecreatefrompng($basePath);
$width      = imagesx($imagemBase);
$height     = imagesy($imagemBase);

$canvas = imagecreatetruecolor($width, $height);
$white  = imagecolorallocate($canvas, 255, 255, 255);
imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
imagecopy($canvas, $imagemBase, 0, 0, 0, 0, $width, $height);
imagedestroy($imagemBase);

$imagem = $canvas;
imagealphablending($imagem, true);
imagesavealpha($imagem, true);

/****************** REDIMENSIONAR E COLOCAR QR ******************/
$qrSmall = imagescale($qrGd, $qrSide, $qrSide);
imagedestroy($qrGd);

// Agora 5px de margem à direita e 5px do topo
$destX = $width - $qrSide - 5;
$destY = 5;

imagecopy($imagem, $qrSmall, $destX, $destY, 0, 0, $qrSide, $qrSide);
imagedestroy($qrSmall);

/****************** DESENHAR TEXTOS ******************/
// Cores
list($r, $g, $b)    = hexToRgb($empresas[$empresaKey]['cor']);
$corNome            = imagecolorallocate($imagem, $r, $g, $b);
$cinza              = imagecolorallocate($imagem, 128, 128, 128);
list($rt, $gt, $bt) = hexToRgb($empresas[$empresaKey]['cor_telefone']);
$corTelefone        = imagecolorallocate($imagem, $rt, $gt, $bt);

// Fonte
$fontePath = $empresas[$empresaKey]['fonte']
    ?? __DIR__ . '/fonts/liberation-fonts/ttf/LiberationSans-Regular.ttf';
if (! file_exists($fontePath)) {
    die('Fonte não encontrada.');
}

// Layout
$baseX       = 20;
$baseYNome   = 50;
$maxWidth    = 450 - $baseX - 10;
$lineSpacing = 4;

// Nome (font size 20)
$nomeSize  = 20;
$nomeLines = wrapText($imagem, $nomeCompleto, $fontePath, $nomeSize, $maxWidth);
foreach ($nomeLines as $i => $line) {
    $y = $baseYNome + $i * ($nomeSize + $lineSpacing);
    imagettftext($imagem, $nomeSize, 0, $baseX, $y, $corNome, $fontePath, $line);
}

// Cargo (font size 15), sempre abaixo do nome
$cargoSize   = 15;
$cargoLines  = wrapText($imagem, $cargo, $fontePath, $cargoSize, $maxWidth);
$cargoStartY = $baseYNome + count($nomeLines) * ($nomeSize + $lineSpacing) + 10;
foreach ($cargoLines as $i => $line) {
    $y = $cargoStartY + $i * ($cargoSize + $lineSpacing);
    imagettftext($imagem, $cargoSize, 0, $baseX, $y, $cinza, $fontePath, $line);
}

// Telefone e E-mail (fixos)
imagettftext($imagem, 15, 0, 450, 50, $corTelefone, $fontePath, $telefone);
imagettftext($imagem, 15, 0, 450, 80, $cinza,       $fontePath, $email);

/****************** SAÍDA PNG ******************/
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="assinatura.png"');
imagepng($imagem, null, 0);
imagedestroy($imagem);
