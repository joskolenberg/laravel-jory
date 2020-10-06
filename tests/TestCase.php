<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use JosKolenberg\LaravelJory\JoryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase(Application $app)
    {
        DB::connection()->setQueryGrammar(new MySqlGrammar());

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->bigInteger('team_id')->nullable();
        });

        $app['db']->connection()->getSchemaBuilder()->create('teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('people', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('first_name');
//            $table->string('last_name');
//            $table->date('date_of_birth');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('bands', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('name');
//            $table->integer('year_start');
//            $table->integer('year_end')->nullable();
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('band_members', function (Blueprint $table) {
//            $table->increments('id');
//            $table->unsignedInteger('person_id');
//            $table->foreign('person_id')->references('id')->on('people')->onDelete('restrict');
//            $table->unsignedInteger('band_id');
//            $table->foreign('band_id')->references('id')->on('bands')->onDelete('restrict');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('instruments', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('name');
//            $table->string('type_name');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('instrument_person', function (Blueprint $table) {
//            $table->increments('id');
//            $table->unsignedInteger('person_id');
//            $table->foreign('person_id')->references('id')->on('band_members')->onDelete('restrict');
//            $table->unsignedInteger('instrument_id');
//            $table->foreign('instrument_id')->references('id')->on('instruments')->onDelete('restrict');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('albums', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('name');
//            $table->unsignedInteger('band_id');
//            $table->foreign('band_id')->references('id')->on('bands')->onDelete('restrict');
//            $table->date('release_date');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('songs', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('title');
//            $table->unsignedInteger('album_id');
//            $table->foreign('album_id')->references('id')->on('albums')->onDelete('restrict');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('album_covers', function (Blueprint $table) {
//            $table->increments('id');
//            $table->text('image');
//            $table->unsignedInteger('album_id');
//            $table->foreign('album_id')->references('id')->on('albums')->onDelete('restrict');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('groupies', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('name');
//            $table->unsignedInteger('person_id');
//            $table->foreign('person_id')->references('id')->on('people')->onDelete('restrict');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('images', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('url');
//            $table->unsignedInteger('imageable_id');
//            $table->string('imageable_type');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('tags', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string('name');
//        });
//
//        $app['db']->connection()->getSchemaBuilder()->create('taggables', function (Blueprint $table) {
//            $table->unsignedInteger('tag_id');
//            $table->unsignedInteger('taggable_id');
//            $table->string('taggable_type');
//        });

    }

    protected function getPackageProviders($app)
    {
        return [
            JoryServiceProvider::class,
        ];
    }

    public function startQueryCount()
    {
        \DB::enableQueryLog();
    }

    public function assertQueryCount($expected)
    {
        $this->assertEquals($expected, count(\DB::getQueryLog()));
    }
}
