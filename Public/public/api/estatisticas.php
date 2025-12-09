<?php
// Endpoint que retorna estatísticas agregadas sobre os perfis.
// Fornece dados para o dashboard (totais e distribuição por continente).

header('Content-Type: application/json; charset=utf-8');

// Assim como em perfis.php, este endpoint tenta obter os dados do CSV remoto
// (mulheres_cs_1900_1999.csv). Se falhar, usa o arquivo JSON local.

/**
 * Determina o continente de um país. Utiliza um mapeamento básico e, em
 * último caso, consulta a API RestCountries para descobrir a região.
 */
function obterContinentedePais(string $pais): string
{
    static $cache = [];
    $mapaEstatico = [
        'Albânia'        => 'Europa',
        'Arábia Saudita' => 'Asia',
        'Brasil'         => 'America',
        'Canadá'         => 'America',
        'China'          => 'Asia',
        'Estados Unidos' => 'America',
        'Irlanda'        => 'Europa',
        'Nigéria'        => 'Africa',
        'Reino Unido'    => 'Europa',
        'Taiwan'         => 'Asia',
        'Índia'          => 'Asia'
    ];
    if (isset($cache[$pais])) {
        return $cache[$pais];
    }
    if (isset($mapaEstatico[$pais])) {
        $cache[$pais] = $mapaEstatico[$pais];
        return $cache[$pais];
    }
    $continente = 'Desconhecido';
    $url = 'https://restcountries.com/v3.1/name/' . urlencode($pais) . '?fullText=true';
    $resp = @file_get_contents($url);
    if ($resp !== false) {
        $dados = json_decode($resp, true);
        if (is_array($dados) && isset($dados[0]['region'])) {
            $mapRegiao = [
                'Americas' => 'America',
                'Europe'   => 'Europa',
                'Asia'     => 'Asia',
                'Africa'   => 'Africa',
                'Oceania'  => 'Oceania'
            ];
            $continente = $mapRegiao[$dados[0]['region']] ?? 'Desconhecido';
        }
    }
    $cache[$pais] = $continente;
    return $continente;
}

/**
 * Carrega perfis do CSV remoto. Retorna array de perfis ou null em caso de
 * falha. Esta função replica a lógica de perfis.php para converter
 * "mulheres_cs_1900_1999.csv" em um array de registros simplificados.
 */
function carregarPerfisRemotosEstat(): ?array
{
    $url = 'https://raw.githubusercontent.com/denise-accuni/mulheres/main/mulheres_cs_1900_1999.csv';
    $conteudo = @file_get_contents($url);
    if ($conteudo === false) {
        return null;
    }
    $linhas = array_map('rtrim', explode("\n", $conteudo));
    if (count($linhas) < 2) {
        return null;
    }
    $cabecalho = str_getcsv(array_shift($linhas));
    $perfis = [];
    foreach ($linhas as $linha) {
        if ($linha === '') continue;
        $valores = str_getcsv($linha);
        if (count($valores) < count($cabecalho)) {
            $valores = array_pad($valores, count($cabecalho), null);
        }
        $registro = array_combine($cabecalho, $valores);
        if (!$registro) continue;
        $nome = trim($registro['Nome completo'] ?? '');
        $areasStr = $registro['Setor de atuação (categoria)'] ?? '';
        $areas = array_filter(array_map('trim', preg_split('/[;,]/', $areasStr)), fn($v) => $v !== '');
        $pais = trim($registro['País'] ?? '');
        $continente = trim($registro['Continente'] ?? '');
        if ($continente === '' || strcasecmp($continente, 'Global') === 0) {
            $continente = $pais ? obterContinentedePais($pais) : 'Desconhecido';
        }
        $perfis[] = [
            'nome'          => $nome,
            'areasDeAtuacao'=> $areas,
            'pais'          => $pais,
            'continente'    => $continente
        ];
    }
    return $perfis;
}

$perfis = carregarPerfisRemotosEstat();
if ($perfis === null) {
    // fallback
    $fallbackFile = __DIR__ . '/../../data/perfis.json';
    if (file_exists($fallbackFile)) {
        $json = file_get_contents($fallbackFile);
        $perfis = json_decode($json, true);
    } else {
        $perfis = [];
    }
}

// Calcula totais e distribuições
$total = count($perfis);
$continentes = [];
$areas = [];
$paises = [];
foreach ($perfis as $p) {
    // Continente
    $cont = $p['continente'] ?? 'Desconhecido';
    $continentes[$cont] = ($continentes[$cont] ?? 0) + 1;
    // Áreas de atuação
    if (!empty($p['areasDeAtuacao']) && is_array($p['areasDeAtuacao'])) {
        foreach ($p['areasDeAtuacao'] as $area) {
            $areas[$area] = ($areas[$area] ?? 0) + 1;
        }
    }
    // País
    $paisField = $p['pais'] ?? ($p['paises'] ?? null);
    if ($paisField) {
        $listaPaises = is_array($paisField) ? $paisField : [$paisField];
        foreach ($listaPaises as $pp) {
            $paises[$pp] = ($paises[$pp] ?? 0) + 1;
        }
    }
}
// Ordena áreas e países em ordem decrescente de contagem
arsort($areas);
arsort($paises);
// Seleciona os 5 principais
$topAreas  = array_slice($areas, 0, 5, true);
$topPaises = array_slice($paises, 0, 5, true);
echo json_encode([
    'total'         => $total,
    'porContinente' => $continentes,
    'topAreas'      => $topAreas,
    'topPaises'     => $topPaises
]);