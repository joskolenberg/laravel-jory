<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBeforeQueryBuildFilterHook extends JoryBuilder
{
    protected function beforeQueryBuild($query, Jory $jory, $count = false)
    {
        parent::beforeQueryBuild($query, $jory);

        $query->where('title', 'like', '%love%');
    }
}