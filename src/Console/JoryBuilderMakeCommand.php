<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        if ($this->option('example')) {
            return __DIR__.'/stubs/jory-builder-example.stub';
        }

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
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceConfig(&$stub, $name)
    {
        $stub = str_replace('GeneratedConfig', $this->getGeneratedConfig(), $stub);

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
            ['example', 'e', InputOption::VALUE_NONE, 'Create a new JoryBuilder class with example data'],

            ['model', 'e', InputOption::VALUE_REQUIRED, 'Generate default configuration based on a model'],
        ];
    }

    protected function getGeneratedConfig()
    {
        $modelClass = $this->input->getOption('model');

        $modelClass = $this->qualifyClass($modelClass);

        if(class_exists($modelClass)){
            die('d');
        }else{
            die('44');
        }
    }
}
