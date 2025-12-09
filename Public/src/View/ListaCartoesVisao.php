<?php
namespace Observatorio\View;

use Observatorio\Model\Perfil;

/**
 * Gera uma lista de perfis em formato de cartões.
 */
class ListaCartoesVisao
{
    /**
     * Renderiza a lista de perfis e eventuais mensagens de erro.
     *
     * @param Perfil[] $perfis
     * @param array<string> $erros
     * @return string
     */
    public function render(array $perfis, array $erros = []): string
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observatório Mulheres na Computação</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Observatório Mulheres na Computação</h1>
    </header>
    <main>
        <form method="get" action="">
            <label for="q">Pesquisar por nome:</label>
            <input type="text" id="q" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit">Buscar</button>
        </form>
        <?php if (!empty($erros)): ?>
            <div class="erros">
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li style="color:red;">⚠️ <?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="cards">
        <?php foreach ($perfis as $perfil): ?>
            <div class="card">
                <h2><?= htmlspecialchars($perfil->getNome()) ?></h2>
                <img src="<?= htmlspecialchars($perfil->getImagemUrl()) ?>" alt="Foto de <?= htmlspecialchars($perfil->getNome()) ?>" style="max-width:100%; height:auto;">
                <p><strong>Países:</strong> <?= htmlspecialchars(implode(', ', $perfil->getPaises())) ?></p>
                <p><strong>Continente:</strong> <?= htmlspecialchars($perfil->getContinente()) ?></p>
                <p><?= htmlspecialchars(mb_substr($perfil->getBiografia(), 0, 140)) ?>…</p>
            </div>
        <?php endforeach; ?>
        </div>
        <p>Total de perfis: <?= count($perfis) ?></p>
        <nav>
            <a href="?view=dashboard">Dashboard</a> |
            <a href="?view=mapa">Mapa</a> |
            <a href="?view=graficos">Gráficos</a>
        </nav>
    </main>
    <script src="js/script.js"></script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}