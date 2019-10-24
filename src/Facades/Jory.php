<?php


namespace JosKolenberg\LaravelJory\Facades;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use JosKolenberg\LaravelJory\Register\JoryResourceRegistration;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Responses\JoryMultipleResponse;
use JosKolenberg\LaravelJory\Responses\JoryResponse;

/**
 * Class Jory
 * @package JosKolenberg\LaravelJory\Facades
 *
 * @method static JoryResponse on($resource)
 * @method static JoryResponse onModel(Model $model)
 * @method static JoryResponse onModelClass(string $modelClass)
 * @method static JoryResponse onQuery(Builder $builder)
 * @method static JoryResponse byUri(string $uri)
 * @method static JoryMultipleResponse multiple()
 * @method static JoryResourcesRegister register(string $joryResourceClass)
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