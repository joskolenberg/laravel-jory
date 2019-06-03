<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JosKolenberg\EloquentReflector\EloquentReflector;

class JoryResourceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:jory-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JoryResource';

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
        // A call with a given --model option is the same as a jory:generate-for
        // command with a --name option. So use the existing command.
        if ($this->option('model')) {
            return $this->call('jory:generate-for', [
                'model' => $this->option('model'),
                '--name' => $this->argument('name'),
            ]);
        }

        return parent::handle();
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
        $stub = str_replace('DummyConfig', '        // Configure the jory resource...', $stub);

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
        $stub = str_replace('UseClass', '', $stub);
        $stub = str_replace('DummyModelBaseClass', "''", $stub);

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
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Give the generated JoryResource another name when you don\'t want to use the default [model]JoryResource convention'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the JoryResource already exists'],
        ];
    }
}
