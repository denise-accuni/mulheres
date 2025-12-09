<?php
namespace Observatorio\View;

use Observatorio\Model\Perfil;

/**
 * Renderiza uma lista simples de coordenadas como mapa.
 *
 * Uma versão completa integraria um serviço de mapas (Google Maps,
 * Leaflet, etc.) para exibir marcadores.  Aqui apresentamos uma
 * tabela com latitudes e longitudes.
 */
class MapaVisao
{
    /**
     * @param Perfil[] $perfis
     * @return string
     */
    public function render(array $perfis): string
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de perfis</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Mapa de Mulheres na Computação</h1>
    </header>
    <main>
        <p>Esta página exibe uma lista de coordenadas associadas aos perfis.  Em uma implementação
        futura, um mapa interativo será utilizado.</p>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr><th>Nome</th><th>Países</th><th>Continente</th><th>Latitude</th><th>Longitude</th></tr>
            </thead>
            <tbody>
                <?php foreach ($perfis as $perfil): ?>
                <?php $coords = $perfil->getCoordenadas(); ?>
                <tr>
                    <td><?= htmlspecialchars($perfil->getNome()) ?></td>
                    <td><?= htmlspecialchars(implode(', ', $perfil->getPaises())) ?></td>
                    <td><?= htmlspecialchars($perfil->getContinente()) ?></td>
                    <td><?= htmlspecialchars((string) $coords[0]) ?></td>
                    <td><?= htmlspecialchars((string) $coords[1]) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <nav>
            <a href="index.php">Lista</a> |
            <a href="?view=dashboard">Dashboard</a> |
            <a href="?view=graficos">Gráficos</a>
        </nav>
    </main>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}