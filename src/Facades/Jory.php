<?php


namespace JosKolenberg\LaravelJory\Facades;


use Illuminate\Support\Facades\Facade;
use JosKolenberg\LaravelJory\Responses\JoryMultipleResponse;
use JosKolenberg\LaravelJory\Responses\JoryResponse;

/**
 * Class Jory
 * @package JosKolenberg\LaravelJory\Facades
 *
 * @method static JoryResponse byUri(string $uri)
 * @method static JoryResponse byModel(string $model)
 * @method static JoryMultipleResponse multiple()
 * @method static JoryResponse count()
 * @method static JoryResponse find($modelId)
 * @method static JoryResponse applyJson(string $json)
 * @method static JoryResponse applyArray(array $array)
 * @method static JoryResponse getResult()
 * @method static toResponse($request)
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