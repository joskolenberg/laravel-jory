<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Blueprint\Blueprint;

class BandJoryBuilder extends JoryBuilder
{
    protected function scopeNumberOfAlbumsInYearFilter($query, $operator, $value)
    {
        $data = $value;
        $year = $data['year'];
        $value = $data['value'];

        $query->whereHas('albums', function ($query) use ($year) {
            $query->where('release_date', '>=', $year.'-01-01');
            $query->where('release_date', '<=', $year.'-12-31');
        }, $operator, $value);
    }

    protected function blueprint(Blueprint $blueprint): void
    {
        parent::blueprint($blueprint);

        $blueprint->field('id');
        $blueprint->field('name');
        $blueprint->field('year_start')->description('The year in which the band started.');
        $blueprint->field('year_end')->description('The year in which the band quitted, could be null if band still exists.');

        $blueprint->filter('id');
        $blueprint->filter('name');
        $blueprint->filter('year_start');
        $blueprint->filter('year_end');
        $blueprint->filter('has_album_with_name')->description('Filter bands that have an album with a given name.');
        $blueprint->filter('number_of_albums_in_year')->operators(["=",">","<","<=",">=","<>","!="])->description('Filter the bands that released a given number of albums in a year, pass value and year parameter.');

        $blueprint->sort('id');
        $blueprint->sort('name');
        $blueprint->sort('year_start');
        $blueprint->sort('year_end');

        $blueprint->limitDefault(30)->limitMax(120);

        $blueprint->relation('albums')->description('Get the related albums for the band.');
        $blueprint->relation('people')->type('person');
        $blueprint->relation('songs');
    }
}
