<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\Tests\Models\User;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class ConsoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanup();
    }

    /** @test */
    public function it_can_run_a_generate_command_for_a_model()
    {
        $this->artisan('jory:generate', ['--model' => 'JosKolenberg\LaravelJory\Tests\Models\Band'])
            ->expectsOutput('BandJoryResource created successfully.');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('BandJoryResource.php');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('BandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_for_a_model_with_name_option()
    {
        $this->artisan('jory:generate', [
            '--model' => 'JosKolenberg\LaravelJory\Tests\Models\Band',
            '--name' => 'AlternateBandJoryResource'
        ])->expectsOutput('AlternateBandJoryResource created successfully.');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('AlternateBandJoryResource.php');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_without_a_model_to_be_prompted_for_the_model()
    {
        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', 'JosKolenberg\LaravelJory\Tests\Models\Song')
            ->expectsOutput('SongJoryResource created successfully.');
    }

    /** @test */
    public function it_can_run_a_generate_command_for_all_models()
    {
        $this->artisan('jory:generate', [
            '--all' => true
        ])->expectsOutput('AlbumCoverJoryResource created successfully.')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('ImageJoryResource created successfully.')
            ->expectsOutput('PersonJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.')
            ->expectsOutput('SongWithCustomJoryResourceJoryResource created successfully.')
            ->expectsOutput('AlbumJoryResource created successfully.')
            ->expectsOutput('TagJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Original'));

        $generatedFilesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Generated'));

        $this->assertTrue($filesystem->read('AlbumJoryResource.php') === $generatedFilesystem->read('AlbumJoryResource.php'));
        $this->assertTrue($filesystem->read('BandJoryResource.php') === $generatedFilesystem->read('BandJoryResource.php'));
        $this->assertTrue($filesystem->read('PersonJoryResource.php') === $generatedFilesystem->read('PersonJoryResource.php'));
        $this->assertTrue($filesystem->read('ImageJoryResource.php') === $generatedFilesystem->read('ImageJoryResource.php'));

        $this->assertTrue($generatedFilesystem->has('AlbumCoverJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('AlbumJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('BandJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('GroupieJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('InstrumentJoryResource.php')); // Excluded in config
        $this->assertFalse($generatedFilesystem->has('ModelJoryResource.php')); // Excluded in config
        $this->assertTrue($generatedFilesystem->has('PersonJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('NonExistingJoryResource.php'));
    }

    /** @test */
    public function it_can_run_a_generate_command_for_all_models_using_the_selector()
    {
        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', '<comment>All models</comment>')
            ->expectsOutput('AlbumCoverJoryResource created successfully.')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('ImageJoryResource created successfully.')
            ->expectsOutput('PersonJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.')
            ->expectsOutput('SongWithCustomJoryResourceJoryResource created successfully.')
            ->expectsOutput('AlbumJoryResource created successfully.')
            ->expectsOutput('TagJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.');
    }

    /** @test */
    public function it_can_use_the_force_option_to_override_existing_files()
    {
        $this->artisan('jory:generate', [
            '--all' => true,
        ])->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.');

        $adapter = new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Generated');
        $filesystem = new Filesystem($adapter);
        $filesystem->delete('SongJoryResource.php');

        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', '<comment>All models</comment>')
            ->expectsOutput('BandJoryResource already exists!')
            ->expectsOutput('GroupieJoryResource already exists!')
            ->expectsOutput('SongJoryResource created successfully.');

        $this->artisan('jory:generate', [
            '--force' => true,
        ])->expectsQuestion('For which model would you like to generate?', '<comment>All models</comment>')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.');
    }

    /** @test */
    public function it_doesnt_configure_fields_which_are_marked_to_be_excluded()
    {
        $this->artisan('jory:generate', [
            '--model' => User::class,
        ])->expectsOutput('UserJoryResource created successfully.');

        $filesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Original'));

        $generatedFilesystem = new Filesystem(new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput/Generated'));

        $this->assertTrue($filesystem->read('UserJoryResource.php') === $generatedFilesystem->read('UserJoryResource.php'));
    }

    protected function cleanup()
    {
        // Remove all previously built JoryResources
        $adapter = new LocalFilesystemAdapter(__DIR__ . '/ConsoleOutput');
        $filesystem = new Filesystem($adapter);

        foreach ($filesystem->listContents('Generated') as $file){
            if(!Str::endsWith($file->path(), '.gitignore')){
                $filesystem->delete($file->path());
            }
        }
    }
}