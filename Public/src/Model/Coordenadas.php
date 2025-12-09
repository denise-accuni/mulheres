<?php
namespace Observatorio\Model;

/**
 * Representa coordenadas geográficas de latitude e longitude.
 */
class Coordenadas
{
    public float $lat;
    public float $lng;

    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }
}