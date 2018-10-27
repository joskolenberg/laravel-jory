<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBeforeQueryBuildFilterHook extends JoryBuilder
{
    protected function beforeQueryBuild($query, Jory $jory)
    {
        $query->where('title', 'like', '%love%');

        parent::beforeQueryBuild($query, $jory);
    }
}