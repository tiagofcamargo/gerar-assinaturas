<?php

function hexToRgb($hex) {
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

// Converter cor personalizada da empresa
$corHex = $empresas[$empresaKey]['cor'] ?? '#000000';
list($r, $g, $b) = hexToRgb($corHex);
$corEmpresa = imagecolorallocate($imagem, $r, $g, $b);

$cinza = imagecolorallocate($imagem, 128, 128, 128);  // Cor cinza para o texto


$corTelefoneHex = $empresas[$empresaKey]['cor_telefone'] ?? '#000000';
list($rTel, $gTel, $bTel) = hexToRgb($corTelefoneHex);
$corTelefone = imagecolorallocate($imagem, $rTel, $gTel, $bTel);

// Definir as fontes e tamanhos
$fontePath = $empresas[$empresaKey]['fonte'] ?? './fonts/liberation-fonts/ttf/LiberationSans-Regular.ttf';// Certifique-se de que a fonte TTF esteja no caminho correto
if (!file_exists($fontePath)) {
    die('Fonte não encontrada.');
}

// Inserir nome e e-mail com posições ajustáveis
$tamanhoFonte = 20;
$nomePosX = 20;
$nomePosY = 50;

$cargoPosX = 20;
$cargoPosY = 80;

$telefonePosX = 450;
$telefonePosY = 50;

$emailPosX = 450;
$emailPosY = 80;

// Utilizando TrueType font para maior flexibilidade e controle sobre a aparência do texto
imagettftext($imagem, $tamanhoFonte, 0, $nomePosX, $nomePosY, $corEmpresa, $fontePath, $nome);
imagettftext($imagem, 15, 0, $cargoPosX, $cargoPosY, $cinza, $fontePath, $cargo);
imagettftext($imagem, 15, 0, $telefonePosX, $telefonePosY, $corTelefone, $fontePath, $telefone);
imagettftext($imagem, 15, 0, $emailPosX, $emailPosY, $cinza, $fontePath, $email);


// Salvar a imagem gerada temporariamente
$tempImagePath = '/tmp/assinatura_temp.png';
imagepng($imagem, $tempImagePath);

// Otimizar a imagem com pngcrush
exec("pngcrush -rem allb -reduce $tempImagePath /tmp/assinatura_optimized.png");

// Exibir a imagem otimizada
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="assinatura.png"');

// Ler a imagem otimizada
readfile('/tmp/assinatura_optimized.png');

// Limpeza
imagedestroy($imagem);
unlink($tempImagePath); // Excluir a imagem temporária
unlink('/tmp/assinatura_optimized.png'); // Excluir a imagem otimizada
?>
