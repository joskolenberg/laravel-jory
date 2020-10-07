<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Filter\JoryResources;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\Feature\Filter\Scopes\BertAndErnieFilter;
use JosKolenberg\LaravelJory\Tests\Feature\Filter\Scopes\HasUserWithNameFilter;
use JosKolenberg\LaravelJory\Tests\Feature\Filter\Scopes\OscarFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\NameFilter;

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