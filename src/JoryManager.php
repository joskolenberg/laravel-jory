<?php


namespace JosKolenberg\LaravelJory;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Register\JoryBuilderRegistration;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;
use JosKolenberg\LaravelJory\Responses\JoryMultipleResponse;
use JosKolenberg\LaravelJory\Responses\JoryResponse;


/**
 * Class JoryManager.
 *
 * The baseclass for the facade.
 */
class JoryManager
{

    /**
     * The multiple() call will resolve to a JoryMultipleResponse.
     *
     * @return JoryMultipleResponse
     * @throws BindingResolutionException
     */
    public function multiple(): JoryMultipleResponse
    {
        return new JoryMultipleResponse(app()->make('request'), app()->make(JoryBuildersRegister::class));
    }

    /**
     * The register() call will be sent to the JoryBuildersRegister.
     *
     * @param string $modelClass
     * @param string $builderClass
     * @return JoryBuilderRegistration
     * @throws BindingResolutionException
     */
    public function register(string $modelClass, string $builderClass): JoryBuilderRegistration
    {
        $registration = new JoryBuilderRegistration($modelClass, $builderClass);

        app()->make(JoryBuildersRegister::class)->add($registration);

        return $registration;
    }

    /**
     * Set the resource to be called based on the uri.
     *
     * @param string $uri
     * @return JoryResponse
     * @throws Exceptions\ResourceNotFoundException
     */
    public function byUri(string $uri): JoryResponse
    {
        return $this->getJoryResponse()->byUri($uri);
    }


    /**
     * Set the resource to be called based on the model class.
     *
     * @param string $modelClass
     * @return $this
     * @throws Exceptions\RegistrationNotFoundException
     * @throws LaravelJoryException
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
     * Set the resource to be called based on the model class.
     *
     * @param string $modelClass
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     */
    public function onModelClass(string $modelClass): JoryResponse
    {
        return $this->getJoryResponse()->onModelClass($modelClass);
    }

    /**
     * Set the resource to be called based on the model class.
     *
     * @param Model $model
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     */
    public function onModel(Model $model): JoryResponse
    {
        return $this->getJoryResponse()->onModel($model);
    }

    /**
     * Set the resource to be called based on the model class.
     *
     * @param Builder $query
     * @return JoryResponse
     * @throws Exceptions\RegistrationNotFoundException
     */
    public function onQuery(Builder $query): JoryResponse
    {
        return $this->getJoryResponse()->onQuery($query);
    }

    protected function getJoryResponse()
    {
        return new JoryResponse(app()->make('request'), app()->make(JoryBuildersRegister::class));
    }

}
