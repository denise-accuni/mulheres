<?php
// simulate index in root after move
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'Observatorio\\';
        $baseDir = __DIR__ . '/src/';
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return;
        }
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}
$config = require __DIR__ . '/config/config.php';
use Observatorio\Core\ObservatorioApp;
$app = new ObservatorioApp($config);
$app->run();
