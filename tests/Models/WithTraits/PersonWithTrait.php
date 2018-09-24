<?php

namespace JosKolenberg\LaravelJory\Tests\Models\WithTraits;


use JosKolenberg\LaravelJory\Traits\JoryTrait;

class PersonWithTrait extends \JosKolenberg\LaravelJory\Tests\Models\Person
{
    use JoryTrait;

    protected $table = 'people';
}