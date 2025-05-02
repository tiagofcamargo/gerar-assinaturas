<?php $empresas = require 'empresas.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Assinaturas</title>
  <style>
    @font-face {
      font-family: 'Intelo';
      src: url('fonts/intelo/Intelo-Regular.ttf') format('truetype');
      font-weight: 400;
      font-style: normal;
    }

    @font-face {
      font-family: 'Intelo';
      src: url('fonts/intelo/Intelo-Bold.ttf') format('truetype');
      font-weight: 700;
      font-style: normal;
    }

    @font-face {
      font-family: 'Intelo';
      src: url('fonts/intelo/Intelo-Italic.ttf') format('truetype');
      font-weight: 400;
      font-style: italic;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 30px;
      background: #424893;
      background: linear-gradient(144deg, rgba(66, 72, 147, 1) 39%, rgba(213, 124, 49, 1) 100%);
      height: 100vh;
    }

    h2 {
      text-align: center;
      margin: 64px;
      font-family: 'Intelo', sans-serif;
      color: #FFFFFF;
      font-weight: 400;
      font-size: 32px;
    }

    .cards-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      max-width: 60%;
      gap: 20px;
      margin: 0 auto;
    }

    img.logo {
      max-width: 300px;
      transition: ease .3s;
      filter: drop-shadow(1px 1px 3px #cfcfcf);
    }

    img.logo:hover {
      transform: scale(102%);
      cursor: pointer;
    }

    .modal {
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
      z-index: 999;
      display: flex;
    }

    .modal.ativo {
      opacity: 1;
      visibility: visible;
    }

    .modal-content {
      background: #fff;
      padding: 50px;
      width: 100%;
      max-width: 500px;
      position: relative;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .close {
      position: absolute;
      top: 10px;
      right: 15px;
      cursor: pointer;
      font-weight: bold;
      font-size: 18px;
      color: #888;
    }

    .close:hover {
      color: #000;
    }

    label {
      display: block;
      margin-bottom: 10px;
      font-size: 14px;
    }

    input {
      padding: 8px;
      width: 100%;
      margin: 0 auto;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    button {
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 10px 16px;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #45a049;
    }

    img.base-preview {
      max-width: 100%;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <h2>Selecione sua Empresa</h2>

  <div class="cards-container">
    <?php foreach ($empresas as $key => $empresa): ?>
      <div onclick="abrirModal('<?= $key ?>')">
        <img src="<?= $empresa['logo'] ?>" class="logo"><br>
      </div>
    <?php endforeach; ?>
  </div>

  <?php foreach ($empresas as $key => $empresa): ?>
    <div class="modal" id="modal-<?= $key ?>">
      <div class="modal-content">
        <span class="close" onclick="fecharModal('<?= $key ?>')">&times;</span>
        <h3><?= $empresa['nome'] ?></h3>
        <form action="gerar.php" method="POST" target="_blank">
          <input type="hidden" name="empresa" value="<?= $key ?>">
          <label>Nome:<br><input type="text" name="nome" required></label>
          <label>Cargo:<br><input type="text" name="cargo" required></label>
          <label>Email:<br><input type="email" name="email" required></label>
          <label>Telefone:<br><input type="tel" name="telefone" required></label>
          <img src="<?= $empresa['base'] ?>" class="base-preview"><br><br>
          <div style="display: flex; justify-content: center;">
            <button type="submit">Gerar Assinatura</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <script>
    function abrirModal(id) {
      const modal = document.getElementById('modal-' + id);
      modal.classList.add('ativo');
    }

    function fecharModal(id) {
      const modal = document.getElementById('modal-' + id);
      modal.classList.remove('ativo');
    }
  </script>
</body>

</html>