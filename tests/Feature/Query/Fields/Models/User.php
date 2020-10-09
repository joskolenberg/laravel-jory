<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Fields\Models;


class User extends \JosKolenberg\LaravelJory\Tests\DefaultModels\User
{
    public function getCustomValueAttribute()
    {
        return 'custom value';
    }
}