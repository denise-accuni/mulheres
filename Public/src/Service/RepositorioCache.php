<?php
namespace Observatorio\Service;

use Observatorio\Model\Perfil;

/**
 * Repositório de cache em memória para perfis.
 *
 * Para simplificar, este repositório mantém os perfis em um array
 * associativo na memória durante a requisição.  Em uma aplicação
 * real, seria interessante persistir este cache em um banco ou
 * sistema de arquivos.
 */
class RepositorioCache
{
    /**
     * @var array<int, Perfil> Mapeamento de ID para Perfil
     */
    private array $perfis = [];

    /**
     * Armazena um perfil no repositório.
     */
    public function salvarPerfil(Perfil $perfil): void
    {
        $this->perfis[$perfil->getId()] = $perfil;
    }

    /**
     * Obtém um perfil pelo ID.
     */
    public function obterPerfilPorId(int $id): ?Perfil
    {
        return $this->perfis[$id] ?? null;
    }

    /**
     * Retorna todos os perfis armazenados.
     *
     * @return Perfil[]
     */
    public function todos(): array
    {
        return array_values($this->perfis);
    }

    /**
     * Limpa o repositório.
     */
    public function limpar(): void
    {
        $this->perfis = [];
    }
}