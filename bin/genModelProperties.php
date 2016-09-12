<?php
/**
 * Created by PhpStorm.
 * User: Piet  Marcus
 * Date: 11-9-2016
 * Time: 19:39
 */

use PietMarcus\ModelDocGenerator\GenModelProperties;

$cwd         = getcwd();
$files       = array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php');
$loader      = null;
$directories = array($cwd, $cwd . DIRECTORY_SEPARATOR . 'config');
$configFile  = null;

foreach ($files as $file) {
    if (file_exists($file)) {
        $loader = require $file;

        break;
    }
}

if ( ! $loader) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

foreach ($directories as $directory) {
    $configFile = $directory . DIRECTORY_SEPARATOR . 'genModelProperties-config.php';

    if (file_exists($configFile)) {
        break;
    }
}

if ( ! file_exists($configFile)) {
    GenModelProperties::printConfigTemplate();

    exit(1);
}

require $configFile;

GenModelProperties::execute($argv);