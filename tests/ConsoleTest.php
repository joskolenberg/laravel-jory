<?php

namespace JosKolenberg\LaravelJory\Tests;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class ConsoleTest extends TestCase
{
    /** @test */
    public function dummy()
    {
        // Disabled this tests because they fail on scrutinizer-ci.
        // Keep them for local testing.
        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanup();
    }

    /** @test */
    public function it_can_run_a_generate_for_command_1()
    {
        $this->artisan('jory:generate-for', ['model' => 'JosKolenberg\LaravelJory\Tests\Models\Band'])
            ->expectsOutput('BandJoryResource created successfully.');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Original');
        $filesystem = new Filesystem($adapter);
        $expectedContents = $filesystem->read('BandJoryResource.php');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Generated');
        $filesystem = new Filesystem($adapter);
        $realContents = $filesystem->read('BandJoryResource.php');

        $this->assertEquals($expectedContents, $realContents);
    }

    /** @test */
    public function it_can_run_a_generate_for_command_with_name_option()
    {
        $this->artisan('jory:generate-for', ['model' => 'JosKolenberg\LaravelJory\Tests\Models\Band', '--name' => 'AlternateBandJoryResource'])
            ->expectsOutput('AlternateBandJoryResource created successfully.');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Original');
        $filesystem = new Filesystem($adapter);
        $expectedContents = $filesystem->read('AlternateBandJoryResource.php');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Generated');
        $filesystem = new Filesystem($adapter);
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertEquals($expectedContents, $realContents);
    }

    /** @test */
    public function it_can_run_a_make_jory_resource_command()
    {
        $this->artisan('make:jory-resource', ['name' => 'EmptyJoryResource'])
            ->expectsOutput('JoryResource created successfully.');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Original');
        $filesystem = new Filesystem($adapter);
        $expectedContents = $filesystem->read('EmptyJoryResource.php');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Generated');
        $filesystem = new Filesystem($adapter);
        $realContents = $filesystem->read('EmptyJoryResource.php');

        $this->assertEquals($expectedContents, $realContents);
    }

    /** @test */
    public function it_can_run_a_make_jory_resource_command_with_related_model()
    {
        $this->artisan('make:jory-resource', ['name' => 'AlternateBandJoryResource', '--model' => 'JosKolenberg\LaravelJory\Tests\Models\Band'])
            ->expectsOutput('AlternateBandJoryResource created successfully.');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Original');
        $filesystem = new Filesystem($adapter);
        $expectedContents = $filesystem->read('AlternateBandJoryResource.php');

        $adapter = new Local(__DIR__ . '/ConsoleOutput/Generated');
        $filesystem = new Filesystem($adapter);
        $realContents = $filesystem->read('AlternateBandJoryResource.php');

        $this->assertEquals($expectedContents, $realContents);
    }

    /** @test */
    public function it_can_run_a_generate_all_command()
    {
        $this->artisan('jory:generate-all');

        $filesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Original'));
        $expectedContents = $filesystem->read('BandJoryResource.php');

        $generatedFilesystem = new Filesystem(new Local(__DIR__ . '/ConsoleOutput/Generated'));
        $realContents = $generatedFilesystem->read('BandJoryResource.php');

        $this->assertEquals($expectedContents, $realContents);

        $this->assertTrue($generatedFilesystem->has('AlbumCoverJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('AlbumJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('BandJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('ErrorPersonJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('GroupieJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('InstrumentJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('ModelJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('PersonJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongJoryResource.php'));
        $this->assertTrue($generatedFilesystem->has('SongWithAfterFetchHookJoryResource.php'));
        $this->assertFalse($generatedFilesystem->has('NonExistingJoryResource.php'));
    }

    protected function cleanup()
    {
        // Remove all previously built JoryBuilders
        $adapter = new Local(__DIR__ . '/ConsoleOutput');
        $filesystem = new Filesystem($adapter);
        $filesystem->deleteDir('Generated');
    }
}