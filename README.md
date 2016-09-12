# Model Doc Generator

Create phpDocs for Eloquent models straight from the tables in your database. This package was inspired by
the [Automatic phpDocs for models](https://github.com/barryvdh/laravel-ide-helper#automatic-phpdocs-for-models) of barryvdh
for Laravel projects and uses parts of its code.

## Install
Require this package with composer using the following command:
```
composer require --dev pietmarcus/modeldocgenerator
```

## Configuration
Create a configuration file for the settings of this package and save it as modeldocgenerator-config.php in the root of your project or
in a config directory within the root of your project with the following contents:
```
<?php
use Illuminate\Database\Capsule\Manager as Capsule;

// Define the directory where the models reside.
// Remember to change the value if the config-file is in a config-directory
define('MODELS_DIRECTORY', __DIR__ . '/src/Models');

// Define the root namespace of the models
define('ROOT_NAMESPACE', 'App\Models');

// Load the global settings
$config    = require_once __DIR__ . '/src/settings.php';

// Boot Eloquent
$capsule   = new Capsule;
$capsule->addConnection($config['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
```

Edit the file as needed for your project.

## Generating documentation

Execute the generator with the following command from the root of your project:
```
vendor\bin\modelDocGenerator
```

By default models that already have a phpDoc will be skipped. To force overwriting existing documentation supply `--overwrite` to the command:
```
vendor\bin\modelDocGenerator --overwrite
```

## License

The Model Doc Generator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
