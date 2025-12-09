<?php
namespace Observatorio\Model;

/**
 * Representa uma conquista importante da pesquisadora.
 */
class Conquista
{
    public string $titulo;
    public string $descricao;
    public string $fonte;

    public function __construct(string $titulo, string $descricao, string $fonte)
    {
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->fonte = $fonte;
    }
}