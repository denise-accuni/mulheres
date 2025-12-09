<?php
namespace Observatorio\Model;

/**
 * Representa uma pesquisadora ou pioneira da computação.
 */
class Perfil
{
    private int $id;
    private string $nome;
    /** @var string[] Lista de países de origem ou atuação */
    private array $paises;
    private string $continente;
    private string $biografia;
    /** @var string[] Áreas de estudo ou atuação */
    private array $areasDeAtuacao;
    private string $imagemUrl;
    /** @var float[] Coordenadas geográficas [lat, lng] */
    private array $coordenadas;

    public function __construct(
        int $id,
        string $nome,
        array $paises,
        string $continente,
        string $biografia,
        array $areasDeAtuacao,
        string $imagemUrl,
        array $coordenadas
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->paises = $paises;
        $this->continente = $continente;
        $this->biografia = $biografia;
        $this->areasDeAtuacao = $areasDeAtuacao;
        $this->imagemUrl = $imagemUrl;
        $this->coordenadas = $coordenadas;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * @return string[]
     */
    public function getPaises(): array
    {
        return $this->paises;
    }

    public function getContinente(): string
    {
        return $this->continente;
    }

    public function getBiografia(): string
    {
        return $this->biografia;
    }

    /**
     * @return string[]
     */
    public function getAreasDeAtuacao(): array
    {
        return $this->areasDeAtuacao;
    }

    public function getImagemUrl(): string
    {
        return $this->imagemUrl;
    }

    /**
     * @return float[] Coordenadas [lat, lng]
     */
    public function getCoordenadas(): array
    {
        return $this->coordenadas;
    }
}