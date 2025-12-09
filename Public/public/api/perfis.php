<?php
// Endpoint que retorna uma lista de perfis em formato JSON.
// Aceita parâmetros via query string:
//  - continente: filtra por continente (case-insensitive)
//  - search: filtra por nome (substring, case-insensitive)
//  - page: número da página (iniciando em 1)
//  - limit: quantidade de itens por página (padrão 20)

header('Content-Type: application/json; charset=utf-8');

// Este endpoint retorna perfis a partir de um CSV remoto hospedado no GitHub.
// O conjunto de dados utilizado é "mulheres_cs_1900_1999.csv", que contém
// informações sobre mulheres na computação no século XX. Caso a leitura
// remota falhe (por exemplo, devido a restrições de rede no host), o
// endpoint recorre a um arquivo JSON local (gerado previamente a partir do
// mesmo CSV) localizado em data/perfis.json.

/**
 * Determina o continente de um país. Se o continente estiver definido no
 * dataset, ele será utilizado; caso contrário, tenta deduzir usando uma API
 * externa (RestCountries). Para evitar múltiplas requisições, os resultados
 * são armazenados em cache.
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
 * Carrega perfis a partir do CSV remoto. Se falhar, retorna null.
 * O CSV possui as seguintes colunas:
 * - Nome completo
 * - Continente
 * - País
 * - Década principal de impacto
 * - Ano de nascimento
 * - Período de atividade documentado
 * - Principal impacto (resumo)
 * - Setor de atuação (categoria)
 * - Instituição/Organização de vínculo
 * - Local de nascimento/residência
 * - Principais obras/contribuições (máx. 3 linhas)
 * - Links de comprovação (1 por linha)
 * - Qualidade da evidência (Alto/Médio/Baixo)
 */
function carregarPerfisRemotos(): ?array
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
        // Extrai informações principais
        $nome = trim($registro['Nome completo'] ?? '');
        $resumo = $registro['Principal impacto (resumo)'] ?? '';
        $obras  = $registro['Principais obras/contribuições (máx. 3 linhas)'] ?? '';
        $biografia = trim($resumo . ' ' . $obras);
        $areasStr = $registro['Setor de atuação (categoria)'] ?? '';
        // separa áreas por ; ou ,
        $areas = array_filter(array_map('trim', preg_split('/[;,]/', $areasStr)), fn($v) => $v !== '');
        $pais  = trim($registro['País'] ?? '');
        $continente = trim($registro['Continente'] ?? '');
        if ($continente === '' || strcasecmp($continente, 'Global') === 0) {
            $continente = $pais ? obterContinentedePais($pais) : 'Desconhecido';
        }
        $linksStr = $registro['Links de comprovação (1 por linha)'] ?? '';
        // separa links por quebras de linha ou ponto e vírgula
        $fontes = array_values(array_filter(array_map('trim', preg_split('/[\n;]+/', $linksStr)), fn($v) => $v !== ''));
        $fonte  = $fontes[0] ?? '';
        $anoStr = $registro['Década principal de impacto'] ?? '';
        $ano = '';
        if ($anoStr !== '') {
            if (preg_match('/(\d{4})/', $anoStr, $m)) {
                $ano = $m[1];
            }
        }
        $perfis[] = [
            'nome'          => $nome,
            'biografia'     => $biografia,
            'areasDeAtuacao'=> $areas,
            'pais'          => $pais,
            'continente'    => $continente,
            'ano'           => $ano,
            'fonte'         => $fonte,
            'fontes'        => $fontes
        ];
    }
    return $perfis;
}

// Tenta carregar perfis do CSV remoto; em caso de falha, utiliza JSON local
$perfis = carregarPerfisRemotos();
if ($perfis === null) {
    $fallbackFile = __DIR__ . '/../../data/perfis.json';
    if (file_exists($fallbackFile)) {
        $json = file_get_contents($fallbackFile);
        $perfis = json_decode($json, true);
    } else {
        $perfis = [];
    }
}

$continente = isset($_GET['continente']) ? trim($_GET['continente']) : '';
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';
$page       = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit      = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 20;

// Filtrar pelo continente, se fornecido
if ($continente !== '') {
    $perfis = array_filter($perfis, function ($p) use ($continente) {
        return strcasecmp($p['continente'] ?? '', $continente) === 0;
    });
}

// Filtrar por termo de busca no nome, se fornecido
if ($search !== '') {
    $searchLc = mb_strtolower($search);
    $perfis = array_filter($perfis, function ($p) use ($searchLc) {
        return mb_strpos(mb_strtolower($p['nome']), $searchLc) !== false;
    });
}

$total = count($perfis);

// Ordenar alfabeticamente por nome
usort($perfis, function ($a, $b) {
    return strcmp($a['nome'], $b['nome']);
});

// Paginação
$offset = ($page - 1) * $limit;
$items  = array_slice($perfis, $offset, $limit);

echo json_encode([
    'total' => $total,
    'page'  => $page,
    'limit' => $limit,
    'items' => array_values($items)
]);