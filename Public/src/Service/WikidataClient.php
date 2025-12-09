<?php
namespace Observatorio\Service;

use Observatorio\Core\ErroHandler;

/**
 * Cliente para recuperar informações de países e continentes via Wikidata.
 *
 * A API da Wikidata pode ser consultada via endpoint `https://www.wikidata.org/w/api.php`.
 * Nesta implementação, os métodos estão como placeholders; a resolução
 * real exigiria consultas SPARQL ou ao endpoint API.
 */
class WikidataClient
{
    private array $apiConfig;
    private ErroHandler $erroHandler;

    public function __construct(array $apiConfig, ErroHandler $erroHandler)
    {
        $this->apiConfig = $apiConfig;
        $this->erroHandler = $erroHandler;
    }

    /**
     * Resolva o nome completo de um país a partir de seu código ISO.
     *
     * @param string $codigo Código ISO (ex.: "BR")
     * @return string|null Nome do país ou null
     */
    public function resolverPais(string $codigo): ?string
    {
        // TODO: implementar consulta real ao Wikidata
        $this->erroHandler->notificar('Método resolverPais ainda não implementado');
        return null;
    }

    /**
     * Resolve o continente ao qual pertence um país.
     *
     * @param string $pais Nome do país
     * @return string|null Nome do continente ou null
     */
    public function resolverContinente(string $pais): ?string
    {
        // TODO: implementar consulta real ao Wikidata
        $this->erroHandler->notificar('Método resolverContinente ainda não implementado');
        return null;
    }
}