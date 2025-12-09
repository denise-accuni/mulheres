<?php
namespace Observatorio\Service;

use Observatorio\Model\Perfil;

/**
 * Classe utilitária para filtrar perfis.
 */
class Filtro
{
    /**
     * Filtra perfis por nome (contém termo).
     *
     * @param Perfil[] $perfis
     * @param string   $termo
     * @return Perfil[]
     */
    public function porNome(array $perfis, string $termo): array
    {
        $termo = mb_strtolower($termo);
        return array_values(array_filter($perfis, function (Perfil $p) use ($termo) {
            return mb_strpos(mb_strtolower($p->getNome()), $termo) !== false;
        }));
    }

    /**
     * Filtra perfis por continente.
     *
     * @param Perfil[] $perfis
     * @param string   $continente
     * @return Perfil[]
     */
    public function porContinente(array $perfis, string $continente): array
    {
        return array_values(array_filter($perfis, function (Perfil $p) use ($continente) {
            return strtolower($p->getContinente()) === strtolower($continente);
        }));
    }

    /**
     * Filtra perfis por país.
     *
     * @param Perfil[] $perfis
     * @param string   $pais
     * @return Perfil[]
     */
    public function porPais(array $perfis, string $pais): array
    {
        $pais = mb_strtolower($pais);
        return array_values(array_filter($perfis, function (Perfil $p) use ($pais) {
            foreach ($p->getPaises() as $pItem) {
                if (mb_strtolower($pItem) === $pais) {
                    return true;
                }
            }
            return false;
        }));
    }
}