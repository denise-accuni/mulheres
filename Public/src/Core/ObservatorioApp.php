<?php
namespace Observatorio\Core;

use Observatorio\Service\WikipediaClient;
use Observatorio\Service\WikidataClient;
use Observatorio\Service\RepositorioCache;
use Observatorio\Service\Filtro;
use Observatorio\Service\RateLimiter;
use Observatorio\Model\Perfil;
use Observatorio\View\ListaCartoesVisao;
use Observatorio\View\MapaVisao;
use Observatorio\View\GraficosVisao;
use Observatorio\View\Dashboard;
use Observatorio\Model\Estatisticas;

/**
 * Classe principal do aplicativo.
 *
 * Responsável por coordenar a carga de dados, execução de filtros e
 * renderização das diferentes visões (lista, mapa, gráficos,
 * dashboard).  A lógica de roteamento é simples e baseada nos
 * parâmetros de query string.
 */
class ObservatorioApp
{
    private array $config;
    private WikipediaClient $wikipediaClient;
    private WikidataClient $wikidataClient;
    private RepositorioCache $cache;
    private Filtro $filtro;
    private RateLimiter $rateLimiter;
    private ErroHandler $erroHandler;

    /**
     * Construtor.
     *
     * @param array $config Configurações carregadas de `config/config.php`.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->erroHandler = new ErroHandler();
        $this->wikipediaClient = new WikipediaClient($config['api'], $this->erroHandler);
        $this->wikidataClient  = new WikidataClient($config['api'], $this->erroHandler);
        $this->cache = new RepositorioCache();
        $this->filtro = new Filtro();
        $this->rateLimiter = new RateLimiter();
    }

    /**
     * Executa a aplicação, tratando a requisição atual.
     */
    public function run(): void
    {
        // Carrega dados de exemplo ou do cache.
        $perfis = $this->carregarDados();

        // Obter parâmetro de ação/view
        $view = $_GET['view'] ?? 'lista';

        switch ($view) {
            case 'mapa':
                $visao = new MapaVisao();
                echo $visao->render($perfis);
                break;
            case 'graficos':
                $visao = new GraficosVisao();
                $estatisticas = Estatisticas::gerarEstatisticas($perfis);
                echo $visao->render($estatisticas);
                break;
            case 'dashboard':
                $visao = new Dashboard();
                $estatisticas = Estatisticas::gerarEstatisticas($perfis);
                echo $visao->render($estatisticas);
                break;
            default:
                // Permitir busca por termo na query ?q=
                $termo = trim($_GET['q'] ?? '');
                if ($termo !== '') {
                    $perfisFiltrados = $this->filtro->porNome($perfis, $termo);
                } else {
                    $perfisFiltrados = $perfis;
                }
                $visao = new ListaCartoesVisao();
                echo $visao->render($perfisFiltrados, $this->erroHandler->getErros());
        }
    }

    /**
     * Carrega os dados das mulheres para exibição.
     *
     * Em uma versão inicial, utiliza dados estáticos para demonstração.
     * Uma implementação futura deverá integrar chamadas ao WikipediaClient
     * e WikidataClient, bem como cachear os resultados em
     * `RepositorioCache`.
     *
     * @return Perfil[]
     */
    private function carregarDados(): array
    {
        // Se houver dados no cache, retorná‑los
        $todos = $this->cache->todos();
        if (!empty($todos)) {
            return $todos;
        }

        // Carregar dados de perfis a partir de arquivo JSON localizado em `data/perfis.json`.
        // Esse arquivo contém uma coleção de perfis com campos: id, nome, pais, continente,
        // biografia, areasDeAtuacao, imagemURL e coordenadas.  É gerado durante o build
        // como uma demonstração com 100 mulheres em tecnologia.
        $dataFile = __DIR__ . '/../../data/perfis.json';
        $perfis = [];
        if (file_exists($dataFile)) {
            $json = file_get_contents($dataFile);
            $arr = json_decode($json, true);
            if (is_array($arr)) {
                foreach ($arr as $item) {
                    // Garantir que campos obrigatórios existam; ignorar registros inválidos
                    if (!isset($item['id'], $item['nome'], $item['pais'], $item['continente'], $item['biografia'])) {
                        continue;
                    }
                    $id = (int) $item['id'];
                    $nome = (string) $item['nome'];
                    $paises = [];
                    if (is_array($item['pais'])) {
                        $paises = $item['pais'];
                    } else {
                        $paises = [$item['pais']];
                    }
                    $continente = (string) $item['continente'];
                    $biografia = (string) $item['biografia'];
                    $areas = [];
                    if (isset($item['areasDeAtuacao']) && is_array($item['areasDeAtuacao'])) {
                        $areas = $item['areasDeAtuacao'];
                    }
                    $imagemUrl = $item['imagemURL'] ?? '';
                    $coord = $item['coordenadas'] ?? [0, 0];
                    $perfil = new Perfil(
                        $id,
                        $nome,
                        $paises,
                        $continente,
                        $biografia,
                        $areas,
                        $imagemUrl,
                        $coord
                    );
                    $perfis[] = $perfil;
                    $this->cache->salvarPerfil($perfil);
                }
            }
        }

        // Caso não haja perfis carregados, manter lista vazia
        return $perfis;
    }
}