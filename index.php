<?php $empresas = require 'empresas.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Assinaturas</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 30px;
      background-color: #f5f5f5;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    .cards-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
    }

    .card {
      background-color: #eaeaea;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      width: 180px;
    }

    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    img.logo {
      max-width: 100px;
      margin-bottom: 10px;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width:100%; height:100%;
      background:rgba(0,0,0,0.5);
      align-items: center;
      justify-content: center;
      z-index: 999;
    }

    .modal-content {
      background: #fff;
      padding: 30px;
      width: 100%;
      max-width: 500px;
      position: relative;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
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
      width: 100%;
      padding: 8px;
      margin-top: 3px;
      margin-bottom: 15px;
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
  <h2>Selecione a empresa</h2>

  <div class="cards-container">
    <?php foreach ($empresas as $key => $empresa): ?>
      <div class="card" onclick="abrirModal('<?= $key ?>')">
        <img src="<?= $empresa['logo'] ?>" class="logo"><br>
        <strong><?= $empresa['nome'] ?></strong>
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
      document.getElementById('modal-' + id).style.display = 'flex';
    }

    function fecharModal(id) {
      document.getElementById('modal-' + id).style.display = 'none';
    }
  </script>
</body>
</html>
