<?php

namespace JosKolenberg\LaravelJory\Register;

use Illuminate\Support\Collection;
use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Exceptions\ResourceNotFoundException;
use JosKolenberg\LaravelJory\JoryResource;

/**
 * Class JoryRecourcesRegister.
 *
 * Collects the registered JoryResources.
 */
class JoryResourcesRegister
{

    protected $joryResources;

    /**
     * Any additional registrars to deliver registrations
     *
     * @var array<RegistersJoryResources>
     */
    protected $registrars = [];

    /**
     * JoryResourcesRegister constructor.
     */
    public function __construct()
    {
        $this->joryResources = new Collection();
    }

    /**
     * Manually register a JoryResource.
     *
     * @param JoryResource $joryResource
     * @return JoryResourcesRegister
     */
    public function add(JoryResource $joryResource): JoryResourcesRegister
    {
        /**
         * Every resource has got to have a unique uri.
         * To be sure we filter out any previous
         * joryResources with the same uri.
         */
        $this->joryResources = $this->joryResources->filter(function ($existing) use ($joryResource) {
            return $existing->getUri() !== $joryResource->getUri();
        });

        /**
         * If we have multiple resources for the same related model, we want
         * the last one to be applied. This way any standard registered
         * resources can be overridden later. So prepend to the
         * front of the resource collection.
         */
        $this->joryResources->prepend($joryResource);

        return $this;
    }

    /**
     * Add a registrar for delivering registrations
     *
     * @param RegistersJoryResources $registrar
     */
    public function addRegistrar(RegistersJoryResources $registrar): void
    {
        $this->registrars[] = $registrar;
    }

    /**
     * Get a JoryResource by a model's class name.
     *
     * When multiple resources exist for the same model,
     * the last one registered will be used.
     *
     * @param string $modelClass
     * @return JoryResource
     */
    public function getByModelClass(string $modelClass): JoryResource
    {
        foreach ($this->getAllJoryResources() as $joryResource) {
            if ($joryResource->getModelClass() === $modelClass) {
                return $joryResource;
            }
        }

        throw new RegistrationNotFoundException('No joryResource found for model ' . $modelClass . '. Does ' . $modelClass . ' have an associated JoryResource?');
    }

    /**
     * @param string $uri
     * @return JoryResource
     */
    public function getByUri(string $uri): JoryResource
    {
        foreach ($this->getAllJoryResources() as $registration) {
            if ($registration->getUri() == $uri) {
                return $registration;
            }
        }

        throw new ResourceNotFoundException($uri);
    }

    /**
     * Get an sorted array of all registered uri's.
     *
     * @return array
     */
    public function getUrisArray(): array
    {
        $result = [];

        foreach ($this->getAllJoryResources()->sortBy(function($joryResource){
            return $joryResource->getUri();
        }) as $joryResource) {
            $result[] = $joryResource->getUri();
        }

        return $result;
    }

    /**
     * Get all registrations registered by all the registrars.
     *
     * @return Collection
     */
    public function getAllJoryResources(): Collection
    {
        // Manual registrations get precedence.
        $joryResources = $this->joryResources;

        foreach ($this->registrars as $registrar){
            $this->mergeJoryResources($joryResources, $registrar->getJoryResources());
        }

        return $joryResources;
    }

    /**
     * Merge the additional registration collection into the
     * subject collection and filter duplicates by uri.
     *
     * @param Collection $subject
     * @param Collection $additional
     */
    protected function mergeJoryResources(Collection $subject, Collection $additional)
    {
        foreach ($additional as $joryResource){
            /**
             * If the existing collection already has a joryResource for
             * this uri, don't register it again.
             */
            if($subject->contains(function ($existing) use ($joryResource) {
                return $existing->getUri() === $joryResource->getUri();
            })){
                continue;
            }

            $subject->push($joryResource);
        }
    }
}
