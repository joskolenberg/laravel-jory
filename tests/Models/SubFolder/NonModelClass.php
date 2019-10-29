<?php


namespace JosKolenberg\LaravelJory\Tests\Models\SubFolder;


use Illuminate\Routing\Controller;

class NonModelClass extends Controller
{
    // There should no JoryResource be generated for this class because it isn't a model

    public function __construct()
    {
        throw new \Exception('A class should not be instantiated when auto detecting model classes');
    }
}