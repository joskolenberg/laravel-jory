<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

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
            return __DIR__.'/stubs/jory-builder-example.stub.stub';
        }

        return __DIR__.'/stubs/jory-builder.stub.stub';
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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', 'e', InputOption::VALUE_NONE, 'Create a new JoryBuilder class with example data'],
        ];
    }
}
