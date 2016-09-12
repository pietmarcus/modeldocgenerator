<?php

use PietMarcus\ModelDocGenerator\ModelDocGenerator;

$cwd         = getcwd();
$files       = array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php');
$loader      = null;
$directories = array($cwd, $cwd . DIRECTORY_SEPARATOR . 'config');
$configFile  = null;

// Try to load the autoloader
foreach ($files as $file) {
    if (file_exists($file)) {
        $loader = require $file;

        break;
    }
}

if ( ! $loader) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

// Try to load the configuration-file
foreach ($directories as $directory) {
    $configFile = $directory . DIRECTORY_SEPARATOR . 'modelDocGenerator-config.php';

    if (file_exists($configFile)) {
        break;
    }
}

if ( ! file_exists($configFile)) {
    ModelDocGenerator::printConfigTemplate();

    exit(1);
}

require $configFile;

// Run the package
ModelDocGenerator::execute($argv);