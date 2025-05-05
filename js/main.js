// js/main.js
document.addEventListener('DOMContentLoaded', () => {
  // abrir modal
  document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', () => {
      const id = card.dataset.modalId;
      const modal = document.getElementById(`modal-${id}`);
      modal.classList.add('ativo');
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  // fechar modal ao clicar no X
  document.querySelectorAll('.modal .close').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal');
      modal.classList.remove('ativo');
      modal.setAttribute('aria-hidden', 'true');
    });
  });

  // fechar modal ao clicar fora do conteúdo
  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('ativo');
        modal.setAttribute('aria-hidden', 'true');
      }
    });
  });

  // máscara de telefone
  function maskTelefone(event) {
    let v = event.target.value.replace(/\D/g, '');

    // determina se deve usar 8 ou 9 dígitos
    if (v.length > 10) {
      // (99) 9 9999-9999
      v = v.replace(/^(\d{2})(\d)(\d{4})(\d{0,4}).*/, '($1) $2 $3-$4');
    } else {
      // (99) 9999-9999
      v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
    }

    event.target.value = v;
  }

  document.querySelectorAll('input[type="tel"]').forEach(input => {
    input.addEventListener('input', maskTelefone);
    input.addEventListener('blur', maskTelefone); // garante formatação ao sair do campo
  });
});
