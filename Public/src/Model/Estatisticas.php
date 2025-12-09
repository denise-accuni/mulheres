<?php
namespace Observatorio\Model;

use Observatorio\Model\Perfil;

/**
 * Classe utilitária para calcular estatísticas a partir de uma lista de perfis.
 */
class Estatisticas
{
    /**
     * Gera estatísticas básicas: total de perfis, distribuição por
     * continente, distribuição por áreas de atuação e lista das
     * principais áreas.
     *
     * @param Perfil[] $perfis
     * @return array<string,mixed>
     */
    public static function gerarEstatisticas(array $perfis): array
    {
        $total = count($perfis);
        $porContinente = [];
        $porArea = [];

        foreach ($perfis as $perfil) {
            // Continentes
            $cont = $perfil->getContinente();
            $porContinente[$cont] = ($porContinente[$cont] ?? 0) + 1;
            // Áreas
            foreach ($perfil->getAreasDeAtuacao() as $area) {
                $porArea[$area] = ($porArea[$area] ?? 0) + 1;
            }
        }

        // Ordenar áreas de forma decrescente
        arsort($porArea);
        $principaisAreas = array_slice(array_keys($porArea), 0, 5);

        return [
            'total' => $total,
            'porContinente' => $porContinente,
            'porArea' => $porArea,
            'principaisAreas' => $principaisAreas,
        ];
    }
}