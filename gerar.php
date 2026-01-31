<?php

/**
 * gerar.php
 * - Modo padrão: retorna image/png
 * - Modo AJAX: se POST response=json, retorna JSON com png_base64
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

function normalizePhoneE164(string $phoneRaw, string $country): string
{
    $country = strtoupper(trim($country));
    if ($country !== 'AR' && $country !== 'BR') $country = 'BR';
    $countryCode = ($country === 'AR') ? '54' : '55';

    $digits = preg_replace('/\D+/', '', $phoneRaw) ?? '';
    $digits = trim($digits);
    if ($digits === '') return '';

    if (strpos($digits, '00') === 0 && strlen($digits) > 2) {
        $digits = substr($digits, 2);
    }

    if (strpos($digits, $countryCode) === 0) {
        $digits = substr($digits, strlen($countryCode));
    }

    $digits = ltrim($digits, '0');
    if ($digits === '') return '';

    return '+' . $countryCode . $digits;
}

function wrapText($image, string $text, string $fontFile, int $fontSize, int $maxWidth): array
{
    if ($maxWidth <= 0 || trim($text) === '') {
        return [$text];
    }

    $measure = function (string $s) use ($fontFile, $fontSize): int {
        $box = imagettfbbox($fontSize, 0, $fontFile, $s);
        return $box[2] - $box[0];
    };

    $words = preg_split('/\s+/', $text);
    $lines = [];
    $current = '';

    $splitLongToken = function (string $token) use ($measure, $maxWidth): array {
        $chars = preg_split('//u', $token, -1, PREG_SPLIT_NO_EMPTY);
        $parts = [];
        $run = '';
        foreach ($chars as $ch) {
            $candidate = $run . $ch;
            if ($measure($candidate) > $maxWidth) {
                if ($run !== '') {
                    $parts[] = $run;
                    $run = $ch;
                } else {
                    $parts[] = $ch;
                    $run = '';
                }
            } else {
                $run = $candidate;
            }
        }
        if ($run !== '') $parts[] = $run;
        return $parts;
    };

    $i = 0;
    while ($i < count($words)) {
        $word = $words[$i];

        $test = ($current === '') ? $word : ($current . ' ' . $word);
        if ($measure($test) <= $maxWidth) {
            $current = $test;
            $i++;
            continue;
        }

        if ($current !== '') {
            $lines[] = $current;
            $current = '';
            continue;
        }

        $chunks = $splitLongToken($word);
        foreach ($chunks as $chunk) {
            if ($current === '') {
                $current = $chunk;
            } else {
                $cand = $current . ' ' . $chunk;
                if ($measure($cand) <= $maxWidth) {
                    $current = $cand;
                } else {
                    $lines[] = $current;
                    $current = $chunk;
                }
            }
        }
        $i++;
    }

    if ($current !== '') $lines[] = $current;

    return $lines;
}

function jsonError(string $message, int $status = 400): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$empresas     = require __DIR__ . '/empresas.php';

$empresaKey   = $_POST['empresa'] ?? '';
$primeiroNome = trim($_POST['primeiro_nome'] ?? '');
$sobrenome    = trim($_POST['sobrenome'] ?? '');
$cargo        = trim($_POST['cargo'] ?? '');
$email        = trim($_POST['email'] ?? '');
$telefoneUI   = trim($_POST['telefone'] ?? '');
$pais         = strtoupper(trim($_POST['pais'] ?? 'BR'));
$responseMode = strtolower(trim($_POST['response'] ?? ''));

if ($pais !== 'AR' && $pais !== 'BR') $pais = 'BR';

if (!isset($empresas[$empresaKey])) {
    if ($responseMode === 'json') jsonError('Empresa inválida.');
    die('Empresa inválida.');
}

$endereco = $empresas[$empresaKey]['endereco'] ?? '';
$site     = $empresas[$empresaKey]['site'] ?? '';
$nomeCompleto = trim("$primeiroNome $sobrenome");

$telefoneVcard = normalizePhoneE164($telefoneUI, $pais);
if ($telefoneVcard === '') {
    if ($responseMode === 'json') jsonError('Telefone inválido.');
    die('Telefone inválido.');
}

/** ==== vCard (QR) ==== */
$vcardLines = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    "N:$sobrenome;$primeiroNome;;;",
    "FN:$nomeCompleto",
    "ORG:{$empresas[$empresaKey]['nome']}",
    "TITLE:$cargo",
    "TEL;TYPE=CELL:$telefoneVcard",
    "EMAIL:$email",
    "ADR;TYPE=WORK:;;{$endereco};;;;",
    "URL:{$site}",
    'END:VCARD',
];
$vcard = implode("\r\n", $vcardLines) . "\r\n";

$qrSide = 140;

$qr = Builder::create()
    ->writer(new PngWriter())
    ->data($vcard)
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
    ->size(300)
    ->margin(4)
    ->build();

$qrGd = imagecreatefromstring($qr->getString());
if (!$qrGd) {
    if ($responseMode === 'json') jsonError('Falha ao gerar o QR Code.', 500);
    die('Falha ao gerar o QR Code.');
}

/** ==== Base ==== */
$basePath = $empresas[$empresaKey]['base'];
if (!file_exists($basePath)) {
    if ($responseMode === 'json') jsonError('Imagem base não encontrada.', 500);
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

/** ==== QR no canto superior direito ==== */
$qrSmall = imagescale($qrGd, $qrSide, $qrSide);
imagedestroy($qrGd);

$destX = $width - $qrSide - 5;
$destY = 5;
imagecopy($imagem, $qrSmall, $destX, $destY, 0, 0, $qrSide, $qrSide);
imagedestroy($qrSmall);

/** ==== FONTES ==== */
$fonteRegular = $empresas[$empresaKey]['fonte']
    ?? __DIR__ . '/fonts/liberation-fonts/ttf/LiberationSans-Regular.ttf';

$fonteBold = __DIR__ . '/fonts/liberation-fonts/ttf/LiberationSans-Bold.ttf';
if (!file_exists($fonteBold)) {
    $fonteBold = __DIR__ . '/fonts/intelo/Intelo-Bold.ttf';
    if (!file_exists($fonteBold)) {
        if ($responseMode === 'json') jsonError('Fonte bold não encontrada.', 500);
        die('Fonte bold não encontrada.');
    }
}

if (!file_exists($fonteRegular)) {
    if ($responseMode === 'json') jsonError('Fonte regular não encontrada.', 500);
    die('Fonte regular não encontrada.');
}

/** ==== CORES ==== */
list($r, $g, $b)    = hexToRgb($empresas[$empresaKey]['cor']);
$corNome            = imagecolorallocate($imagem, $r, $g, $b);
$cinza              = imagecolorallocate($imagem, 128, 128, 128);
list($rt, $gt, $bt) = hexToRgb($empresas[$empresaKey]['cor_telefone']);
$corTelefone        = imagecolorallocate($imagem, $rt, $gt, $bt);

/** ==== POSICIONAMENTO ==== */
$baseX       = 20;
$topY        = 50;
$lineSpacing = 4;

$qrLeftEdge  = $destX;
$colGap      = 10;

$targetRightX   = 420;
$minRightWidth  = 260;
$minLeftWidth   = 340;

$rightColX = $targetRightX;
$rightColX = min($rightColX, $qrLeftEdge - $minRightWidth - $colGap);
$rightColX = max($rightColX, $baseX + $minLeftWidth);

$leftMaxWidth  = max(60, $rightColX - $baseX - $colGap);
$rightMaxWidth = max(60, $qrLeftEdge - $rightColX - $colGap);

$nomeSize       = 20;
$cargoSize      = 15;
$rightPhoneSize = 14;
$rightEmailSize = 13;

// NOME
$nomeLines = wrapText($imagem, $nomeCompleto, $fonteBold, $nomeSize, $leftMaxWidth);
foreach ($nomeLines as $i => $line) {
    $y = $topY + $i * ($nomeSize + $lineSpacing);
    imagettftext($imagem, $nomeSize, 0, $baseX, $y, $corNome, $fonteBold, $line);
}

// CARGO
$cargoStartY = $topY + count($nomeLines) * ($nomeSize + $lineSpacing) + 10;
$cargoLines  = wrapText($imagem, $cargo, $fonteRegular, $cargoSize, $leftMaxWidth);
foreach ($cargoLines as $i => $line) {
    $y = $cargoStartY + $i * ($cargoSize + $lineSpacing);
    imagettftext($imagem, $cargoSize, 0, $baseX, $y, $cinza, $fonteRegular, $line);
}

// CONTATO (imagem)
$telLines   = wrapText($imagem, $telefoneUI, $fonteRegular, $rightPhoneSize, $rightMaxWidth);
$emailLines = wrapText($imagem, $email,      $fonteRegular, $rightEmailSize, $rightMaxWidth);

$y = $topY;
foreach ($telLines as $line) {
    imagettftext($imagem, $rightPhoneSize, 0, $rightColX, $y, $corTelefone, $fonteRegular, $line);
    $y += $rightPhoneSize + $lineSpacing;
}

$y += 10;
foreach ($emailLines as $line) {
    imagettftext($imagem, $rightEmailSize, 0, $rightColX, $y, $cinza, $fonteRegular, $line);
    $y += $rightEmailSize + $lineSpacing;
}

/** ==== SAÍDA ==== */
$filename = 'assinatura.png';

if ($responseMode === 'json') {
    ob_start();
    imagepng($imagem, null, 0);
    $pngData = ob_get_clean();
    imagedestroy($imagem);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'filename' => $filename,
        'png_base64' => base64_encode($pngData),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// PNG direto (fallback)
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="' . $filename . '"');
imagepng($imagem, null, 0);
imagedestroy($imagem);
