<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithAfterQueryOffsetLimitHook extends JoryBuilder
{
    protected function afterQueryBuild($query, Jory $jory)
    {
        $query->offset(2)->limit(3);

        parent::beforeQueryBuild($query, $jory);
    }
}