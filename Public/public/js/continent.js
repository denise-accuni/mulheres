// Script para página de detalhes de continente
document.addEventListener('DOMContentLoaded', () => {
  const continente = document.body.dataset.continent;
  // Dados carregados do servidor para o continente
  let dadosPerfis = [];
  // Carregar globais para calcular porcentagem
  let totalGlobal = 0;

  // Buscar estatísticas globais primeiro
  fetch('api/estatisticas.php')
    .then((res) => res.json())
    .then((data) => {
      totalGlobal = data.total;
    })
    .finally(() => {
      carregarDados();
    });

  // Botões e campos
  const buscaInput = document.getElementById('busca-continente');
  const atualizarBtn = document.getElementById('btn-atualizar');
  atualizarBtn.addEventListener('click', () => carregarDados(true));
  buscaInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      filtrarPerfis(buscaInput.value.trim());
    }
  });

  function carregarDados(forceReload = false) {
    // Se já temos dados e não for recarregar, apenas exibir
    if (dadosPerfis.length > 0 && !forceReload) {
      atualizarVista();
      return;
    }
    fetch(`api/perfis.php?continente=${encodeURIComponent(continente)}&limit=1000`)
      .then((res) => res.json())
      .then((data) => {
        dadosPerfis = data.items || [];
        atualizarVista();
      })
      .catch((err) => console.error(err));
  }

  function atualizarVista() {
    // Atualizar métricas
    const total = dadosPerfis.length;
    document.getElementById('cnt-total').textContent = total;
    // Países distintos
    const paises = {};
    dadosPerfis.forEach((p) => {
      const lista = Array.isArray(p.pais) ? p.pais : [p.pais];
      lista.forEach((pp) => {
        paises[pp] = (paises[pp] || 0) + 1;
      });
    });
    document.getElementById('cnt-paises').textContent = Object.keys(paises).length;
    // Contribuição global
    const percent = totalGlobal ? ((total / totalGlobal) * 100).toFixed(0) : 0;
    document.getElementById('cnt-continente').textContent = percent + '%';
    // Distribuição por país (todos)
    const labels = Object.keys(paises);
    const values = Object.values(paises);
    criarGraficoBarra(labels, values, 'chartPais');
    // Top 5 países
    const entries = Object.entries(paises).sort((a,b) => b[1]-a[1]).slice(0,5);
    const labelsTop = entries.map((e) => e[0]);
    const valuesTop = entries.map((e) => e[1]);
    criarGraficoPizza(labelsTop, valuesTop, 'chartTopPais');
    // Listar perfis
    filtrarPerfis(buscaInput.value.trim());
  }

  function filtrarPerfis(termo) {
    const lista = document.getElementById('lista-perfis-cont');
    lista.innerHTML = '';
    const termoLc = termo ? termo.toLowerCase() : '';
    const filtrados = dadosPerfis.filter((p) => {
      if (!termoLc) return true;
      return (
        p.nome.toLowerCase().includes(termoLc) ||
        (Array.isArray(p.pais) ? p.pais.join(',').toLowerCase().includes(termoLc) : p.pais.toLowerCase().includes(termoLc))
      );
    });
    filtrados.forEach((p) => {
      const card = document.createElement('div');
      card.className = 'perfil-card';
      const bio = p.biografia.length > 120 ? p.biografia.substring(0,117) + '…' : p.biografia;
      const paisesStr = Array.isArray(p.pais) ? p.pais.join(', ') : p.pais;
      card.innerHTML = `
        <h3>${p.nome}</h3>
        <p><strong>País:</strong> ${paisesStr}</p>
        <p>${bio}</p>
        ${p.fonte ? `<p><a href="${p.fonte}" target="_blank" rel="noopener noreferrer">Fonte</a></p>` : ''}
      `;
      lista.appendChild(card);
    });
  }

  // Cria gráfico de barras de distribuição por país
  let chartPais;
  function criarGraficoBarra(labels, values, canvasId) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    if (chartPais) {
      chartPais.destroy();
    }
    chartPais = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Perfis',
            data: values,
            backgroundColor: '#9C27B0',
            borderColor: '#6A1B9A',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }

  // Cria gráfico de pizza para top 5 países
  let chartTop;
  function criarGraficoPizza(labels, values, canvasId) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    if (chartTop) {
      chartTop.destroy();
    }
    const colors = ['#8E24AA','#BA68C8','#CE93D8','#E1BEE7','#F3E5F5'];
    chartTop = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [
          {
            data: values,
            backgroundColor: colors.slice(0, labels.length),
            borderWidth: 1,
            borderColor: '#fff'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  }
});