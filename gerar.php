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

/**
 * Quebra texto respeitando largura máxima (suporta tokens longos sem espaços, p.ex. e-mails).
 * Retorna um array de linhas para desenhar com imagettftext.
 */
function wrapText($image, string $text, string $fontFile, int $fontSize, int $maxWidth): array
{
    if ($maxWidth <= 0 || trim($text) === '') {
        return [$text];
    }

    $measure = function (string $s) use ($fontFile, $fontSize): int {
        $box = imagettfbbox($fontSize, 0, $fontFile, $s);
        return $box[2] - $box[0];
    };

    // divide por espaços, mas vamos tratar palavras muito longas
    $words = preg_split('/\s+/', $text);
    $lines = [];
    $current = '';

    $splitLongToken = function (string $token) use ($measure, $maxWidth): array {
        // Divide token por caracteres para respeitar $maxWidth
        // (ASCII já resolve bem e-mails/telefones)
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
                    // caractere sozinho maior que maxWidth (praticamente impossível)
                    $parts[] = $ch;
                    $run = '';
                }
            } else {
                $run = $candidate;
            }
        }
        if ($run !== '') {
            $parts[] = $run;
        }
        return $parts;
    };

    $i = 0;
    while ($i < count($words)) {
        $word = $words[$i];

        // Tenta adicionar a palavra atual à linha
        $test = ($current === '') ? $word : ($current . ' ' . $word);
        if ($measure($test) <= $maxWidth) {
            $current = $test;
            $i++;
            continue;
        }

        // Se já temos algo na linha, fecha a linha e tenta de novo
        if ($current !== '') {
            $lines[] = $current;
            $current = '';
            continue;
        }

        // A palavra sozinha já é grande demais: dividir em pedaços
        $chunks = $splitLongToken($word); // cada chunk cabe em uma linha
        foreach ($chunks as $idx => $chunk) {
            // primeira parte vai para a linha corrente
            if ($current === '') {
                $current = $chunk;
            } else {
                // tenta anexar com espaço; se não couber, quebra
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

/** ==== vCard (QR) ==== */
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
if (! $qrGd) {
    die('Falha ao gerar o QR Code.');
}

/** ==== Base ==== */
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

/** ==== QR no canto superior direito ==== */
$qrSmall = imagescale($qrGd, $qrSide, $qrSide);
imagedestroy($qrGd);

// margem de 5 px
$destX = $width - $qrSide - 5;
$destY = 5;
imagecopy($imagem, $qrSmall, $destX, $destY, 0, 0, $qrSide, $qrSide);
imagedestroy($qrSmall);

/** ==== FONTES ==== */
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

/** ==== CORES ==== */
list($r, $g, $b)    = hexToRgb($empresas[$empresaKey]['cor']);
$corNome            = imagecolorallocate($imagem, $r, $g, $b);
$cinza              = imagecolorallocate($imagem, 128, 128, 128);
list($rt, $gt, $bt) = hexToRgb($empresas[$empresaKey]['cor_telefone']);
$corTelefone        = imagecolorallocate($imagem, $rt, $gt, $bt);

/** =========================================================
 *   POSICIONAMENTO / QUEBRA DE LINHA (coluna esq. e dir.)
 *   - Coluna direita em ~x=420 (mais à esquerda)
 *   - Garante min 260px até o QR e min 340px para a coluna esquerda
 *   - Wrap em nome, cargo, telefone e e-mail (inclusive sem espaços)
 *  ========================================================= */

// parâmetros gerais
$baseX       = 20;   // margem esquerda da coluna esquerda
$topY        = 50;   // Y inicial
$lineSpacing = 4;    // espaçamento entre linhas

// onde começa o QR (borda esquerda)
$qrLeftEdge  = $destX;
$colGap      = 10;   // respiro entre colunas

// alvos e limites
$targetRightX   = 420; // queremos a coluna direita por aqui (mais à esquerda)
$minRightWidth  = 260; // largura mínima da coluna da direita
$minLeftWidth   = 340; // largura mínima para nome/cargo

// coloca a direita em ~420, mas assegura largura até o QR e espaço para a esquerda
$rightColX = $targetRightX;
$rightColX = min($rightColX, $qrLeftEdge - $minRightWidth - $colGap);     // não encostar no QR
$rightColX = max($rightColX, $baseX + $minLeftWidth);                     // não esmagar a coluna esquerda

// larguras úteis p/ wrap
$leftMaxWidth  = max(60, $rightColX - $baseX - $colGap);      // nome/cargo
$rightMaxWidth = max(60, $qrLeftEdge - $rightColX - $colGap); // tel/email

// tamanhos de fonte
$nomeSize       = 20; // destaque
$cargoSize      = 15;
$rightPhoneSize = 14; // um pouco menor
$rightEmailSize = 13; // menor para e-mail

// === NOME (bold) com wrap ===
$nomeLines = wrapText($imagem, $nomeCompleto, $fonteBold, $nomeSize, $leftMaxWidth);
foreach ($nomeLines as $i => $line) {
    $y = $topY + $i * ($nomeSize + $lineSpacing);
    imagettftext($imagem, $nomeSize, 0, $baseX, $y, $corNome, $fonteBold, $line);
}

// === CARGO (regular) com wrap ===
$cargoStartY = $topY + count($nomeLines) * ($nomeSize + $lineSpacing) + 10;
$cargoLines  = wrapText($imagem, $cargo, $fonteRegular, $cargoSize, $leftMaxWidth);
foreach ($cargoLines as $i => $line) {
    $y = $cargoStartY + $i * ($cargoSize + $lineSpacing);
    imagettftext($imagem, $cargoSize, 0, $baseX, $y, $cinza, $fonteRegular, $line);
}

// === CONTATO (coluna direita) com wrap ===
$telLines   = wrapText($imagem, $telefone, $fonteRegular, $rightPhoneSize, $rightMaxWidth);
$emailLines = wrapText($imagem, $email,    $fonteRegular, $rightEmailSize, $rightMaxWidth);

// telefone
$y = $topY;
foreach ($telLines as $line) {
    imagettftext($imagem, $rightPhoneSize, 0, $rightColX, $y, $corTelefone, $fonteRegular, $line);
    $y += $rightPhoneSize + $lineSpacing;
}

// e-mail (um respiro abaixo do telefone)
$y += 10;
foreach ($emailLines as $line) {
    imagettftext($imagem, $rightEmailSize, 0, $rightColX, $y, $cinza, $fonteRegular, $line);
    $y += $rightEmailSize + $lineSpacing;
}

/** ==== SAÍDA ==== */
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="assinatura.png"');
imagepng($imagem, null, 0);
imagedestroy($imagem);
