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

/**
 * Se preferir ocultar avisos de “Deprecated”:
 * error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
 */

/****************** FUNÇÃO AUXILIAR ******************/
function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

/****************** LEITURA DAS ENTRADAS ******************/
$empresas     = require 'empresas.php';

$empresaKey   = $_POST['empresa']        ?? '';
$primeiroNome = trim($_POST['primeiro_nome'] ?? '');
$sobrenome    = trim($_POST['sobrenome']     ?? '');
$cargo        = trim($_POST['cargo']        ?? '');
$email        = trim($_POST['email']        ?? '');
$telefone     = trim($_POST['telefone']     ?? '');

if (! isset($empresas[$empresaKey])) {
    die('Empresa inválida.');
}

$nomeCompleto = trim("$primeiroNome $sobrenome");

/****************** MONTAR VCARD ******************/
$vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:$sobrenome;$primeiroNome;;;
FN:$nomeCompleto
ORG:{$empresas[$empresaKey]['nome']}
TITLE:$cargo
TEL;TYPE=CELL:$telefone
EMAIL:$email
END:VCARD
VCF;

/****************** GERAR QR CODE ******************/
$qr = Builder::create()
    ->writer(new PngWriter())
    ->data($vcard)
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
    ->size(300)   // Tamanho original do QR antes do redimensionamento
    ->margin(0)
    ->build();

// Transforma o PNG gerado em resource GD
$qrGd = imagecreatefromstring($qr->getString());
if (! $qrGd) {
    die('Falha ao gerar o QR Code.');
}

/****************** CARREGAR A BASE PNG ******************/
$basePath = $empresas[$empresaKey]['base'];
if (! file_exists($basePath)) {
    die('Imagem base não encontrada.');
}

$imagemBase = @imagecreatefrompng($basePath);
if (! $imagemBase) {
    die('Falha ao carregar a imagem base.');
}

/**
 * Criar um “canvas branco” do mesmo tamanho da base.
 * Assim, eliminamos transparências na imagem-base.
 */
$larguraBase = imagesx($imagemBase);
$alturaBase  = imagesy($imagemBase);

$canvas = imagecreatetruecolor($larguraBase, $alturaBase);
// Aloca cor branca
$corBranca = imagecolorallocate($canvas, 255, 255, 255);
// Preenche todo o canvas com branco
imagefilledrectangle($canvas, 0, 0, $larguraBase, $alturaBase, $corBranca);

// Copia a base (que pode ter canal alpha) sobre o fundo branco
imagecopy($canvas, $imagemBase, 0, 0, 0, 0, $larguraBase, $alturaBase);
imagedestroy($imagemBase);

// Agora $canvas é a “imagem” principal, com fundo branco
$imagem = $canvas;

// Ativar mistura de cores e preservar canal alpha (caso textos/QR usem transparência)
imagealphablending($imagem, true);
imagesavealpha($imagem, true);

/****************** REDIMENSIONAR E COLOCAR QR ******************/
$qrLado   = 120; // pixels finais do QR dentro da assinatura
$qrSmall  = imagescale($qrGd, $qrLado, $qrLado);
imagedestroy($qrGd);

$destX = imagesx($imagem) - $qrLado - 10; // 10 px de margem direita
$destY = 10;                             // 10 px de margem superior

imagecopy($imagem, $qrSmall, $destX, $destY, 0, 0, $qrLado, $qrLado);
imagedestroy($qrSmall);

/****************** DESENHAR TEXTOS ******************/
// Cor do nome (variável para cada empresa)
list($r, $g, $b) = hexToRgb($empresas[$empresaKey]['cor']);
$corEmpresa      = imagecolorallocate($imagem, $r, $g, $b);

// Cinza para cargos e e-mail
$cinza = imagecolorallocate($imagem, 128, 128, 128);

// Cor do telefone (variável para cada empresa)
list($rt, $gt, $bt) = hexToRgb($empresas[$empresaKey]['cor_telefone']);
$corTelefone      = imagecolorallocate($imagem, $rt, $gt, $bt);

// Caminho para a fonte TTF
$fontePath = $empresas[$empresaKey]['fonte']
    ?? './fonts/liberation-fonts/ttf/LiberationSans-Regular.ttf';
if (! file_exists($fontePath)) {
    die('Fonte não encontrada.');
}

// Escrever Nome (maior), Cargo, Telefone e Email
imagettftext($imagem, 20, 0, 20, 50,  $corEmpresa,   $fontePath, $nomeCompleto);
imagettftext($imagem, 15, 0, 20, 80,  $cinza,        $fontePath, $cargo);
imagettftext($imagem, 15, 0, 450, 50, $corTelefone,  $fontePath, $telefone);
imagettftext($imagem, 15, 0, 450, 80, $cinza,        $fontePath, $email);

/****************** GERAR SAÍDA PNG ******************/
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="assinatura.png"');
imagepng($imagem, null, 0); // compressão 0 = qualidade máxima

imagedestroy($imagem);
