<?php


namespace JosKolenberg\LaravelJory\Facades;


use Illuminate\Support\Facades\Facade;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Register\JoryBuilderRegistration;
use JosKolenberg\LaravelJory\Responses\JoryMultipleResponse;
use JosKolenberg\LaravelJory\Responses\JoryResponse;

/**
 * Class Jory
 * @package JosKolenberg\LaravelJory\Facades
 *
 * @method static JoryResponse byUri(string $uri)
 * @method static JoryResponse onModelClass(string $modelClass)
 * @method static JoryMultipleResponse multiple()
 * @method static JoryResponse count()
 * @method static JoryResponse find($modelId)
 * @method static JoryResponse applyJson(string $json)
 * @method static JoryResponse applyArray(array $array)
 * @method static JoryResponse getResult()
 * @method static JoryBuilder getProcessedBuilder()
 * @method static toResponse($request)
 * @method static JoryBuilderRegistration register(string $modelClass, string $builderClass)
 */
class Jory extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'jory';
    }

}