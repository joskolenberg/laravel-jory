<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;

/**
 * Class GrrrroupieJoryBuilder
 *
 * Jorybuilder with spelling mistake for testing errors.
 *
 * @package JosKolenberg\LaravelJory\Tests\JoryBuilders
 */
class GrrrroupieJoryBuilder extends JoryBuilder
{

    /**
     * Configure the JoryBuilder.
     *
     * @param Config $config
     */
    protected function config(Config $config): void
    {
    }
}
