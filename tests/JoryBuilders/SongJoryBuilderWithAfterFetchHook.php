<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithAfterFetchHook extends JoryBuilder
{

    protected function afterFetch(Collection $collection, Jory $jory): Collection
    {
        $collection = $collection->filter(function ($model) {
            return ($model->id > 100);
        });

        return parent::afterFetch($collection, $jory);
    }
}