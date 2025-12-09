<?php
// Endpoint para baixar o conjunto de dados completo em CSV.
// Por padrão, tenta buscar o arquivo diretamente do GitHub. Caso
// a solicitação falhe (por exemplo, sem acesso à Internet), recorre a um
// arquivo local armazenado em data/mulheres_cs_1900_1999.csv.

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="mulheres_cs_1900_1999.csv"');

$url = 'https://raw.githubusercontent.com/denise-accuni/mulheres/main/mulheres_cs_1900_1999.csv';
$conteudo = @file_get_contents($url);
if ($conteudo === false) {
    // Fallback para arquivo local
    $localFile = __DIR__ . '/../../data/mulheres_cs_1900_1999.csv';
    if (file_exists($localFile)) {
        $conteudo = file_get_contents($localFile);
    } else {
        http_response_code(500);
        echo "Não foi possível obter o arquivo CSV.";
        exit;
    }
}
echo $conteudo;