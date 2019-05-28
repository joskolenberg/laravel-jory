<?php


namespace JosKolenberg\LaravelJory;

use Illuminate\Contracts\Container\BindingResolutionException;
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
     * Proxy all other calls to a new JoryResponse.
     *
     * @param $method
     * @param $args
     * @return mixed
     * @throws BindingResolutionException
     */
    public function __call($method, $args)
    {
        $response = new JoryResponse(app()->make('request'), app()->make(JoryBuildersRegister::class));

        return $response->{$method}(...$args);
    }

}
