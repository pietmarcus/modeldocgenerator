<?php
/**
 * Created by PhpStorm.
 * User: Piet  Marcus
 * Date: 11-9-2016
 * Time: 19:39
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use PietMarcus\ModelDocGenerator\GenModelProperties;

$cwd = getcwd();

define('MODELS_DIRECTORY', $cwd . '\src\Models');
define('ROOT_NAMESPACE', 'App\Models');

$files       = array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php');
$loader      = null;

foreach ($files as $file) {
    if (file_exists($file)) {
        $loader = require $file;

        break;
    }
}

if ( ! $loader) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

$config    = require_once __DIR__ . '/../../../../src/settings.php';
$capsule   = new Capsule;
$capsule->addConnection($config['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$genModelProperties = new GenModelProperties();
$genModelProperties->execute($argv);