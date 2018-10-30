<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Blueprint\Filter;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Blueprint\Blueprint;
use JosKolenberg\LaravelJory\Tests\Models\Person;

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

        $blueprint->field('id')
            ->filterable(function (Filter $filter) {
                $filter->description('Try this filter by id!')
                    ->operators(["=", ">", "<", "<=", ">=", "<>", "!="]);
            })->sortable();

        $blueprint->field('name')
            ->filterable()
            ->sortable();

        $blueprint->field('year_start')
            ->description('The year in which the band started.')
            ->filterable()
            ->sortable();

        $blueprint->field('year_end')
            ->description('The year in which the band quitted, could be null if band still exists.')
            ->filterable()
            ->sortable();

        $blueprint->filter('has_album_with_name')->description('Filter bands that have an album with a given name.');
        $blueprint->filter('number_of_albums_in_year')->operators([
            "=",
            ">",
            "<",
            "<=",
            ">=",
            "<>",
            "!=",
        ])->description('Filter the bands that released a given number of albums in a year, pass value and year parameter.');

        $blueprint->limitDefault(30)->limitMax(120);

        $blueprint->relation('albums', Album::class)->description('Get the related albums for the band.');
        $blueprint->relation('people', Person::class)->type('person');
        $blueprint->relation('songs', Song::class);
    }
}
