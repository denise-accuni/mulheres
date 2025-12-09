<?php
namespace Observatorio\Service;

use Observatorio\Core\ErroHandler;

/**
 * Cliente simples para chamadas à API da Wikipédia.
 *
 * Esta classe encapsula as interações com a API REST da Wikipédia
 * (https://pt.wikipedia.org/api/rest_v1).  Para manter a simplicidade, os
 * métodos retornam arrays associativos.  Em uma versão completa,
 * podem ser retornados modelos específicos.
 */
class WikipediaClient
{
    private array $apiConfig;
    private ErroHandler $erroHandler;

    public function __construct(array $apiConfig, ErroHandler $erroHandler)
    {
        $this->apiConfig = $apiConfig;
        $this->erroHandler = $erroHandler;
    }

    /**
     * Busca perfis por tag ou termo de busca.
     *
     * @param string $tag Termo para pesquisar
     * @param int    $limite Máximo de resultados
     * @return array Lista de páginas correspondentes
     */
    public function buscarPerfis(string $tag, int $limite = 10): array
    {
        // TODO: implementar chamada real à API de pesquisa da Wikipédia
        $this->erroHandler->notificar('Método buscarPerfis ainda não implementado');
        return [];
    }

    /**
     * Retorna o resumo de uma página da Wikipédia dado o título.
     *
     * @param string $titulo Título da página
     * @return array|null Dados do resumo ou null em caso de erro
     */
    public function buscaResumo(string $titulo): ?array
    {
        $url = $this->apiConfig['wikipedia_base'] . '/page/summary/' . rawurlencode($titulo);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Accept: application/json'
                ],
                'timeout' => 5,
            ],
        ]);
        $resp = @file_get_contents($url, false, $context);
        if ($resp === false) {
            $this->erroHandler->notificar('Erro ao buscar resumo de ' . $titulo);
            return null;
        }
        return json_decode($resp, true);
    }

    /**
     * Busca informações de localização de uma página (opcional).
     *
     * @param string $title Título da página
     * @return array|null Dados de localização ou null
     */
    public function buscaLocalizacao(string $title): ?array
    {
        // A API REST não oferece localização diretamente; seria necessário
        // consultar a Wikidata.  Este método é um placeholder.
        $this->erroHandler->notificar('Método buscaLocalizacao ainda não implementado');
        return null;
    }
}