document.addEventListener('DOMContentLoaded', () => {
  carregarEstatisticas();
  configurarFiltros();
  carregarPerfis({ page: 1 });
});

// Carrega estatísticas agregadas e atualiza o dashboard e o gráfico
function carregarEstatisticas() {
  fetch('api/estatisticas.php')
    .then((res) => res.json())
    .then((data) => {
      // Atualizar contadores
      document.getElementById('total-perfis').textContent = data.total;
      // Número de áreas distintas
      document.getElementById('total-areas').textContent = Object.keys(data.topAreas).length;
      // Países distintos
      document.getElementById('total-paises').textContent = Object.keys(data.topPaises).length;
      // Continentes distintos
      document.getElementById('total-continentes').textContent = Object.keys(data.porContinente).length;
      criarGrafico(data.porContinente);
    })
    .catch((err) => console.error(err));
}

// Cria um gráfico de barras utilizando Chart.js com dados de continentes
let chart;
function criarGrafico(porContinente) {
  const ctx = document.getElementById('chartDistribuicao').getContext('2d');
  const labels = Object.keys(porContinente);
  const values = Object.values(porContinente);
  if (chart) {
    chart.destroy();
  }
  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Número de perfis',
          data: values,
          // Utilizar cor temática roxa mais vibrante para as barras
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

// Configura eventos para filtros de busca e seleção de continentes
function configurarFiltros() {
  const searchInput = document.getElementById('search-input');
  const searchBtn = document.getElementById('btn-search');
  searchBtn.addEventListener('click', () => {
    const termo = searchInput.value.trim();
    carregarPerfis({ search: termo, page: 1 });
  });
  searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      const termo = searchInput.value.trim();
      carregarPerfis({ search: termo, page: 1 });
    }
  });
  // Filtros de continente
  document.querySelectorAll('.filtro-continente').forEach((btn) => {
    btn.addEventListener('click', () => {
      const continente = btn.dataset.continente;
      carregarPerfis({ continente: continente, page: 1 });
    });
  });
  // Botão de carregar mais
  const loadMoreBtn = document.getElementById('btn-load-more');
  loadMoreBtn.addEventListener('click', () => {
    const container = document.getElementById('lista-perfis');
    const paginaAtual = parseInt(container.dataset.page || '1');
    const termo = searchInput.value.trim();
    const continente = document.querySelector('.filtro-continente.active')?.dataset.continente || '';
    carregarPerfis({ search: termo, continente: continente, page: paginaAtual + 1, append: true });
  });
}

// Carrega perfis via API com filtros opcionais
function carregarPerfis({ search = '', continente = '', page = 1, append = false }) {
  const params = new URLSearchParams();
  if (search) params.set('search', search);
  if (continente) params.set('continente', continente);
  params.set('page', page);
  params.set('limit', 20);
  fetch('api/perfis.php?' + params.toString())
    .then((res) => res.json())
    .then((data) => {
      const container = document.getElementById('lista-perfis');
      container.dataset.page = page;
      if (!append) {
        container.innerHTML = '';
      }
      data.items.forEach((p) => {
        const card = document.createElement('div');
        card.className = 'perfil-card';
        // Encurtar biografia se for muito longa
        const bio = p.biografia.length > 120 ? p.biografia.substring(0, 117) + '…' : p.biografia;
        const paises = Array.isArray(p.pais) ? p.pais.join(', ') : p.pais;
        card.innerHTML = `
          <h3>${p.nome}</h3>
          <p><strong>País:</strong> ${paises}</p>
          <p><strong>Continente:</strong> ${p.continente}</p>
          <p>${bio}</p>
          ${p.fonte ? `<p><a href="${p.fonte}" target="_blank" rel="noopener noreferrer">Fonte</a></p>` : ''}
        `;
        container.appendChild(card);
      });
      // Mostrar ou esconder botão "Carregar mais"
      const loadMoreBtn = document.getElementById('btn-load-more');
      if (data.total > page * 20) {
        loadMoreBtn.style.display = 'block';
      } else {
        loadMoreBtn.style.display = 'none';
      }
    })
    .catch((err) => console.error(err));
}