document.addEventListener('DOMContentLoaded', () => {

  // Esconde os flashes
  const flashes = document.querySelectorAll('[data-autohide]');
  flashes.forEach((el) => {
    const ms = 8000;
    setTimeout(() => {
      el.remove();
    }, ms);
  });

  // Confirmação simples
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

});

