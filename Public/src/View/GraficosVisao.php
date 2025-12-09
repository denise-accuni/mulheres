<?php
namespace Observatorio\View;

/**
 * Renderiza gráficos simples (na forma de tabelas ou listas) para
 * visualização das estatísticas dos perfis.
 */
class GraficosVisao
{
    /**
     * Renderiza a visualização em HTML.
     *
     * @param array $estatisticas Estrutura retornada por Estatisticas::gerarEstatisticas()
     * @return string
     */
    public function render(array $estatisticas): string
    {
        $porContinente = $estatisticas['porContinente'] ?? [];
        $porArea = $estatisticas['porArea'] ?? [];

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Distribuição de Perfis</h1>
    </header>
    <main>
        <h2>Perfis por Continente</h2>
        <ul>
            <?php foreach ($porContinente as $continente => $count): ?>
                <li><?= htmlspecialchars($continente) ?>: <?= (int) $count ?></li>
            <?php endforeach; ?>
        </ul>
        <h2>Perfis por Área de Atuação</h2>
        <ul>
            <?php foreach ($porArea as $area => $count): ?>
                <li><?= htmlspecialchars($area) ?>: <?= (int) $count ?></li>
            <?php endforeach; ?>
        </ul>
        <nav>
            <a href="index.php">Lista</a> |
            <a href="?view=mapa">Mapa</a> |
            <a href="?view=dashboard">Dashboard</a>
        </nav>
    </main>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}