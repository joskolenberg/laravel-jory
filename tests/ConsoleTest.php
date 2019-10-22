<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Facades\Artisan;
use JosKolenberg\LaravelJory\JoryServiceProvider;
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
    public function it_can_run_a_generate_for_command_1()
    {
        // Output on scrutinizer can be different than local but is both fine since it only changes the order of the lines.

        $this->artisan('jory:generate-for', ['model' => 'JosKolenberg\LaravelJory\Tests\Models\Band'])
            ->expectsOutput('BandJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('BandJoryResource.php');
        $expectedContentsScrutinizer = $filesystem->read('Scrutinizer/BandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('BandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal || $realContents === $expectedContentsScrutinizer);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_with_name_option()
    {
        // Output on scrutinizer can be different than local but is both fine since it only changes the order of the lines.

        $this->artisan('jory:generate-for', ['model' => 'JosKolenberg\LaravelJory\Tests\Models\Band', '--name' => 'AlternateBandJoryResource'])
            ->expectsOutput('AlternateBandJoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('AlternateBandJoryResource.php');
        $expectedContentsScrutinizer = $filesystem->read('Scrutinizer/AlternateBandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal || $realContents === $expectedContentsScrutinizer);
    }

    /** @test */
    public function it_can_run_a_make_jory_resource_command()
    {
        $this->artisan('make:jory-resource', ['name' => 'EmptyJoryResource'])
            ->expectsOutput('JoryResource created successfully.');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContents = $filesystem->read('EmptyJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('EmptyJoryResource.php');

        $this->assertEquals($expectedContents, $realContents);
    }

    /** @test */
    public function it_can_run_a_make_jory_resource_command_with_related_model()
    {
        // Output on scrutinizer can be different than local but is both fine since it only changes the order of the lines.

        $this->artisan('make:jory-resource', ['name' => 'AlternateBandJoryResource', '--model' => 'JosKolenberg\LaravelJory\Tests\Models\Band'])
            ->expectsOutput('AlternateBandJoryResource created successfully.');


        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContentsLocal = $filesystem->read('AlternateBandJoryResource.php');
        $expectedContentsScrutinizer = $filesystem->read('Scrutinizer/AlternateBandJoryResource.php');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertTrue($realContents === $expectedContentsLocal || $realContents === $expectedContentsScrutinizer);
    }

    /** @test */
    public function it_can_run_a_generate_all_command()
    {
        $this->artisan('jory:generate-all')->expectsOutput('BandJoryResource created successfully.')
            ->expectsOutput('SongJoryResource created successfully.')
            ->expectsOutput('GroupieJoryResource created successfully.')
            ->expectsOutput('TagJoryResource created successfully.')
            ->expectsOutput('AlbumCoverJoryResource created successfully.')
            ->expectsOutput('UserJoryResource created successfully.')
            ->expectsOutput('PersonJoryResource created successfully.')
            ->expectsOutput('ErrorPersonJoryResource created successfully.')
            ->expectsOutput('AlbumJoryResource created successfully.')
            ->expectsOutput('SongWithAfterFetchHookJoryResource created successfully.')
            ->expectsOutput('SongWithCustomJoryResourceJoryResource created successfully.')
            ->expectsOutput('ImageJoryResource created successfully.');

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
        $this->assertTrue($generatedFilesystem->has('ErrorPersonJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('GroupieJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('InstrumentJoryResource.php')); // Excluded in config
        $this->assertFalse($generatedFilesystem->has('ModelJoryResource.php')); // Excluded in config
        $this->assertTrue($generatedFilesystem->has('PersonJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongWithAfterFetchHookJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('NonExistingJoryResource.php'));
    }

    protected function cleanup()
    {
        // Remove all previously built JoryResources
        $adapter = new Local(__DIR__ . '/ConsoleOutput');
        $filesystem = new Filesystem($adapter);
        $filesystem->deleteDir('Generated');
    }

    /** @test */
    public function it_can_run_a_generate_jory_publish()
    {
        $filesystem = new Filesystem(new Local(config_path()));

        if ($filesystem->has('jory.php')) {
            $filesystem->delete('jory.php');
        }

        $this->artisan('jory:publish');

        $this->assertTrue($filesystem->has('jory.php'));
    }
}