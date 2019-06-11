<?php


namespace JosKolenberg\LaravelJory\Register;


use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\JoryResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class AutoRegistrar
 *
 * Automatically registers JoryResources which follow the <ModelName>JoryResource naming convention
 */
class AutoRegistrar implements RegistersJoryResources
{

    /**
     * The discovered JoryResources
     *
     * @var Collection
     */
    protected $joryResources;

    /**
     * AutoRegistrar constructor.
     */
    public function __construct()
    {
        $this->joryResources = new Collection();

        $this->discoverJoryResources();
    }

    /**
     * Get all registered registrations.
     *
     * @return Collection
     */
    public function getJoryResources(): Collection
    {
        return $this->joryResources;
    }

    /**
     * Try to find the and register all the JoryResources
     */
    protected function discoverJoryResources(): void
    {
        if(!file_exists(config('jory.auto-registrar.path'))){
            return;
        }

        $files = (new Finder())->files()->in(config('jory.auto-registrar.path'))->depth('== 0');

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->discoverJoryResourceForFile($file);
        }
    }

    /**
     * If we can find a JoryResource for the given file, we'll register it.
     *
     * @param SplFileInfo $file
     */
    protected function discoverJoryResourceForFile(SplFileInfo $file): void
    {
        $className = $this->getClassNameFromFilePath($file->getRealPath());

        $joryResource = new $className();

        if($joryResource instanceof JoryResource){
            $this->joryResources->push($joryResource);
        }
    }

    /**
     * Convert the path name for a file to it's classname.
     *
     * @param $path
     * @return string
     */
    protected function getClassNameFromFilePath($path): string
    {
        // Example; $path = /home/vagrant/code/project/app/Http/JoryResources/UserJoryResource.php

        $rootPath = config('jory.auto-registrar.path');
        $rootNameSpace = config('jory.auto-registrar.namespace');

        // Get filename relative to rootPath without extension, e.g. /Http/JoryResources/UserJoryResource
        $className = str_replace($rootPath, '', substr($path, 0, -4));

        // Convert to backslashes and make all namespaces StudlyCased, e.g. \Http\JoryResources\UserJoryResource
        $className = collect(explode(DIRECTORY_SEPARATOR, $className))
            ->map(function($namespace){
                return Str::studly($namespace);
            })
            ->implode('\\');

        // Return the classname prefixed with the rootPath's namespace, e.g. \App\Http\JoryResources\UserJoryResource
        return $rootNameSpace . $className;
    }
}