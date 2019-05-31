<?php


namespace JosKolenberg\LaravelJory;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Register\JoryResourceRegistration;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Responses\JoryMultipleResponse;
use JosKolenberg\LaravelJory\Responses\JoryResponse;


/**
 * Class JoryManager.
 *
 * The base class for the facade.
 */
class JoryManager
{

    /**
     * The multiple() call will resolve to a clean JoryMultipleResponse.
     *
     * @return JoryMultipleResponse
     */
    public function multiple(): JoryMultipleResponse
    {
        return new JoryMultipleResponse(app()->make('request'), app()->make(JoryResourcesRegister::class));
    }

    /**
     * Register a JoryResource using the facade.
     *
     * @param string $joryResourceClass
     * @return JoryResourcesRegister
     */
    public function register(string $joryResourceClass): JoryResourcesRegister
    {
        return app()->make(JoryResourcesRegister::class)->add(new $joryResourceClass());
    }

    /**
     * Create a new response based on the public uri.
     *
     * @param string $uri
     * @return JoryResponse
     * @throws Exceptions\ResourceNotFoundException
     * @throws BindingResolutionException
     */
    public function byUri(string $uri): JoryResponse
    {
        return $this->getJoryResponse()->byUri($uri);
    }


    /**
     * Helper method to create a new response based on
     * a model instance, a model's class name or existing query.
     *
     * @param mixed $resource
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     * @throws LaravelJoryException
     * @throws BindingResolutionException
     */
    public function on($resource): JoryResponse
    {
        $response = $this->getJoryResponse();
        if($resource instanceof Model){
            return $response->onModel($resource);
        }

        if($resource instanceof Builder){
            return $response->onQuery($resource);
        }

        if(!is_string($resource)){
            throw new LaravelJoryException('Unexpected type given. Please provide a model instance, Eloquent builder instance or a model\'s class name.');
        }

        return $response->onModelClass($resource);
    }

    /**
     * Create a new response based on a model's class name.
     *
     * @param string $modelClass
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     * @throws BindingResolutionException
     */
    public function onModelClass(string $modelClass): JoryResponse
    {
        return $this->getJoryResponse()->onModelClass($modelClass);
    }

    /**
     * Create a new response based on a model instance.
     *
     * @param Model $model
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     * @throws BindingResolutionException
     */
    public function onModel(Model $model): JoryResponse
    {
        return $this->getJoryResponse()->onModel($model);
    }

    /**
     * Create a new response based on an existing query.
     *
     * @param Builder $query
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     * @throws BindingResolutionException
     */
    public function onQuery(Builder $query): JoryResponse
    {
        return $this->getJoryResponse()->onQuery($query);
    }

    /**
     * Get a fresh JoryResponse.
     *
     * @return JoryResponse
     * @throws BindingResolutionException
     */
    protected function getJoryResponse(): JoryResponse
    {
        return new JoryResponse(app()->make('request'), app()->make(JoryResourcesRegister::class));
    }

}
