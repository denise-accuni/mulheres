<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Observatório Global — Dashboard Geral</title>
<link rel="stylesheet" href="assets/styles.css">
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
  <div class="brand">
    <span style="font-size:22px;">🌐</span>
    <h1>Observatório Global — Dashboard Geral</h1>
  </div>
  <nav class="header-spacer" style="text-align:left;">
    <a href="home.php"><b>Dashboard</b></a>
    <a href="index.php">API Wikidata</a>
    <a href="outras.php">Planilha</a>
  </nav>
  <img class="brand-logo" src="assets/img/logo.png" alt="Mulheres da Computação">
</header>

<div class="wrap">
  <div class="title">Resumo Integrado</div>
  <div class="subtitle">Métricas combinadas da API (Wikidata) e da Planilha local.</div>

  <!-- Filtros rápidos -->
  <div class="toolbar" style="grid-template-columns: 180px 180px 220px 220px;">
    <select id="continent">
      <option>América</option><option>Ásia</option><option>Europa</option><option>África</option><option>Oceania</option>
    </select>
    <select id="selSeculo"><option>Todos</option></select>
    <select id="selArea"><option>Todas</option></select>
    <select id="selEmpresa"><option>Todas</option></select>
  </div>

  <!-- Cards -->
  <div class="stats">
    <div class="tile">
      <div style="font-weight:700;">Total (API/Wikidata)</div>
      <div style="font-size:28px;font-weight:800" id="totalApi">0</div>
      <div class="pill" id="contApi">—</div>
    </div>
    <div class="tile">
      <div style="font-weight:700;">Total (Planilha)</div>
      <div style="font-size:28px;font-weight:800" id="totalCsv">0</div>
      <div class="pill" id="fltCsv">—</div>
    </div>
    <div class="tile">
      <div style="font-weight:700;">Acesso rápido</div>
      <div class="pill" id="status">Pronto</div>
      <div style="margin-top:8px;">
        <a class="more" href="index.php">Abrir página da API ↗</a><br>
        <a class="more" href="outras.php">Abrir página da Planilha ↗</a>
      </div>
    </div>
  </div>

  <!-- Gráficos -->
  <div class="charts">
    <div class="chartbox">
      <div style="font-weight:700;margin:6px 0 8px;">Distribuição por País — API (Top 10)</div>
      <canvas id="chartApi" height="150"></canvas>
    </div>
    <div class="chartbox">
      <div style="font-weight:700;margin:6px 0 8px;">Distribuição por País — Planilha (Top 10)</div>
      <canvas id="chartCsv" height="150"></canvas>
    </div>
  </div>

  <!-- Amostras -->
  <div class="grid" id="gridApi"></div>
  <div class="footer">Amostra da API (30 perfis por requisição) — role na página da API para carregar mais.</div>
  <div class="grid" id="gridCsv" style="margin-top:12px;"></div>
  <div class="footer">Amostra da Planilha (5 perfis) — abra a página da planilha para filtros completos.</div>
</div>

<!-- Rodapé institucional -->
<footer class="footer">
  <div class="inner">
    <div class="title"><b>Produto do Programa de Iniciação Científica da UNIG</b></div>
    <div><b>Nome do orientador:</b> Denise Moraes do Nascimento Vieira</div>
    <div><b>Co-orientação:</b> Selma Sant Anna</div>
    <div style="margin-top:6px;"><b>Participantes:</b></div>
    <ul style="margin:6px 0 0 18px; padding:0;">
      <li>1º Aluna voluntária: Caroline de Souza Cruz</li>
      <li>2º Aluna voluntária: Ana Clara do Nascimento de Oliveira</li>
      <li>3º Aluna voluntária: Caroline Luz de Souza</li>
      <li>4º Aluna voluntária: Daymara Aparecida de Abreu Silva</li>
      <li>5º Aluna voluntária: Mikaella Teixeira da Silva</li>
    </ul>
  </div>
</footer>

<script>
const statusPill = document.getElementById('status');
const continentSelect = document.getElementById('continent');
const selSeculo = document.getElementById('selSeculo');
const selArea = document.getElementById('selArea');
const selEmpresa = document.getElementById('selEmpresa');

const totalApiEl = document.getElementById('totalApi');
const totalCsvEl = document.getElementById('totalCsv');
const contApiEl  = document.getElementById('contApi');
const fltCsvEl   = document.getElementById('fltCsv');

const gridApi = document.getElementById('gridApi');
const gridCsv = document.getElementById('gridCsv');

let chartApi, chartCsv;

function pillLoading(on){ statusPill.textContent = on ? 'Carregando…' : 'Pronto'; statusPill.style.background = on ? '#ffe58a' : '#f1e0ff'; }
function cardHTML(item){
  const img = (item.image || item.imagem) ? `<img class="thumb" src="${item.image || item.imagem}" alt="${item.name || item.nome}">` : `<div class="thumb"></div>`;
  const name = item.name || item.nome || '';
  const country = item.country || item.pais || '';
  const desc = item.desc || item.descricao || '';
  const link = item.link ? `<a class="more" href="${item.link}" target="_blank" rel="noopener">Saiba mais ↗</a>` : '';
  return `<div class="card">${img}<div><div class="name">${name}</div><div class="meta">${country}</div><div class="desc">${desc}</div>${link}</div></div>`;
}
function updateChart(canvasId, ref, obj){
  const entries = Object.entries(obj||{}).sort((a,b)=>b[1]-a[1]).slice(0,10);
  const labels = entries.map(e=>e[0]);
  const values = entries.map(e=>e[1]);
  if (ref) ref.destroy();
  return new Chart(document.getElementById(canvasId).getContext('2d'), {
    type:'bar',
    data:{ labels, datasets:[{ label:'Perfis', data: values }]},
    options:{ plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 }}}}
  });
}

async function loadFacetsCsv(){
  const r = await fetch('outras.php?api=1&page=1&size=1');
  if (!r.ok) return;
  const data = await r.json();
  if (selSeculo.options.length===1) {
    (data.facets?.seculo||[]).forEach(v=> selSeculo.insertAdjacentHTML('beforeend', `<option>${v}</option>`));
    (data.facets?.area||[]).forEach(v=> selArea.insertAdjacentHTML('beforeend', `<option>${v}</option>`));
    (data.facets?.empresa||[]).forEach(v=> selEmpresa.insertAdjacentHTML('beforeend', `<option>${v}</option>`));
  }
}

async function loadAll(){
  try{
    pillLoading(true);
    gridApi.innerHTML=''; gridCsv.innerHTML='';

    // API — stats + amostra (1 página = 30)
    const statsUrl = `index.php?api=stats&continent=${encodeURIComponent(continentSelect.value)}`;
    const rS = await fetch(statsUrl);
    if (rS.ok){
      const dS = await rS.json();
      totalApiEl.textContent = String(dS.total || 0);
      contApiEl.textContent = continentSelect.value;
      chartApi = updateChart('chartApi', chartApi, dS.agg_top10 || {});
    }
    const listUrl = `index.php?api=1&continent=${encodeURIComponent(continentSelect.value)}&page=1&size=30`;
    const rL = await fetch(listUrl);
    if (rL.ok){
      const dL = await rL.json();
      (dL.items||[]).forEach(it => gridApi.insertAdjacentHTML('beforeend', cardHTML(it)));
    }

    // PLANILHA — totais e amostra (5)
    const csvUrl = new URLSearchParams({
      api:'1', page:'1', size:'5',
      seculo: selSeculo.value || 'Todos', area: selArea.value || 'Todas', empresa: selEmpresa.value || 'Todas'
    });
    const rCsv = await fetch('outras.php?'+csvUrl.toString());
    if (rCsv.ok){
      const d = await rCsv.json();
      totalCsvEl.textContent = String(d.total || 0);
      fltCsvEl.textContent = `Século: ${selSeculo.value||'Todos'} • Área: ${selArea.value||'Todas'} • Empresa: ${selEmpresa.value||'Todas'}`;
      chartCsv = updateChart('chartCsv', chartCsv, d.agg_all || {});
      (d.items||[]).forEach(it => gridCsv.insertAdjacentHTML('beforeend', cardHTML(it)));
    }
  } finally {
    pillLoading(false);
  }
}

continentSelect.addEventListener('change', loadAll);
selSeculo.addEventListener('change', loadAll);
selArea.addEventListener('change', loadAll);
selEmpresa.addEventListener('change', loadAll);

loadFacetsCsv().then(loadAll);
</script>
</body>
</html>
