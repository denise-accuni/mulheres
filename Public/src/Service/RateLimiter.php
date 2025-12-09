<?php
namespace Observatorio\Service;

/**
 * Controla a quantidade de chamadas de APIs permitidas dentro de um
 * intervalo de tempo.
 *
 * Implementação simplificada que limita a uma chamada por segundo.
 */
class RateLimiter
{
    private float $ultimaChamada;

    public function __construct()
    {
        $this->ultimaChamada = 0;
    }

    /**
     * Retorna true se a chamada é permitida, false caso contrário.
     */
    public function permitirChamada(): bool
    {
        $agora = microtime(true);
        if ($agora - $this->ultimaChamada >= 1) {
            $this->ultimaChamada = $agora;
            return true;
        }
        return false;
    }
}