<?php

function hexToRgb($hex)
{
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

$empresas = require 'empresas.php';

$empresaKey = $_POST['empresa'] ?? '';
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$cargo = $_POST['cargo'] ?? '';
$telefone = $_POST['telefone'] ?? '';

if (!isset($empresas[$empresaKey])) {
    die('Empresa inválida.');
}

$basePath = $empresas[$empresaKey]['base'];

if (!file_exists($basePath)) {
    die('Imagem base não encontrada.');
}

// Criar a imagem a partir do arquivo base PNG
$imagem = @imagecreatefrompng($basePath);
if (!$imagem) {
    die('Falha ao carregar a imagem base.');
}

// Preservar transparência total e qualidade
imagealphablending($imagem, true); // Ativar mistura de cores (necessário para texto)
imagesavealpha($imagem, true);     // Preserva o canal alpha (transparência)

// Converter cor personalizada da empresa
$corHex = $empresas[$empresaKey]['cor'] ?? '#000000';
list($r, $g, $b) = hexToRgb($corHex);
$corEmpresa = imagecolorallocate($imagem, $r, $g, $b);

$cinza = imagecolorallocate($imagem, 128, 128, 128);

$corTelefoneHex = $empresas[$empresaKey]['cor_telefone'] ?? '#000000';
list($rTel, $gTel, $bTel) = hexToRgb($corTelefoneHex);
$corTelefone = imagecolorallocate($imagem, $rTel, $gTel, $bTel);

// Fonte
$fontePath = $empresas[$empresaKey]['fonte'] ?? './fonts/liberation-fonts/ttf/LiberationSans-Regular.ttf';
if (!file_exists($fontePath)) {
    die('Fonte não encontrada.');
}

// Inserir os textos
$tamanhoFonte = 20;
imagettftext($imagem, $tamanhoFonte, 0, 20, 50, $corEmpresa, $fontePath, $nome);
imagettftext($imagem, 15, 0, 20, 80, $cinza, $fontePath, $cargo);
imagettftext($imagem, 15, 0, 450, 50, $corTelefone, $fontePath, $telefone);
imagettftext($imagem, 15, 0, 450, 80, $cinza, $fontePath, $email);

// Enviar imagem diretamente com qualidade total
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="assinatura.png"');
imagepng($imagem, null, 0); // 0 = compressão mínima, qualidade máxima

imagedestroy($imagem);
