<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use JosKolenberg\EloquentReflector\EloquentReflector;
use JosKolenberg\EloquentReflector\Support\Relation;
use Symfony\Component\Finder\Finder;

class JoryResourceGenerateCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jory:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JoryResource';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'JoryResource';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'jory:generate {--force : Overwrite any existing files}
                    {--all : Generate a JoryResource for all models}
                    {--model= : The model class to generate the JoryResource for}
                    {--name= : Give the JoryResource an alternative class name}';

    /**
     * The model to generate for.
     *
     * @var string
     */
    protected $model = null;

    /**
     * The name of the JoryResource to be generated.
     *
     * @var string
     */
    protected $joryResourceClassName = null;

    /**
     * Whether existing files should be overwritten.
     *
     * @var bool
     */
    protected $force = false;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/jory-resource.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->determineWhatShouldBeGenerated();

        if(!$this->model){
            $this->generateAll();
            return;
        }

        $joryResourceFullClass = $this->qualifyClass($this->joryResourceClassName ?: class_basename($this->model) . 'JoryResource');

        $path = $this->getPath($joryResourceFullClass);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (!$this->force && $this->alreadyExists($joryResourceFullClass)) {
            $this->error(class_basename($joryResourceFullClass) . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($joryResourceFullClass));

        $this->info(class_basename($joryResourceFullClass) . ' created successfully.');
    }

    /**
     * Determine what should be generated.
     *
     * @return void
     */
    protected function determineWhatShouldBeGenerated()
    {
        $this->force = $this->option('force');

        if ($this->option('all')) {
            return;
        }

        $this->model = $this->option('model');

        if($this->model){
            $this->joryResourceClassName = $this->getNameInput();
            return;
        }

        $this->promptForModel();
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->option('name'));
    }

    /**
     * Prompt for which model to generate.
     *
     * @return void
     */
    protected function promptForModel()
    {
        $choice = $this->choice(
            "For which model would you like to generate?",
            $choices = $this->generatableChoices()
        );

        if ($choice == $choices[0] || is_null($choice)) {
            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * Parse the answer that was given via the prompt.
     *
     * @param  string  $choice
     * @return void
     */
    protected function parseChoice($choice)
    {
        if ($choice === 'All models') {
            return;
        }

        $this->model = $choice;
        $this->joryResourceClassName = $this->getNameInput();
    }

    /**
     * The choices available via the prompt.
     *
     * @return array
     */
    protected function generatableChoices()
    {
        return array_merge(
            ['All models'],
            $this->getModelClasses()
        );
    }

    /**
     * Get an array of all available models in the project.
     *
     * @return array
     */
    protected function getModelClasses()
    {
        $files = (new Finder())->files()->in($this->getModelsPath())->depth('== 0');

        $exludedModelClasses = config('jory.generator.models.exclude', []);

        $modelClasses = [];
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFilePath($file);

            if(in_array($className, $exludedModelClasses)){
                continue;
            }

            $modelClasses[] = $className;
        }

        asort($modelClasses);

        return $modelClasses;
    }

    /**
     * Generate a JoryResource for all models in the project.
     */
    protected function generateAll()
    {
        foreach ($this->getModelClasses() as $modelClass) {
            $this->call('jory:generate', [
                '--model' => $modelClass,
                '--force' => $this->force,
            ]);
        }
    }

    /**
     * Get the folder where models are stored.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getModelsPath()
    {
        return config('jory.generator.models.path');
    }

    /**
     * Get the namespace of the models folder.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getModelsNamespace()
    {
        return config('jory.generator.models.namespace');
    }

    /**
     * Convert the path name for a file to it's classname.
     *
     * @param $path
     * @return string
     */
    protected function getClassNameFromFilePath($path): string
    {
        // Example; $path = /home/vagrant/code/project/app/User.php

        $rootPath = $this->getModelsPath();
        $rootNameSpace = $this->getModelsNamespace();

        // Get filename relative to rootPath without extension, e.g. /User
        $className = str_replace($rootPath, '', substr($path, 0, -4));

        // Convert to backslashes and make all namespaces StudlyCased, e.g. \User
        $className = collect(explode(DIRECTORY_SEPARATOR, $className))->map(function ($namespace) {
            return Str::studly($namespace);
        })->implode('\\');

        // Return the classname prefixed with the rootPath's namespace, e.g. \App\User
        return $rootNameSpace.$className;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\JoryResources';
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
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
        $stub = str_replace('UseClass', 'use ' . $this->model . ';', $stub);
        $stub = str_replace('DummyModelBaseClass', class_basename($this->model) . '::class', $stub);

        return $this;
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
        $modelClass = $this->model;

        // Generate fields configuration.
        $generatedCode = "$tab$tab// Fields\n";
        foreach ($this->getModelFields($modelClass) as $attribute) {
            /**
             * Standard attributes (database columns) can be sorted and filtered
             * out of the box. So make them sortable and filterable.
             */
            $generatedCode .= "$tab$tab\$this->field('" . $attribute->name . "')->filterable()->sortable();\n";
        }

        // Generate custom attributes configuration.
        $customAttributes = $this->getModelCustomAttributes($modelClass);
        if ($customAttributes->isNotEmpty()) {
            $generatedCode .= "\n$tab$tab// Custom attributes\n";
            foreach ($customAttributes as $attribute) {
                /**
                 * Custom attributes (using accessors) cannot be sorted or filtered
                 * out of the box. So don't make them sortable or filterable.
                 * Often extra queries or heavy calculations are involved
                 * at custom attributes, don't return them by default.
                 */
                $generatedCode .= "$tab$tab\$this->field('" . $attribute->name . "')->hideByDefault();\n";
            }
        }

        // Generate relations configuration.
        $generatedCode .= "\n$tab$tab// Relations\n";
        foreach ($this->getModelRelationNames($modelClass) as $relationName) {
            $generatedCode .= "$tab$tab\$this->relation('" . $relationName . "');\n";
        }

        // Remove last \n and return result.
        return substr($generatedCode, 0, -1);
    }

    /**
     * Get all the database from a model class.
     *
     * @param $modelClass
     * @return \Illuminate\Support\Collection
     */
    protected function getModelFields($modelClass)
    {
        // Create reflector to get attributes and relations from the modelClass.
        $reflector = new EloquentReflector($modelClass);

        $attributes = $reflector->getAttributes()->filter(function ($attribute) {
            return !$attribute->custom;
        });

        return $attributes;
    }

    /**
     * Get all custom attributes from a model class.
     *
     * @param $modelClass
     * @return \Illuminate\Support\Collection
     */
    protected function getModelCustomAttributes($modelClass)
    {
        // Create reflector to get attributes and relations from the modelClass.
        $reflector = new EloquentReflector($modelClass);

        $attributes = $reflector->getAttributes()->filter(function ($attribute) {
            return $attribute->custom;
        })->sortBy(function ($attribute) {
            return $attribute->name;
        });

        return $attributes;
    }

    /**
     * Get all relation names from a model class.
     *
     * @param $modelClass
     * @return array
     */
    protected function getModelRelationNames($modelClass)
    {
        // Create reflector to get attributes and relations from the modelClass.
        $reflector = new EloquentReflector($modelClass);

        return $reflector->getRelations()->filter(function (Relation $relation) {
            return in_array(Str::camel($relation->type), $this->getSupportedRelationTypes());
        })->sortBy(function (Relation $relation) {
            return $relation->name;
        })->pluck('name')
            ->toArray();
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return config('jory.generator.jory-resources.namespace');
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->getNamespace($name), '', $name);

        return config('jory.generator.jory-resources.path') . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Get all available relation methods.
     *
     * @return array
     */
    protected function getSupportedRelationTypes(): array
    {
        return [
            'hasOne',
            'belongsTo',
            'hasMany',
            'belongsToMany',
            'hasManyThrough',
            'hasOneThrough',
            'morphOne',
            'morphMany',
            'morphToMany',
            'morphedByMany',
        ];
    }
}
