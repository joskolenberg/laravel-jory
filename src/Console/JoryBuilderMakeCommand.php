<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use JosKolenberg\EloquentReflector\EloquentReflector;

class JoryBuilderMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jory-builder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JoryBuilder class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'JoryBuilder';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/jory-builder.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\JoryBuilders';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceConfig($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the auto-generated configuration for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceConfig(&$stub, $name)
    {
        $stub = str_replace('DummyConfig', $this->getGeneratedConfig(), $stub);

        return $this;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Generate default configuration based on a model'],
        ];
    }

    /**
     * Get the replacement code for auto-generated configuration.
     *
     * @return string
     */
    protected function getGeneratedConfig(): string
    {
        // Get model string and chek if it's supplied.
        $modelClass = $this->option('model');
        if(!$modelClass){
            return "\t\t// Configure the builder...";
        }

        // Create reflector to get attributes and relations from the modelClass.
        $reflector = new EloquentReflector($modelClass);

        // Generate fields configuration.
        $generatedCode = "\t\t// Fields\n";
        foreach ($reflector->getAttributes() as $attribute) {
            if($attribute->custom){
                // Custom attributes (using accessors) cannot be sorted or filtered out
                // of the box. So don't make them sortable or filterable.
                $generatedCode .= "\t\t\$config->field('".$attribute->name."');\n";
            }else{
                // Standard attributes (database columns) can be sorted and filtered
                // out of the box. So make them sortable ande filterable.
                $generatedCode .= "\t\t\$config->field('".$attribute->name."')->filterable()->sortable();\n";
            }
        }

        // Generate relations configuration.
        $generatedCode .= "\n\t\t// Relations\n";
        foreach ($reflector->getRelationNames() as $relationName) {
            $generatedCode .= "\t\t\$config->relation('".$relationName."');\n";
        }

        // Remove last \n and return result.
        return substr($generatedCode, 0, -1);
    }
}
