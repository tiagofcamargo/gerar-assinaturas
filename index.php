<?php
$empresas = require __DIR__ . '/empresas.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <div
      class="modal"
      id="modal-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
      aria-hidden="true"
      data-empresa="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">

      <div class="modal-content" role="dialog" aria-labelledby="modal-title-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
        <button class="close" aria-label="Fechar">&times;</button>

        <h3 id="modal-title-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>
        </h3>

        <?php if ($key === 'mercovia'): ?>
          <!-- ETAPA EXTRA SOMENTE PARA MERCOVIA -->
          <div class="country-step" data-country-step="1">
            <p class="country-title">Você é funcionário de qual país?</p>

            <div class="country-options">
              <button type="button" class="country-btn" data-country="BR" aria-label="Brasil">
                🇧🇷 <span>Brasil</span>
              </button>

              <button type="button" class="country-btn" data-country="AR" aria-label="Argentina">
                🇦🇷 <span>Argentina</span>
              </button>
            </div>
          </div>
        <?php endif; ?>

        <!-- FORM -->
        <form
          action="gerar.php"
          method="POST"
          <?= ($key === 'mercovia') ? 'data-requires-country="1" style="display:none;"' : '' ?>
          data-ajax="1">

          <input type="hidden" name="empresa" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="pais" value="">
          <input type="hidden" name="response" value="json">

          <label for="primeiro-nome-<?= $key ?>">Primeiro nome</label>
          <input type="text" id="primeiro-nome-<?= $key ?>" name="primeiro_nome" required>

          <label for="sobrenome-<?= $key ?>">Sobrenome</label>
          <input type="text" id="sobrenome-<?= $key ?>" name="sobrenome" required>

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

        <!-- RESULTADO (somente PNG + download) -->
        <div class="result" data-result hidden>
          <p class="result-title">Sua assinatura está pronta</p>

          <div class="result-preview">
            <img data-result-img alt="Prévia da assinatura" />
          </div>

          <div class="result-actions">
            <button type="button" class="btn-secondary" data-back>Editar / Voltar</button>
            <button type="button" class="btn-primary" data-download>Baixar PNG</button>
          </div>
        </div>

        <div class="form-error" data-form-error hidden></div>

      </div>
    </div>
  <?php endforeach; ?>
</body>

</html>
