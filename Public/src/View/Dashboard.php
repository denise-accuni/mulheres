<?php
namespace Observatorio\View;

/**
 * Dashboard para apresentar estatísticas resumidas.
 */
class Dashboard
{
    /**
     * Renderiza o dashboard.
     *
     * @param array $estatisticas Resultado de Estatisticas::gerarEstatisticas()
     * @return string
     */
    public function render(array $estatisticas): string
    {
        $total = $estatisticas['total'] ?? 0;
        $porContinente = $estatisticas['porContinente'] ?? [];
        $principais = $estatisticas['principaisAreas'] ?? [];

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Dashboard</h1>
    </header>
    <main>
        <p>Total de perfis cadastrados: <strong><?= (int) $total ?></strong></p>
        <h2>Distribuição por Continente</h2>
        <ul>
            <?php foreach ($porContinente as $continente => $count): ?>
                <li><?= htmlspecialchars($continente) ?>: <?= (int) $count ?></li>
            <?php endforeach; ?>
        </ul>
        <h2>Principais Áreas de Atuação</h2>
        <ol>
            <?php foreach ($principais as $area): ?>
                <li><?= htmlspecialchars($area) ?></li>
            <?php endforeach; ?>
        </ol>
        <nav>
            <a href="index.php">Lista</a> |
            <a href="?view=mapa">Mapa</a> |
            <a href="?view=graficos">Gráficos</a>
        </nav>
    </main>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}