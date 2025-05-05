<!-- index.php -->
<?php
$empresas = require __DIR__ . '/empresas.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Assinaturas</title>
  <link rel="stylesheet" href="css/style.css">
  <script defer src="js/main.js"></script>
</head>

<body>
  <h2>Selecione sua Empresa</h2>
  <div class="cards-container">
    <?php foreach ($empresas as $key => $empresa): ?>
      <button
        type="button"
        class="card"
        data-modal-id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
        aria-label="Selecionar <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>">
        <img
          src="<?= htmlspecialchars($empresa['logo'], ENT_QUOTES, 'UTF-8') ?>"
          alt="<?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?> Logo"
          class="logo">
      </button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($empresas as $key => $empresa): ?>
    <div class="modal" id="modal-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true">
      <div class="modal-content" role="dialog" aria-labelledby="modal-title-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
        <button class="close" aria-label="Fechar">&times;</button>
        <h3 id="modal-title-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>
        </h3>
        <form action="gerar.php" method="POST" target="_blank">
          <input type="hidden" name="empresa" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">

          <label for="nome-<?= $key ?>">Nome</label>
          <input type="text" id="nome-<?= $key ?>" name="nome" required>

          <label for="cargo-<?= $key ?>">Cargo</label>
          <input type="text" id="cargo-<?= $key ?>" name="cargo" required>

          <label for="email-<?= $key ?>">Email</label>
          <input type="email" id="email-<?= $key ?>" name="email" required>

          <label for="telefone-<?= $key ?>">Telefone</label>
          <input type="tel" id="telefone-<?= $key ?>" name="telefone" required>

          <img
            src="<?= htmlspecialchars($empresa['base'], ENT_QUOTES, 'UTF-8') ?>"
            alt="Preview da base de <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>"
            class="base-preview">

          <div class="actions">
            <button type="submit">Gerar Assinatura</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</body>

</html>