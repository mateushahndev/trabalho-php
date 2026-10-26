document.addEventListener('DOMContentLoaded', () => {

  const flashes = document.querySelectorAll('[data-autohide]');
  flashes.forEach((el) => {
    const ms = parseInt(el.dataset.autohide, 10) || 6000;
    setTimeout(() => {
      el.classList.add('flash-hidden');
      setTimeout(() => el.remove(), 450);
    }, ms);
  });

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

  const bsBtn   = document.getElementById('btn-bs-preview');
  const bsPanel = document.getElementById('bs-preview');
  if (bsBtn && bsPanel) {
    bsBtn.addEventListener('click', () => {
      const request = new XMLHttpRequest();
      const params  = [
        'ativo='  + encodeURIComponent(document.getElementById('ativo').value),
        'tipo='   + encodeURIComponent(document.getElementById('tipo_opcao').value),
        'strike=' + encodeURIComponent(document.getElementById('preco_exercicio').value),
        'vencto=' + encodeURIComponent(document.getElementById('data_vencto').value),
        'merc='   + encodeURIComponent(document.getElementById('preco_atual').value)
      ].join('&');

      bsPanel.hidden = false;
      bsBtn.setAttribute('aria-expanded', 'true');

      request.open('GET', '/preview-bs?' + params, true);
      request.onload = () => {
        if (request.status === 200) {
          bsPanel.innerHTML = request.responseText;
        } else {
          bsPanel.innerHTML = '<p class="empty">Não foi possível carregar a análise BS. Tente novamente.</p>';
        }
      };
      request.onerror = () => {
        bsPanel.innerHTML = '<p class="empty">Falha de rede ao calcular a análise BS.</p>';
      };
      request.send();

      bsPanel.animate(
        [{ opacity: 0, transform: 'translateY(-6px)' }, { opacity: 1, transform: 'none' }],
        { duration: 300, easing: 'ease-out' }
      );
    });
  }

  document.querySelectorAll('.price-chart').forEach((canvas) => {
    let prices = [];
    try {
      prices = JSON.parse(canvas.getAttribute('data-closes') || '[]');
    } catch {}
    if (!Array.isArray(prices) || prices.length < 2) {
      return;
    }

    const width  = canvas.getAttribute('width')  || 1000;
    const height = canvas.getAttribute('height') || 250;
    const spX    = width / (prices.length - 1);
    const maxP   = prices.reduce((a, b) => Math.max(a, b), Number.NEGATIVE_INFINITY);
    const scale  = maxP > 0 ? height / maxP : 1;

    const context = canvas.getContext('2d');
    context.lineWidth = 2;
    context.strokeStyle = prices[prices.length - 1] < prices[0] ? 'red' : 'green';

    const toY = (p) => height - p * scale;

    context.beginPath();
    context.moveTo(0, toY(prices[0]));
    for (let i = 1; i < prices.length; i++) {
      context.lineTo(i * spX, toY(prices[i]));
    }
    context.stroke();
  });

});
