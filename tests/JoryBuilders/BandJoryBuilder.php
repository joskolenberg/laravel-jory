<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Config\Filter;

class BandJoryBuilder extends JoryBuilder
{
    protected function scopeNumberOfAlbumsInYearFilter($query, $operator, $data)
    {
        $year = $data['year'];
        $value = $data['value'];

        $query->whereHas('albums', function ($query) use ($year) {
            $query->where('release_date', '>=', $year.'-01-01');
            $query->where('release_date', '<=', $year.'-12-31');
        }, $operator, $value);
    }

    protected function config(Config $config): void
    {
        parent::config($config);

        $config->field('id')->filterable(function (Filter $filter) {
            $filter->description('Try this filter by id!')->operators(['=', '>', '<', '<=', '>=', '<>', '!=']);
        })->sortable();

        $config->field('name')->filterable()->sortable();

        $config->field('year_start')->description('The year in which the band started.')->filterable()->sortable();

        $config->field('year_end')->description('The year in which the band quitted, could be null if band still exists.')->filterable()->sortable();

        $config->filter('has_album_with_name')->description('Filter bands that have an album with a given name.');
        $config->filter('number_of_albums_in_year')->operators([
            '=',
            '>',
            '<',
            '<=',
            '>=',
            '<>',
            '!=',
        ])->description('Filter the bands that released a given number of albums in a year, pass value and year parameter.');

        $config->limitDefault(30)->limitMax(120);

        $config->relation('albums')->description('Get the related albums for the band.');
        $config->relation('people');
        $config->relation('songs');
    }
}
