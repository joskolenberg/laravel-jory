<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JosKolenberg\EloquentReflector\EloquentReflector;

class JoryResourceGenerateForCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jory:generate-for';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JoryResource for a model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'JoryResource';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/jory-resource.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->option('force')) && $this->alreadyExists($this->getNameInput())) {
            $this->error($this->getNameInput().' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->getNameInput().' created successfully.');
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        if ($this->option('name')) {
            return $this->option('name');
        }

        return class_basename($this->argument('model')).'JoryResource';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\JoryResources';
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub,
            $name)->replaceConfig($stub)->replaceModelClass($stub)->replaceClass($stub, $name);
    }

    /**
     * Replace the auto-generated configuration for the given stub.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceConfig(&$stub)
    {
        $stub = str_replace('DummyConfig', $this->getGeneratedConfig(), $stub);

        return $this;
    }

    /**
     * Replace the auto-generated configuration for the given stub.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceModelClass(&$stub)
    {
        $stub = str_replace('UseClass', 'use '.$this->argument('model').';', $stub);
        $stub = str_replace('DummyModelBaseClass', class_basename($this->argument('model')).'::class', $stub);

        return $this;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The model class to generate the JoryResource for (e.g. App\User)'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['name', 'j', InputOption::VALUE_OPTIONAL, 'Give the generated JoryResource another name when you don\'t want to use the default [model]JoryResource convention'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the JoryResource already exists'],
        ];
    }

    /**
     * Get the replacement code for auto-generated configuration.
     *
     * @return string
     */
    protected function getGeneratedConfig(): string
    {
        // Specify what one tab is.
        $tab = '    ';

        // Get model string and chek if it's supplied.
        $modelClass = $this->argument('model');

        // Create reflector to get attributes and relations from the modelClass.
        $reflector = new EloquentReflector($modelClass);

        // Generate fields configuration.
        $generatedCode = "$tab$tab// Fields\n";
        foreach ($reflector->getAttributes() as $attribute) {
            if ($attribute->custom) {
                /**
                 * Custom attributes (using accessors) cannot be sorted or filtered
                 * out of the box. So don't make them sortable or filterable.
                 * Often extra queries or heavy calculations are involved
                 * at custom attributes, don't return them by default.
                 */
                $generatedCode .= "$tab$tab\$this->field('".$attribute->name."')->hideByDefault();\n";
            } else {
                /**
                 * Standard attributes (database columns) can be sorted and filtered
                 * out of the box. So make them sortable and filterable.
                 */
                $generatedCode .= "$tab$tab\$this->field('".$attribute->name."')->filterable()->sortable();\n";
            }
        }

        // Generate relations configuration.
        $generatedCode .= "\n$tab$tab// Relations\n";
        foreach ($reflector->getRelationNames() as $relationName) {
            $generatedCode .= "$tab$tab\$this->relation('".$relationName."');\n";
        }

        // Remove last \n and return result.
        return substr($generatedCode, 0, -1);
    }
}
