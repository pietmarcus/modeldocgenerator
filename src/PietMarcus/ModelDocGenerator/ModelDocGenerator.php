<?php
namespace PietMarcus\ModelDocGenerator;

/**
 * Class ModelDocGenerator
 * @package PietMarcus\ModelDocGenerator
 */
class ModelDocGenerator {

    /**
     * @var DocModel[]
     */
    var $models = [];

    /**
     * @var bool
     */
    var $overwrite = false;

    /**
     * Execute the package
     *
     * @param string[] $arguments
     */
    static function execute($arguments) {
        $modelDocGenerator = new ModelDocGenerator();

        if (in_array('--overwrite', $arguments)) {
            $modelDocGenerator->overwrite = true;
        }

        if (in_array('--help', $arguments) || in_array('--h', $arguments)) {
            $modelDocGenerator->printHelp();
            exit;
        }

        $modelDocGenerator->generateDocBlocks();
    }

    /**
     * Print the help text.
     */
    private function printHelp() {
        print "Generate class documentation based on columns in the database." . PHP_EOL .
            "Parameters:" . PHP_EOL .
            "    --overwrite : Overwrite existing documentation." . PHP_EOL .
            PHP_EOL;
    }

    /**
     * Generate doc-blocks for the models.
     */
    private function generateDocBlocks() {
        $this->findModels(MODELS_DIRECTORY);

        foreach ($this->models as $model) {
            $this->setProperties($model);

            $docBlock = $this->generateDocBlock($model);

            $this->writeDocBlock($model, $docBlock);
        }
    }

    /**
     * Write the new docBlock to the model file
     *
     * @param DocModel $docModel
     * @param $newDocBlock
     */
    private function writeDocBlock(DocModel $docModel, $newDocBlock) {
        /* Skip if class has phpDoc and overwrite param is not given */
        if (!$this->overwrite && $docModel->phpDoc != '') {
            print "Skipping " . $docModel->filename . PHP_EOL;
            return;
        }

        $contents = file_get_contents(($docModel->filename));

        if ($docModel->phpDoc == '') {
            $needle = "class {$docModel->classname}";
            $replace = "{$newDocBlock}\nclass {$docModel->classname}";
            $pos = strpos($contents, $needle);
            if ($pos !== false) {
                $contents = substr_replace($contents, $replace, $pos, strlen($needle));
            }
        } else {
            $contents = str_replace($docModel->phpDoc, $newDocBlock, $contents);
        }

        print "Writing new docBlock to " . $docModel->filename . PHP_EOL;

        file_put_contents($docModel->filename, $contents);
    }

    /**
     * Generate the docBlock for the model
     *
     * @param DocModel $docModel
     * @return string
     */
    private function generateDocBlock(DocModel $docModel) {
        $docBlock = '/**' . PHP_EOL .
            ' * Class ' . $docModel->classname . PHP_EOL .
            ' * @package ' . $docModel->namespace . PHP_EOL .
            ' *' . PHP_EOL
        ;

        foreach ($docModel->properties as $property) {
            $docBlock .= ' * ' . trim("@property $property->type $property->name $property->comment") . PHP_EOL;
        }

        $docBlock .= ' *' . PHP_EOL;
        $docBlock .= ' * @mixin Model' . PHP_EOL;
        $docBlock .= ' * @mixin Illuminate\Database\Query\Builder' . PHP_EOL;

        $docBlock .=
            ' */';

        return $docBlock;
    }

    /**
     * Find model-files recursively in directory
     *
     * @param string $directory
     */
    private function findModels($directory) {
        $files = glob($directory . DIRECTORY_SEPARATOR . "*");

        foreach ($files as $file) {
            if (is_file($file)) {
                try {
                    if (strtolower(substr($file, -4)) != '.php') {
                        continue;
                    }
                    $subDirectory = substr(dirname($file), strlen(MODELS_DIRECTORY));
                    $modelName = basename($file, '.php');
                    $modelFullname = ROOT_NAMESPACE . $subDirectory . '\\' . $modelName;
                    $reflectionClass = new \ReflectionClass($modelFullname);

                    if (!$reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                        // only work on Models
                        continue;
                    }



                    if (!$reflectionClass->isInstantiable()) {
                        // ignore abstract class or interface
                        continue;
                    }

                    $docModel = new DocModel();
                    $docModel->classname = $modelName;
                    $docModel->fullname = $modelFullname;
                    $docModel->filename = $file;
                    $docModel->namespace = $reflectionClass->getNamespaceName();
                    $docModel->phpDoc = $reflectionClass->getDocComment();

                    $this->models[] = $docModel;

                } catch (\Exception $e) {
                    print "Exception: " . $e->getMessage() . PHP_EOL;
                }
            } else {
                // Work on directories recursively
                $this->findModels($file);
            }
        }
    }

    /**
     * Get all the properties of a model
     *
     * @param $model
     */
    private function setProperties($model) {
        $modelObject = new $model->fullname();

        $table = $modelObject->getConnection()->getTablePrefix() . $modelObject->getTable();
        $schema = $modelObject->getConnection()->getDoctrineSchemaManager($table);
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $platformName = $databasePlatform->getName();

        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }

        $columns = $schema->listTableColumns($table, $database);

        $model->properties = [];

        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                if (in_array($name, $modelObject->getDates())) {
                    $type = '\Carbon\Carbon';
                } else {
                    $type = $column->getType()->getName();
                    switch ($type) {
                        case 'string':
                        case 'text':
                        case 'date':
                        case 'time':
                        case 'guid':
                        case 'datetimetz':
                        case 'datetime':
                            $type = 'string';
                            break;
                        case 'integer':
                        case 'bigint':
                        case 'smallint':
                            $type = 'integer';
                            break;
                        case 'decimal':
                        case 'float':
                            $type = 'float';
                            break;
                        case 'boolean':
                            $type = 'boolean';
                            break;
                        default:
                            $type = 'mixed';
                            break;
                    }
                }
                $comment = $column->getComment();

                $docProperty = new DocProperty();
                $docProperty->name = $name;
                $docProperty->type = $type;
                $docProperty->comment = $comment;

                $model->properties[] = $docProperty;
            }
        }
    }

    /**
     * Print information about the configuration-file
     */
    static function printConfigTemplate() {
        print <<<'INFO'
You are missing a "modelDocGenerator-config.php" or "config/modelDocGenerator-config.php" file in your
project, which is required to get modelDocGenerator working. You can use the
following sample as a template:

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

INFO;
    }
}
