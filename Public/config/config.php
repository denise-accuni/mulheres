<?php
/**
 * Configurações globais da aplicação.
 *
 * Este arquivo contém definições simples que podem ser ajustadas
 * conforme o ambiente de implantação.  Mantê-lo fora da pasta
 * `public/` evita que seja acessado diretamente via navegador.
 */

return [
    // URL base para requisições de APIs externas (Wikipedia/Wikidata).
    'api' => [
        'wikipedia_base' => 'https://pt.wikipedia.org/api/rest_v1',
        'wikidata_base'   => 'https://www.wikidata.org/w/api.php',
        // Chaves de API podem ser adicionadas aqui, se necessário.
    ],
    // Habilitar ou desabilitar o modo de depuração.
    'debug' => true,
];