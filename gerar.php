<?php

/**
 * gerar.php
 * Gera uma assinatura em PNG com QR Code vCard e fundo branco.
 */

require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

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

$len = strlen($vcard);
$qrSide = $len > 300 ? 180 : ($len > 200 ? 130 : 120);

$qr = Builder::create()
    ->writer(new PngWriter())
    ->data($vcard)
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
    ->size(300)
    ->margin(4)
    ->build();

$qrGd = imagecreatefromstring($qr->getString());
if (! $qrGd) {
    die('Falha ao gerar o QR Code.');
}

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

$qrSmall = imagescale($qrGd, $qrSide, $qrSide);
imagedestroy($qrGd);

$destX = $width - $qrSide - 5;
$destY = 5;
imagecopy($imagem, $qrSmall, $destX, $destY, 0, 0, $qrSide, $qrSide);
imagedestroy($qrSmall);

// === FONTES ===
$fonteRegular = $empresas[$empresaKey]['fonte']
    ?? __DIR__ . '/fonts/liberation-fonts/ttf/LiberationSans-Regular.ttf';

// Fonte bold padrão
$fonteBold = __DIR__ . '/fonts/liberation-fonts/ttf/LiberationSans-Bold.ttf';

// Se não existir, tenta a Intelo Bold
if (!file_exists($fonteBold)) {
    $fonteBold = __DIR__ . '/fonts/intelo/Intelo-Bold.ttf';
    if (!file_exists($fonteBold)) {
        die('Fonte bold não encontrada em nenhuma das opções.');
    }
}

if (!file_exists($fonteRegular)) {
    die('Fonte regular não encontrada.');
}

// === CORES ===
list($r, $g, $b)    = hexToRgb($empresas[$empresaKey]['cor']);
$corNome            = imagecolorallocate($imagem, $r, $g, $b);
$cinza              = imagecolorallocate($imagem, 128, 128, 128);
list($rt, $gt, $bt) = hexToRgb($empresas[$empresaKey]['cor_telefone']);
$corTelefone        = imagecolorallocate($imagem, $rt, $gt, $bt);

// === POSICIONAMENTO ===
$baseX       = 20;
$baseYNome   = 50;
$maxWidth    = 450 - $baseX - 10;
$lineSpacing = 4;

// === NOME EM BOLD ===
$nomeSize  = 20;
$nomeLines = wrapText($imagem, $nomeCompleto, $fonteBold, $nomeSize, $maxWidth);
foreach ($nomeLines as $i => $line) {
    $y = $baseYNome + $i * ($nomeSize + $lineSpacing);
    imagettftext($imagem, $nomeSize, 0, $baseX, $y, $corNome, $fonteBold, $line);
}

// === CARGO (regular) ===
$cargoSize   = 15;
$cargoLines  = wrapText($imagem, $cargo, $fonteRegular, $cargoSize, $maxWidth);
$cargoStartY = $baseYNome + count($nomeLines) * ($nomeSize + $lineSpacing) + 10;
foreach ($cargoLines as $i => $line) {
    $y = $cargoStartY + $i * ($cargoSize + $lineSpacing);
    imagettftext($imagem, $cargoSize, 0, $baseX, $y, $cinza, $fonteRegular, $line);
}

// === CONTATO (regular) ===
imagettftext($imagem, 15, 0, 450, 50, $corTelefone, $fonteRegular, $telefone);
imagettftext($imagem, 15, 0, 450, 80, $cinza,       $fonteRegular, $email);

// === SAÍDA ===
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="assinatura.png"');
imagepng($imagem, null, 0);
imagedestroy($imagem);
