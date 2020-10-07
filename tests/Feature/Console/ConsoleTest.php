<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Console;

use JosKolenberg\LaravelJory\Tests\Feature\Console\Models\Band;
use JosKolenberg\LaravelJory\Tests\Feature\Console\Models\Musician;
use JosKolenberg\LaravelJory\Tests\Models\User;
use JosKolenberg\LaravelJory\Tests\TestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class ConsoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanup();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('jory.generator.models', [
            'namespace' => 'JosKolenberg\LaravelJory\Tests\Feature\Console\Models',
            'path' => __DIR__ . DIRECTORY_SEPARATOR . 'Models',
            'exclude' => [
                Musician::class,
            ]
        ]);
        $app['config']->set('jory.generator.jory_resources', [
            'namespace' => 'App\Http\JoryResources',
            'path' => __DIR__ . DIRECTORY_SEPARATOR . 'Generated',
        ]);
    }

    /** @test */
    public function it_can_run_a_generate_command_for_a_model()
    {
        $this->artisan('jory:generate', ['--model' => Band::class])
            ->expectsOutput('BandJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/Original'));
        $expectedContentsLocal = $filesystem->read('BandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/Generated'));
        $realContents = $filesystem->read('BandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_for_a_model_with_name_option()
    {
        $this->artisan('jory:generate', [
            '--model' => Band::class,
            '--name' => 'AlternateBandJoryResource'
        ])->expectsOutput('AlternateBandJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/Original'));
        $expectedContentsLocal = $filesystem->read('AlternateBandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/Generated'));
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_without_a_model_to_be_prompted_for_the_model()
    {
        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', Band::class)
            ->expectsOutput('BandJoryResource created successfully.');
    }

    /** @test */
    public function it_can_run_a_generate_command_for_all_models_and_excludes_models_from_the_exclude_list()
    {
        $this->artisan('jory:generate', [
            '--all' => true
        ])->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('TeamJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/Original'));

        $generatedFilesystem = new Filesystem(new Local(__DIR__ . '/Generated'));

        $this->assertTrue($filesystem->read('BandJoryResource.php') === $generatedFilesystem->read('BandJoryResource.php'));
        $this->assertTrue($filesystem->read('TeamJoryResource.php') === $generatedFilesystem->read('TeamJoryResource.php'));
        $this->assertTrue($filesystem->read('UserJoryResource.php') === $generatedFilesystem->read('UserJoryResource.php'));

        $this->assertFalse($generatedFilesystem->has('MusicianJoryResource.php')); // Excluded in config
    }

    /** @test */
    public function it_can_run_a_generate_command_for_all_models_using_the_selector()
    {
        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', '<comment>All models</comment>')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('TeamJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.');
    }

    /** @test */
    public function it_can_use_the_force_option_to_override_existing_files()
    {
        $this->artisan('jory:generate', [
            '--all' => true,
        ]);

        $adapter = new Local(__DIR__ . '/Generated');
        $filesystem = new Filesystem($adapter);
        $filesystem->delete('TeamJoryResource.php');

        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', '<comment>All models</comment>')
            ->expectsOutput('BandJoryResource already exists!')
            ->expectsOutput('TeamJoryResource created successfully.')
            ->expectsOutput('UserJoryResource already exists!');

        $this->artisan('jory:generate', [
            '--force' => true,
        ])->expectsQuestion('For which model would you like to generate?', '<comment>All models</comment>')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('TeamJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.');
    }

    /** @test */
    public function it_doesnt_configure_fields_which_are_marked_to_be_excluded()
    {
        /**
         * Already gets tested when generating the UserJoryResource
         * in other tests which excludes the password field.
         */
        $this->assertTrue(true);
    }

    protected function cleanup()
    {
        // Remove all previously built JoryResources
        $adapter = new Local(__DIR__);
        $filesystem = new Filesystem($adapter);

        foreach ($filesystem->listContents('Generated') as $file){
            if($file['basename'] !== '.gitignore'){
                $filesystem->delete($file['path']);
            }
        }
    }
}