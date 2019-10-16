<?php

namespace JosKolenberg\LaravelJory\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use JosKolenberg\LaravelJory\JoryServiceProvider;

class JoryPublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jory:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the Jory config file';

    public function handle()
    {
        Artisan::call('vendor:publish', [
            "--provider" => JoryServiceProvider::class,
        ]);

        $this->info(Artisan::output());
    }
}
