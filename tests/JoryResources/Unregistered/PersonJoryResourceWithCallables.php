<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\Helpers\FilterHelper;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class PersonJoryResourceWithCallables extends JoryResource
{
    protected $modelClass = Person::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id');
        $this->field('first_name')->filterable(function (Filter $filter){
            $filter->scope(function ($builder, string $operator = null, $data = null){
                if($data['is_reversed']){
                    $data = strrev($data['value']);
                }

                FilterHelper::applyWhere($builder, 'first_name', $operator, $data);
            });
        });
        $this->field('last_name')->filterable()->sortable(function(Sort $sort){
            $sort->scope(function($builder, string $order = 'asc'){
                $builder->orderBy('last_name', $order === 'asc' ? 'desc' : 'asc');
            });
        });

        $this->filter('full_name', function ($builder, string $operator = null, $data = null){
            $builder->where('first_name', $operator, $data)
                ->orWhere('last_name', $operator, $data);
        });

        $this->sort('last_name_alias', function($builder, string $order = 'asc'){
            $builder->orderBy('last_name', $order);
        });
    }
}
