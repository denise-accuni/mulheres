<?php
namespace Observatorio\Model;

/**
 * Representa um país associado a um perfil.
 */
class Pais
{
    public string $codigoISO;
    public string $nome;

    public function __construct(string $codigoISO, string $nome)
    {
        $this->codigoISO = $codigoISO;
        $this->nome = $nome;
    }
}