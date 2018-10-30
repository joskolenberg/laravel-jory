<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;
use Illuminate\Database\Eloquent\Collection;

class SongJoryBuilderWithAfterFetchHook extends JoryBuilder
{

    protected function afterFetch(Collection $collection, Jory $jory): Collection
    {
        $collection = parent::afterFetch($collection, $jory);

        return $collection->filter(function ($model) {
            return ($model->id > 100);
        });

    }
}