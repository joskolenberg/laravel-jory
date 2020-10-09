<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Filter\JoryResources;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Tests\Feature\Query\Filter\Scopes\BertAndErnieFilter;
use JosKolenberg\LaravelJory\Tests\Feature\Query\Filter\Scopes\OscarFilter;

class UserJoryResource extends \JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource
{
    protected function configure(): void
    {
        parent::configure();


        $this->field('oscar')->filterable(function (Filter $filter){
            $filter->scope(new OscarFilter);
        });
        $this->filter('bert_and_ernie', new BertAndErnieFilter);
    }
}