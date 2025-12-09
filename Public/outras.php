<?php
/* ===========================================================
   Observatório — Outras Mulheres (fonte: planilha CSV)
   Filtros: século, área, empresa | Busca: nome/país | Paginação: 5
   CSV (UTF-8; ';'): nome;pais;seculo;area;empresa;descricao;imagem;link
   =========================================================== */
header('Content-Type: text/html; charset=UTF-8');

const CSV_PATH = __DIR__.'/data/mulheres.csv';
const PAGE_SIZE = 5;

function h($s){ return htmlspecialchars($s??'', ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function get_param($k,$d=null){ return isset($_GET[$k]) ? trim($_GET[$k]) : $d; }
function wikipedia_thumb_from_link($url){
  // Aceita links pt/en e retorna URL de miniatura (se houver)
  if (!preg_match('~https?://(pt|en)\.wikipedia\.org/wiki/([^#?]+)~i', $url, $m)) return null;
  $lang = $m[1]; $title = $m[2];
  $api = "https://$lang.wikipedia.org/api/rest_v1/page/summary/".rawurlencode($title);
  $ch = curl_init($api);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>10,
    CURLOPT_HTTPHEADER=>['Accept: application/json']
  ]);
  $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($resp===false || $code>=400) return null;
  $j = json_decode($resp, true);
  return $j['thumbnail']['source'] ?? null;
}//função para tratar a

function load_csv($path){
  if (!file_exists($path)) return [];
  $rows = [];
  if (($h = fopen($path, 'r')) !== false) {
    $header = null;
    while (($data = fgetcsv($h, 0, ';')) !== false) {
      if ($header === null) { $header = $data; continue; }
      $row = [];
      foreach ($header as $i=>$key) { $row[$key] = $data[$i] ?? ''; }
      $rows[] = $row;
    }
    fclose($h);
  }
  return $rows;
}

// ---------- Endpoint API JSON ----------
if (get_param('api') === '1') {
  $q       = mb_strtolower(get_param('q',''));
  $seculo  = get_param('seculo','');
  $area    = get_param('area','');
  $empresa = get_param('empresa','');
  $page    = max(1, (int)get_param('page',1));
  $size    = max(1, (int)get_param('size', PAGE_SIZE));

  $all = load_csv(CSV_PATH);

  // Facetas
  $fac_seculo=[]; $fac_area=[]; $fac_empresa=[];
  foreach ($all as $r) {
    if (!empty($r['seculo']))  $fac_seculo[$r['seculo']] = true;
    if (!empty($r['area']))    $fac_area[$r['area']] = true;
    if (!empty($r['empresa'])) $fac_empresa[$r['empresa']] = true;
  }
  ksort($fac_seculo); ksort($fac_area); ksort($fac_empresa);

  // Filtros
  $filtered = array_filter($all, function($r) use($q,$seculo,$area,$empresa){
    $ok = true;
    if ($q !== '') {
      $hay = mb_strtolower(($r['nome']??'').' '.($r['pais']??''));
      if (mb_strpos($hay, $q) === false) $ok=false;
    }
    if ($seculo !== ''  && $seculo  !== 'Todos')  $ok = $ok && (($r['seculo']??'')  === $seculo);
    if ($area   !== ''  && $area    !== 'Todas')  $ok = $ok && (($r['area']??'')    === $area);
    if ($empresa!== ''  && $empresa !== 'Todas')  $ok = $ok && (($r['empresa']??'') === $empresa);
    return $ok;
  });

  // Agregações por país
  $agg = [];
  foreach ($filtered as $r) {
    $p = $r['pais'] ?: '—';
    $agg[$p] = ($agg[$p] ?? 0) + 1;
    
    
  }
  arsort($agg);
  $top5 = array_slice($agg, 0, 5, true);

  // Paginação
  $total = count($filtered);
  $start = ($page-1)*$size;
  $slice = array_slice(array_values($filtered), $start, $size);

  echo json_encode([
    'total'=>$total,
    'items'=>$slice,
    'agg_all'=>$agg,
    'agg_top5'=>$top5,
    'facets'=>[
      'seculo'=>array_keys($fac_seculo),
      'area'=>array_keys($fac_area),
      'empresa'=>array_keys($fac_empresa),
    ]
  ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Observatório — Outras Mulheres (Planilha)</title>
<link rel="stylesheet" href="assets/styles.css">
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
  <div class="brand">
    <span style="font-size:22px;">🌐</span>
    <h1>Observatório — Outras Mulheres (Planilha)</h1>
  </div>
  <nav class="header-spacer" style="text-align:left;">
    <a href="home.php">Dashboard</a>
    <a href="index.php">API Wikidata</a>
    <a href="outras.php"><b>Planilha</b></a>
  </nav>
  <img class="brand-logo" src="assets/img/logo.png" alt="Mulheres da Computação">
</header>

<div class="wrap">
  <div class="title">Catálogo por Planilha</div>
  <div class="subtitle">Filtre por século, área de atuação e empresa. Busque por nome ou país.</div>

  <div class="toolbar" style="grid-template-columns: 1fr 180px 220px 220px;">
    <input type="text" id="q" placeholder="Buscar por nome ou país…">
    <select id="selSeculo"><option>Todos</option></select>
    <select id="selArea"><option>Todas</option></select>
    <select id="selEmpresa"><option>Todas</option></select>
  </div>

  <div class="stats">
    <div class="tile"><div>Total de Perfis</div><div style="font-size:28px;font-weight:800" id="statTotal">0</div></div>
    <div class="tile"><div>Carregados nesta página</div><div style="font-size:20px;font-weight:800" id="statPage">0</div></div>
    <div class="tile"><div class="pill" id="status">Pronto</div></div>
  </div>

  <div class="charts">
    <div class="chartbox">
      <div style="font-weight:700;margin:6px 0 8px;">Distribuição por País</div>
      <canvas id="barChart" height="140"></canvas>
    </div>
    <div class="chartbox">
      <div style="font-weight:700;margin:6px 0 8px;">Top 5 Países</div>
      <canvas id="pieChart" height="140"></canvas>
    </div>
  </div>

  <div class="grid" id="grid"></div>
  <div class="footer" id="footer">Role para carregar mais…</div>
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
let page=1, size=5, loading=false, done=false, total=0;
const grid=document.getElementById('grid');
const statTotal=document.getElementById('statTotal'), statPage=document.getElementById('statPage'), statusPill=document.getElementById('status');
const qInput=document.getElementById('q'), selSeculo=document.getElementById('selSeculo'), selArea=document.getElementById('selArea'), selEmpresa=document.getElementById('selEmpresa');
let barChart, pieChart;

function cardHTML(it){
  const img = it.imagem ? `<img class="thumb" src="${it.imagem}" alt="${it.nome}">` : `<div class="thumb"></div>`;
  const link = it.link ? `<a class="more" href="${it.link}" target="_blank" rel="noopener">Saiba mais ↗</a>` : '';
  const meta = [it.pais, it.seculo, it.area, it.empresa].filter(Boolean).join(" • ");
  return `<div class="card">${img}<div>
    <div class="name">${it.nome}</div>
    <div class="meta">${meta}</div>
    <div class="desc">${it.descricao||''}</div>
    ${link}
  </div></div>`;
}

function updateCharts(aggAll, aggTop5){
  const entries = Object.entries(aggAll).slice(0,10);
  const bLabels = entries.map(e=>e[0]), bData=entries.map(e=>e[1]);
  if (barChart) barChart.destroy();
  barChart = new Chart(document.getElementById('barChart').getContext('2d'), {
    type:'bar', data:{ labels:bLabels, datasets:[{ label:'Perfis', data:bData }]},
    options:{ plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 }}}}
  });

  const pLabels=Object.keys(aggTop5), pData=Object.values(aggTop5);
  if (pieChart) pieChart.destroy();
  pieChart = new Chart(document.getElementById('pieChart').getContext('2d'), {
    type:'pie', data:{ labels:pLabels, datasets:[{ data:pData }]},
    options:{ plugins:{ legend:{ position:'bottom' }}}
  });
}

async function fetchPage(reset=false){
  if (loading || (done && !reset)) return;
  loading=true; statusPill.textContent='Carregando…'; statusPill.style.background='#ffe58a';
  if (reset){ page=1; done=false; grid.innerHTML=''; statPage.textContent='0'; }

  const params = new URLSearchParams({
    api:'1', page:String(page), size:String(size),
    q:qInput.value.trim(), seculo:selSeculo.value, area:selArea.value, empresa:selEmpresa.value
  });
  try{
    const r=await fetch(`?${params.toString()}`);
    if(!r.ok) throw new Error('Falha ao carregar dados da planilha');
    const data=await r.json();

    if (page===1 && (selSeculo.options.length===1)) {
      for(const v of data.facets.seculo)  selSeculo.insertAdjacentHTML('beforeend', `<option>${v}</option>`);
      for(const v of data.facets.area)    selArea.insertAdjacentHTML('beforeend', `<option>${v}</option>`);
      for(const v of data.facets.empresa) selEmpresa.insertAdjacentHTML('beforeend', `<option>${v}</option>`);
    }

    if (page===1){ updateCharts(data.agg_all||{}, data.agg_top5||{}); }

    total=data.total; statTotal.textContent=String(total);
    if ((data.items||[]).length===0){ done=true; document.getElementById('footer').textContent='Fim dos resultados.'; }
    else {
      data.items.forEach(it=>grid.insertAdjacentHTML('beforeend', cardHTML(it)));
      statPage.textContent = String((page-1)*size + data.items.length);
      page++;
    }
  }catch(e){ alert(e.message); }
  finally{ loading=false; statusPill.textContent='Pronto'; statusPill.style.background='#f1e0ff'; }
}

function onScroll(){
  const nearBottom=(window.innerHeight+window.scrollY) >= (document.body.offsetHeight-300);
  if (nearBottom && !loading && !done) fetchPage(false);
}

qInput.addEventListener('input', ()=>fetchPage(true));
selSeculo.addEventListener('change', ()=>fetchPage(true));
selArea.addEventListener('change', ()=>fetchPage(true));
selEmpresa.addEventListener('change', ()=>fetchPage(true));
window.addEventListener('scroll', onScroll);

fetchPage(true);
</script>
</body>
</html>
