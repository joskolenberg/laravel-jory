<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

class JoryResourceGenerateAllCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jory:generate-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JoryResource for all models';

    public function handle()
    {
        foreach ($this->getModelClasses() as $modelClass) {
            $this->call('jory:generate-for', [
                'model' => $modelClass,
                '--force' => $this->option('force')
            ]);
        }
    }

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

        return $modelClasses;
    }

    protected function getModelsPath()
    {
        return config('jory.generator.models.path');
    }

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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the JoryResource already exists'],
        ];
    }
}
