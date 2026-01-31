// js/main.js
document.addEventListener('DOMContentLoaded', () => {
  /** =========================
   * MODAL: abrir/fechar
   * ========================= */
  document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', () => {
      const id = card.dataset.modalId;
      const modal = document.getElementById(`modal-${id}`);
      if (!modal) return;

      resetModalState(modal);
      resetMercoviaIfNeeded(modal);

      modal.classList.add('ativo');
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  document.querySelectorAll('.modal .close').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal');
      if (!setModalHidden(modal)) return;
    });
  });

  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) setModalHidden(modal);
    });
  });

  function setModalHidden(modal) {
    if (!modal) return false;
    modal.classList.remove('ativo');
    modal.setAttribute('aria-hidden', 'true');
    return true;
  }

  /** =========================
   * Helpers UI
   * ========================= */
  function $(root, sel) { return root.querySelector(sel); }
  function show(el) { if (el) el.hidden = false; }
  function hide(el) { if (el) el.hidden = true; }

  function setError(modal, message) {
    const box = $(modal, '[data-form-error]');
    if (!box) return;
    if (!message) {
      hide(box);
      box.textContent = '';
      return;
    }
    box.textContent = message;
    show(box);
  }

  function resetModalState(modal) {
    const form = $(modal, 'form[data-ajax="1"]');
    const result = $(modal, '[data-result]');
    const img = $(modal, '[data-result-img]');
    const downloadBtn = $(modal, '[data-download]');
    const btnSubmit = $(modal, 'button[type="submit"]');

    // limpa resultado
    if (result) hide(result);

    // volta o form
    if (form) form.style.display = '';

    // limpa preview / download
    if (img) img.removeAttribute('src');
    if (downloadBtn) {
      delete downloadBtn.dataset.downloadUrl;
      delete downloadBtn.dataset.downloadName;
    }

    // limpa erro
    setError(modal, '');

    // reseta botão submit
    if (btnSubmit) {
      btnSubmit.disabled = false;
      btnSubmit.classList.remove('is-loading');
      btnSubmit.removeAttribute('aria-busy');

      if (!btnSubmit.dataset.originalLabel) {
        btnSubmit.dataset.originalLabel = btnSubmit.textContent.trim() || 'Gerar Assinatura';
      }
      btnSubmit.textContent = btnSubmit.dataset.labelSubmit || btnSubmit.dataset.originalLabel || 'Gerar Assinatura';
    }
  }

  /** =========================
   * MÁSCARAS DE TELEFONE
   * ========================= */
  function onlyDigits(value) {
    return (value || '').replace(/\D/g, '');
  }

  function maskBR(raw) {
    let v = onlyDigits(raw).slice(0, 11);
    if (v.length > 10) {
      v = v.replace(/^(\d{2})(\d)(\d{4})(\d{0,4}).*/, '($1) $2 $3-$4');
    } else {
      v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
    }
    return v;
  }

  function maskAR(raw) {
    let d = onlyDigits(raw).slice(0, 11);

    if (d.length <= 10) {
      return d.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, (m, a, b, c) => {
        let out = '';
        if (a) out += a;
        if (b) out += (out ? ' ' : '') + b;
        if (c) out += (b ? '-' : '') + c;
        return out;
      });
    }

    return d.replace(/^(\d{0,3})(\d{0,4})(\d{0,4}).*/, (m, a, b, c) => {
      let out = '';
      if (a) out += a;
      if (b) out += (out ? ' ' : '') + b;
      if (c) out += (b ? '-' : '') + c;
      return out;
    });
  }

  function applyMaskToInput(input) {
    if (!input) return;
    const country = (input.dataset.country || 'BR').toUpperCase();
    input.value = (country === 'AR') ? maskAR(input.value) : maskBR(input.value);
  }

  function attachMaskHandlers(input) {
    if (!input) return;
    if (input._maskBound) return;
    input._maskBound = true;

    input.addEventListener('input', () => applyMaskToInput(input));
    input.addEventListener('blur', () => applyMaskToInput(input));
  }

  document.querySelectorAll('input[type="tel"]').forEach(input => {
    input.dataset.country = 'BR';
    attachMaskHandlers(input);
  });

  /** =========================
   * MERCOVIA: idioma
   * ========================= */
  const i18n = {
    BR: {
      question: 'Você é funcionário de qual país?',
      labels: {
        primeiro_nome: 'Primeiro nome',
        sobrenome: 'Sobrenome',
        cargo: 'Cargo',
        email: 'Email',
        telefone: 'Telefone',
        submit: 'Gerar Assinatura',
        submitting: 'Gerando…',
      },
      phonePlaceholder: '(11) 9 1234-5678',
    },
    AR: {
      question: '¿Eres empleado de qué país?',
      labels: {
        primeiro_nome: 'Nombre',
        sobrenome: 'Apellido',
        cargo: 'Cargo',
        email: 'Correo',
        telefone: 'Teléfono',
        submit: 'Generar firma',
        submitting: 'Generando…',
      },
      phonePlaceholder: '11 1234-5678',
    }
  };

  function setMercoviaLanguage(modal, country) {
    const t = i18n[country] || i18n.BR;

    const q = modal.querySelector('.country-title');
    if (q) q.textContent = t.question;

    const inputPrimeiro = modal.querySelector('input[name="primeiro_nome"]');
    const inputSobrenome = modal.querySelector('input[name="sobrenome"]');
    const inputCargo = modal.querySelector('input[name="cargo"]');
    const inputEmail = modal.querySelector('input[name="email"]');
    const inputTelefone = modal.querySelector('input[name="telefone"]');
    const btnSubmit = modal.querySelector('button[type="submit"]');

    function setLabelFor(input, text) {
      if (!input) return;
      const label = modal.querySelector(`label[for="${input.id}"]`);
      if (label) label.textContent = text;
    }

    setLabelFor(inputPrimeiro, t.labels.primeiro_nome);
    setLabelFor(inputSobrenome, t.labels.sobrenome);
    setLabelFor(inputCargo, t.labels.cargo);
    setLabelFor(inputEmail, t.labels.email);
    setLabelFor(inputTelefone, t.labels.telefone);

    if (btnSubmit) {
      if (!btnSubmit.dataset.originalLabel) {
        btnSubmit.dataset.originalLabel = btnSubmit.textContent.trim() || 'Gerar Assinatura';
      }
      btnSubmit.dataset.labelSubmit = t.labels.submit;
      btnSubmit.dataset.labelSubmitting = t.labels.submitting;

      if (!btnSubmit.classList.contains('is-loading')) {
        btnSubmit.textContent = t.labels.submit;
      }
    }

    if (inputTelefone) {
      inputTelefone.placeholder = t.phonePlaceholder;
      inputTelefone.dataset.country = country;
      attachMaskHandlers(inputTelefone);
      applyMaskToInput(inputTelefone);
    }

    const hidden = modal.querySelector('input[name="pais"]');
    if (hidden) hidden.value = country;
  }

  function showMercoviaForm(modal) {
    const step = modal.querySelector('[data-country-step="1"]');
    const form = modal.querySelector('form[data-requires-country="1"]');
    if (step) step.style.display = 'none';
    if (form) form.style.display = '';
  }

  function resetMercoviaIfNeeded(modal) {
    const isMercovia = modal.dataset.empresa === 'mercovia';
    if (!isMercovia) return;

    const step = modal.querySelector('[data-country-step="1"]');
    const form = modal.querySelector('form[data-requires-country="1"]');

    if (step) step.style.display = '';
    if (form) form.style.display = 'none';

    const hidden = modal.querySelector('input[name="pais"]');
    if (hidden) hidden.value = '';

    setMercoviaLanguage(modal, 'BR');
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.country-btn');
    if (!btn) return;

    const modal = btn.closest('.modal');
    if (!modal) return;
    if (modal.dataset.empresa !== 'mercovia') return;

    const country = (btn.dataset.country || 'BR').toUpperCase();
    setMercoviaLanguage(modal, country);
    showMercoviaForm(modal);
  });

  /** =========================
   * Submit AJAX -> mostra preview no modal
   * ========================= */
  const blobUrls = new WeakMap(); // modal -> blob url atual

  function b64ToBlob(b64, mime = 'image/png') {
    const binStr = atob(b64);
    const len = binStr.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) bytes[i] = binStr.charCodeAt(i);
    return new Blob([bytes], { type: mime });
  }

  function setButtonLoading(btn, loading) {
    if (!btn) return;

    if (!btn.dataset.originalLabel) {
      btn.dataset.originalLabel = btn.textContent.trim() || 'Gerar Assinatura';
    }

    if (loading) {
      btn.disabled = true;
      btn.classList.add('is-loading');
      btn.setAttribute('aria-busy', 'true');
      btn.textContent = btn.dataset.labelSubmitting || 'Gerando…';
    } else {
      btn.disabled = false;
      btn.classList.remove('is-loading');
      btn.removeAttribute('aria-busy');
      btn.textContent = btn.dataset.labelSubmit || btn.dataset.originalLabel || 'Gerar Assinatura';
    }
  }

  document.querySelectorAll('form[data-ajax="1"]').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const modal = form.closest('.modal');
      const btn = form.querySelector('button[type="submit"]');
      setError(modal, '');

      if (btn && btn.classList.contains('is-loading')) return;
      setButtonLoading(btn, true);

      try {
        const fd = new FormData(form);
        fd.set('response', 'json');

        const res = await fetch(form.action, { method: 'POST', body: fd });
        const data = await res.json().catch(() => null);

        if (!data || !data.ok) {
          const msg = (data && data.error) ? data.error : 'Falha ao gerar assinatura.';
          throw new Error(msg);
        }

        // revoga blob anterior (se existir)
        const prevUrl = blobUrls.get(modal);
        if (prevUrl) URL.revokeObjectURL(prevUrl);

        // cria blob URL
        const pngBlob = b64ToBlob(data.png_base64, 'image/png');
        const url = URL.createObjectURL(pngBlob);
        blobUrls.set(modal, url);

        const result = $(modal, '[data-result]');
        const img = $(modal, '[data-result-img]');
        const downloadBtn = $(modal, '[data-download]');

        if (img) img.src = url;

        if (downloadBtn) {
          downloadBtn.dataset.downloadUrl = url;
          downloadBtn.dataset.downloadName = data.filename || 'assinatura.png';
        }

        // esconde form e mostra resultado
        form.style.display = 'none';
        if (result) show(result);

        setButtonLoading(btn, false);
      } catch (err) {
        setError(modal, err.message || 'Erro ao gerar.');
        setButtonLoading(btn, false);
      }
    });
  });

  // Botões do resultado: download / voltar
  document.addEventListener('click', (e) => {
    const modal = e.target.closest('.modal');
    if (!modal) return;

    // Download
    const dl = e.target.closest('[data-download]');
    if (dl) {
      const url = dl.dataset.downloadUrl;
      const name = dl.dataset.downloadName || 'assinatura.png';
      if (!url) return;

      const a = document.createElement('a');
      a.href = url;
      a.download = name;
      document.body.appendChild(a);
      a.click();
      a.remove();
      return;
    }

    // Voltar / Editar
    const back = e.target.closest('[data-back]');
    if (back) {
      const form = $(modal, 'form[data-ajax="1"]');
      const result = $(modal, '[data-result]');
      if (result) hide(result);
      if (form) form.style.display = '';
      setError(modal, '');
      return;
    }
  });
});
