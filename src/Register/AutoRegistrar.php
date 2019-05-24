<?php


namespace JosKolenberg\LaravelJory\Register;


use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class AutoRegistrar
 *
 * Automatically registers JoryBuilders which follow the standard <ModelName>JoryBuilder naming convention
 */
class AutoRegistrar implements RegistrarInterface
{

    /**
     * The discovered JoryBuilders
     *
     * @var Collection
     */
    protected $registrations;

    /**
     * AutoRegistrar constructor.
     */
    public function __construct()
    {
        $this->registrations = new Collection();

        $this->discoverJoryBuilders();
    }

    /**
     * Get all registered registrations.
     *
     * @return Collection
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    /**
     * Try to find the and register all the JoryBuilders
     */
    protected function discoverJoryBuilders(): void
    {
        $files = (new Finder())->files()->in(config('jory.auto-registrar.models-path'));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->discoverJoryBuilderForModelFile($file);
        }
    }

    /**
     * If we can find a JoryBuilder for the given file, we'll register it.
     *
     * @param SplFileInfo $file
     */
    protected function discoverJoryBuilderForModelFile(SplFileInfo $file): void
    {
        $joryBuilderFilePath = config('jory.auto-registrar.jory-builders-path') . DIRECTORY_SEPARATOR . $file->getBasename('.php') . 'JoryBuilder.php';

        if (!file_exists($joryBuilderFilePath)) {
            return;
        }

        $this->registrations->push(new JoryBuilderRegistration(
            $this->getClassNameFromFilePath($file->getRealPath()),
            $this->getClassNameFromFilePath($joryBuilderFilePath)
        ));
    }

    /**
     * Convert the path name for a file to it's classname.
     *
     * @param $path
     * @return string
     */
    protected function getClassNameFromFilePath($path): string
    {
        // Example; $path = /home/vagrant/code/project/app/Http/JoryBuilders/UserJoryBuilder.php

        $rootPath = config('jory.auto-registrar.root-path');
        $rootNameSpace = config('jory.auto-registrar.root-namespace');

        // Get filename relative to rootPath without extension, e.g. /Http/JoryBuilders/UserJoryBuilder
        $className = str_replace($rootPath, '', substr($path, 0, -4));

        // Convert to backslashes and make all namespaces StudlyCased, e.g. \Http\JoryBuilders\UserJoryBuilder
        $className = collect(explode(DIRECTORY_SEPARATOR, $className))
            ->map(function($namespace){
                return Str::studly($namespace);
            })
            ->implode('\\');

        // Return the classname prefixed with the rootPath's namespace, e.g. \App\Http\JoryBuilders\UserJoryBuilder
        return $rootNameSpace . $className;
    }
}