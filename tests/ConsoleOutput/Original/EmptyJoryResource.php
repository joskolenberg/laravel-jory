<?php

namespace App\Http\JoryResources;

use JosKolenberg\LaravelJory\JoryResource;


class EmptyJoryResource extends JoryResource
{
    protected $modelClass = '';

    /**
     * Configure the JoryResource.
     *
     * @return void
     */
    protected function configure(): void
    {
        // Configure the jory resource...
    }
}
