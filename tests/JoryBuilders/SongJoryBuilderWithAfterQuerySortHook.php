<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithAfterQuerySortHook extends JoryBuilder
{
    protected function afterQueryBuild($query, Jory $jory)
    {
        $query->orderBy('title', 'desc');

        parent::beforeQueryBuild($query, $jory);
    }
}