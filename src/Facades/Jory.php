<?php


namespace JosKolenberg\LaravelJory\Facades;


use Illuminate\Support\Facades\Facade;

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