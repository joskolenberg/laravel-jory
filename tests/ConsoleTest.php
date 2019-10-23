<?php

namespace JosKolenberg\LaravelJory\Tests;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

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
        // Output on scrutinizer can be different than local but is both fine since it only changes the order of the lines.

        $this->artisan('jory:generate', ['--model' => 'JosKolenberg\LaravelJory\Tests\Models\Band'])
            ->expectsOutput('BandJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('BandJoryResource.php');
        $expectedContentsScrutinizer = $filesystem->read('Scrutinizer/BandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('BandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal || $realContents === $expectedContentsScrutinizer);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_for_a_model_with_name_option()
    {
        // Output on scrutinizer can be different than local but is both fine since it only changes the order of the lines.

        $this->artisan('jory:generate', [
            '--model' => 'JosKolenberg\LaravelJory\Tests\Models\Band',
            '--name' => 'AlternateBandJoryResource'
        ])->expectsOutput('AlternateBandJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('AlternateBandJoryResource.php');
        $expectedContentsScrutinizer = $filesystem->read('Scrutinizer/AlternateBandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal || $realContents === $expectedContentsScrutinizer);
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
        ])->expectsOutput('AlbumJoryResource created successfully.')
            ->expectsOutput('AlbumCoverJoryResource created successfully.')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('ImageJoryResource created successfully.')
            ->expectsOutput('PersonJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.')
            ->expectsOutput('SongWithAfterFetchHookJoryResource created successfully.')
            ->expectsOutput('SongWithCustomJoryResourceJoryResource created successfully.')
            ->expectsOutput('TagJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));

        $generatedFilesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));

        // Output on scrutinizer can be different than local but is both fine since it only changes the order of the lines.
        $this->assertTrue($filesystem->read('AlbumJoryResource.php') === $generatedFilesystem->read('AlbumJoryResource.php') || $filesystem->read('Scrutinizer/AlbumJoryResource.php') === $generatedFilesystem->read('AlbumJoryResource.php'));
        $this->assertTrue($filesystem->read('BandJoryResource.php') === $generatedFilesystem->read('BandJoryResource.php') || $filesystem->read('Scrutinizer/BandJoryResource.php') === $generatedFilesystem->read('BandJoryResource.php'));
        $this->assertTrue($filesystem->read('PersonJoryResource.php') === $generatedFilesystem->read('PersonJoryResource.php') || $filesystem->read('Scrutinizer/PersonJoryResource.php') === $generatedFilesystem->read('PersonJoryResource.php'));
        $this->assertTrue($filesystem->read('ImageJoryResource.php') === $generatedFilesystem->read('ImageJoryResource.php') || $filesystem->read('Scrutinizer/ImageJoryResource.php') === $generatedFilesystem->read('ImageJoryResource.php'));

        $this->assertTrue($generatedFilesystem->has('AlbumCoverJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('AlbumJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('BandJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('GroupieJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('InstrumentJoryResource.php')); // Excluded in config
        $this->assertFalse($generatedFilesystem->has('ModelJoryResource.php')); // Excluded in config
        $this->assertTrue($generatedFilesystem->has('PersonJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongWithAfterFetchHookJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('NonExistingJoryResource.php'));
    }

    /** @test */
    public function it_can_run_a_generate_command_for_all_models_using_the_selector()
    {
        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', 'All models')
            ->expectsOutput('AlbumJoryResource created successfully.')
            ->expectsOutput('AlbumCoverJoryResource created successfully.')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('ImageJoryResource created successfully.')
            ->expectsOutput('PersonJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.')
            ->expectsOutput('SongWithAfterFetchHookJoryResource created successfully.')
            ->expectsOutput('SongWithCustomJoryResourceJoryResource created successfully.')
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

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Generated');
        $filesystem = new Filesystem($adapter);
        $filesystem->delete('SongJoryResource.php');

        $this->artisan('jory:generate')
            ->expectsQuestion('For which model would you like to generate?', 'All models')
            ->expectsOutput('BandJoryResource already exists!')
            ->expectsOutput('GroupieJoryResource already exists!')
            ->expectsOutput('SongJoryResource created successfully.');

        $this->artisan('jory:generate', [
            '--force' => true,
        ])->expectsQuestion('For which model would you like to generate?', 'All models')
            ->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.');
    }

    protected function cleanup()
    {
        // Remove all previously built JoryResources
        $adapter = new Local(__DIR__ . '/ConsoleOutput');
        $filesystem = new Filesystem($adapter);
        $filesystem->deleteDir('Generated');
    }
}