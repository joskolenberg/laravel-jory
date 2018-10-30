<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithAfterQueryBuildFilterHook extends JoryBuilder
{
    protected function afterQueryBuild($query, Jory $jory)
    {
        parent::afterQueryBuild($query, $jory);

        $query->where('title', 'like', '%love%');
    }
}