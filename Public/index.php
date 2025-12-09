<?php
/* ===========================================================
   Observatório Global — Mulheres na Computação (API Wikidata)
   - Paginação real: LIMIT 30 / OFFSET
   - Endpoint stats para totais e distribuição por país
   - Cabeçalho com logo à direita
   =========================================================== */
header('Content-Type: text/html; charset=UTF-8');

mb_internal_encoding('UTF-8');

// Continentes (QIDs)
$CONTINENTS = [
  'Ásia'    => 'Q48',
  'América' => 'Q18,Q49,Q828',
  'Europa'  => 'Q46',
  'África'  => 'Q15',
  'Oceania' => 'Q55643'
];

function get_param($k,$d=null){ return isset($_GET[$k]) ? trim($_GET[$k]) : $d; }

function http_post_sparql($sparql){
  $url = 'https://query.wikidata.org/sparql';
  $headers = [
    'Accept: application/sparql-results+json',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'User-Agent: ObservatorioMulheres/1.0 (contato: 0142038@professor.unig.edu.br)'
  ];
  $postFields = http_build_query(['query'=>$sparql], '', '&', PHP_QUERY_RFC3986);
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$postFields, CURLOPT_HTTPHEADER=>$headers,
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>45
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
  if ($resp===false || $code>=400) { http_response_code(502); echo json_encode(['error'=>"SPARQL $code: $err"]); exit; }
  return json_decode($resp,true);
}

/* ---------- SPARQL builders ---------- */
function build_core_filters($continentQIDs){
  $continentFilter = implode(' ', array_map(fn($q)=>"wd:$q", explode(',', $continentQIDs)));
  return "
    ?item wdt:P31 wd:Q5 .
    ?item wdt:P21 wd:Q6581072 .
    OPTIONAL { ?item wdt:P106 ?occ . }
    OPTIONAL { ?item wdt:P101 ?field . }
    FILTER( (?occ IN (wd:Q82594, wd:Q80993, wd:Q11661)) || (?field = wd:Q21198) )
    ?item wdt:P27 ?country .
    ?country wdt:P30 ?continent .
    FILTER(?continent IN ($continentFilter))
  ";
}
function build_list_query($continentQIDs, $limit=30, $offset=0){
  $core = build_core_filters($continentQIDs);
  return "
SELECT ?item ?itemLabel ?itemDescription ?countryLabel ?image ?ptwiki ?enwiki WHERE {
  $core
  OPTIONAL { ?item wdt:P18 ?image . }
  OPTIONAL { ?ptwikiArticle schema:about ?item ; schema:isPartOf <https://pt.wikipedia.org/> ; schema:name ?ptwiki . }
  OPTIONAL { ?enwikiArticle schema:about ?item ; schema:isPartOf <https://en.wikipedia.org/> ; schema:name ?enwiki . }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"pt-BR,pt,en\". }
}
ORDER BY ?itemLabel
LIMIT $limit OFFSET $offset";
}
function build_stats_count($continentQIDs){
  $core = build_core_filters($continentQIDs);
  return "SELECT (COUNT(DISTINCT ?item) AS ?total) WHERE { $core }";
}
function build_stats_countries($continentQIDs, $limit=10){
  $core = build_core_filters($continentQIDs);
  return "
SELECT ?countryLabel (COUNT(DISTINCT ?item) AS ?c) WHERE {
  $core
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"pt-BR,pt,en\". }
}
GROUP BY ?countryLabel
ORDER BY DESC(?c)
LIMIT $limit";
}

/* ---------- API endpoints ---------- */
if (get_param('api') === '1') {
  $continent = get_param('continent','América');
  $size  = min(30, max(1, (int)get_param('size', 30)));
  $page  = max(1, (int)get_param('page', 1));
  $offset = ($page-1)*$size;

  global $CONTINENTS;
  $continentQIDs = $CONTINENTS[$continent] ?? $CONTINENTS['América'];

  $raw = http_post_sparql(build_list_query($continentQIDs, $size, $offset));
  $rows = $raw['results']['bindings'] ?? [];
  $items = [];
  foreach ($rows as $r) {
    $label   = $r['itemLabel']['value'] ?? '';
    $desc    = $r['itemDescription']['value'] ?? '';
    $country = $r['countryLabel']['value'] ?? '';
    $image   = $r['image']['value'] ?? null;
    $pt      = $r['ptwiki']['value'] ?? null;
    $en      = $r['enwiki']['value'] ?? null;
    $link    = $pt ? "https://pt.wikipedia.org/wiki/".rawurlencode($pt)
                   : ($en ? "https://en.wikipedia.org/wiki/".rawurlencode($en) : null);
    $items[] = ['name'=>$label,'desc'=>$desc,'country'=>$country,'image'=>$image,'link'=>$link];
  }
  echo json_encode(['page'=>$page,'size'=>$size,'items'=>$items], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  exit;
}

if (get_param('api') === 'stats') {
  $continent = get_param('continent','América');
  global $CONTINENTS;
  $continentQIDs = $CONTINENTS[$continent] ?? $CONTINENTS['América'];

  $count = http_post_sparql(build_stats_count($continentQIDs));
  $total = (int)($count['results']['bindings'][0]['total']['value'] ?? 0);

  $by = http_post_sparql(build_stats_countries($continentQIDs, 10));
  $agg = [];
  foreach (($by['results']['bindings'] ?? []) as $r) {
    $agg[$r['countryLabel']['value']] = (int)$r['c']['value'];
  }
  echo json_encode(['total'=>$total,'agg_top10'=>$agg], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Observatório — API Wikidata</title>
<link rel="stylesheet" href="assets/styles.css">
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
  <div class="brand">
    <span style="font-size:22px;">🌐</span>
    <h1>Observatório — Mulheres na Computação (API)</h1>
  </div>
  <nav class="header-spacer" style="text-align:left;">
    <a href="home.php">Dashboard</a>
    <a href="index.php"><b>API Wikidata</b></a>
    <a href="outras.php">Planilha</a>
  </nav>
  <img class="brand-logo" src="assets/img/logo.png" alt="Mulheres da Computação">
</header>

<div class="wrap">
  <div class="title" id="titulo">América</div>
  <div class="subtitle">Lista paginada em lotes de 30 (Wikidata) + estatísticas leves.</div>

  <div class="toolbar">
    <input type="text" id="q" placeholder="Buscar por nome ou país… (filtro client-side do que chegou)">
    <select id="continent">
      <option>América</option><option>Ásia</option><option>Europa</option><option>África</option><option>Oceania</option>
    </select>
    <button id="refresh">Atualizar Página</button>
    <span class="pill" id="status">Pronto</span>
  </div>

  <div class="stats">
    <div class="tile"><div>Total estimado (fonte: API)</div><div style="font-size:28px;font-weight:800" id="statTotal">0</div></div>
    <div class="tile"><div>Continente</div><div style="font-size:20px;font-weight:800" id="statCont">América</div></div>
    <div class="tile"><div>Carregados nesta sessão</div><div style="font-size:20px;font-weight:800" id="statPage">0</div></div>
  </div>

  <div class="charts">
    <div class="chartbox">
      <div style="font-weight:700;margin:6px 0 8px;">Top Países (API)</div>
      <canvas id="barChart" height="150"></canvas>
    </div>
    <div class="chartbox">
      <div style="font-weight:700;margin:6px 0 8px;">Amostra desta página</div>
      <div style="font-size:14px;color:#555">Role para carregar mais páginas (30 por requisição).</div>
    </div>
  </div>

  <div class="grid" id="grid"></div>
  <div class="footer" id="footnote" style="text-align:center; color:#444; padding:16px 0 60px;">Role para carregar mais…</div>
</div>

<!-- Rodapé institucional -->
<?php /* inclui bloco padrão do item (2) aqui */ ?>
<footer class="footer">
  <div class="inner">
    <div class="title"><b>Produto do Programa de Iniciação Científica da UNIG</b></div>
    <div><b>Nome do orientador:</b> Denise Moraes do Nascimento Vieira</div>
    <div><b>Co-orientação:</b> Selma Sant Anna</div>
    <div style="margin-top:6px;"><b>Participantes:</b></div>
    <ul style="margin:6px 0 0 18px; padding:0;">
      <li>Alunas: Caroline de Souza Cruz</li> 
      <li>2º Aluna voluntária: Ana Clara do Nascimento de Oliveira</li>
      <li>3º Aluna voluntária: Caroline Luz de Souza</li>
      <li>4º Aluna voluntária: Daymara Aparecida de Abreu Silva</li>
      <li>5º Aluna voluntária: Mikaella Teixeira da Silva</li>
    </ul>
  </div>
</footer>

<script>
let page=1, size=30, loading=false, done=false, loadedCount=0;
const grid=document.getElementById('grid'), statTotal=document.getElementById('statTotal'),
      statPage=document.getElementById('statPage'), statCont=document.getElementById('statCont'),
      qInput=document.getElementById('q'), continentSelect=document.getElementById('continent'),
      statusPill=document.getElementById('status'), titleEl=document.getElementById('titulo');
let barChart;

function cardHTML(it){
  const img = it.image ? `<img class="thumb" src="${it.image}" alt="${it.name}">` : `<div class="thumb"></div>`;
  const link = it.link ? `<a class="more" href="${it.link}" target="_blank" rel="noopener">Saiba mais ↗</a>` : '';
  const desc = it.desc || '';
  return `<div class="card">${img}<div><div class="name">${it.name}</div><div class="meta">${it.country||''}</div><div class="desc">${desc}</div>${link}</div></div>`;
}

function updateBar(agg){
  const labels = Object.keys(agg), values = Object.values(agg);
  if (barChart) barChart.destroy();
  barChart = new Chart(document.getElementById('barChart').getContext('2d'), {
    type:'bar', data:{ labels, datasets:[{ label:'Perfis', data:values }]},
    options:{ plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 }}}}
  });
}

async function loadStats(){
  const r = await fetch(`index.php?api=stats&continent=${encodeURIComponent(continentSelect.value)}`);
  if (!r.ok) return;
  const d = await r.json();
  statTotal.textContent = String(d.total || 0);
  updateBar(d.agg_top10 || {});
}

async function loadPage(reset=false){
  if (loading || (done && !reset)) return;
  loading=true; statusPill.textContent='Carregando…'; statusPill.style.background='#ffe58a';
  if (reset){ page=1; done=false; loadedCount=0; grid.innerHTML=''; statPage.textContent='0'; }

  const url = `index.php?api=1&continent=${encodeURIComponent(continentSelect.value)}&page=${page}&size=${size}`;
  try{
    const r=await fetch(url); if(!r.ok) throw new Error('Falha na API');
    const data=await r.json();
    let items = data.items||[];
    // filtro client-side do que chegou
    const q = qInput.value.trim().toLowerCase();
    if (q) items = items.filter(it => (it.name||'').toLowerCase().includes(q) || (it.country||'').toLowerCase().includes(q));
    if (items.length===0){ done=true; document.getElementById('footnote').textContent='Fim dos resultados.'; }
    else {
      items.forEach(it => grid.insertAdjacentHTML('beforeend', cardHTML(it)));
      loadedCount += items.length; statPage.textContent = String(loadedCount);
      page++;
    }
  } catch(e){ alert(e.message); }
  finally { loading=false; statusPill.textContent='Pronto'; statusPill.style.background='#f1e0ff'; }
}

function onScroll(){ const near = (window.innerHeight+window.scrollY) >= (document.body.offsetHeight-300); if (near && !loading && !done) loadPage(false); }

continentSelect.addEventListener('change', ()=>{ statCont.textContent=continentSelect.value; titleEl.textContent=continentSelect.value; loadStats(); loadPage(true); });
qInput.addEventListener('input', ()=>loadPage(true));
document.getElementById('refresh').addEventListener('click', ()=>{ loadStats(); loadPage(true); });
window.addEventListener('scroll', onScroll);

loadStats(); loadPage(true);
</script>
</body>
</html>
