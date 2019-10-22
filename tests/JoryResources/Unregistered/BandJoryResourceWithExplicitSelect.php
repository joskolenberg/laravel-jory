<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Band::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        $this->field('id')->filterable(function (Filter $filter) {
            $filter->description('Try this filter by id!')->operators(['=', '>', '<', '<=', '>=', '<>', '!=', 'in', 'not_in']);
        })->sortable();

        $this->field('name')->filterable()->sortable();

        $this->field('year_start')->description('The year in which the band started.')->filterable()->sortable();

        $this->field('year_end')->description('The year in which the band quitted, could be null if band still exists.')->filterable()->sortable();

        $this->field('all_albums_string')->noSelect()->load('albums')->hideByDefault();
        $this->field('titles_string')->noSelect()->load('songs')->hideByDefault();
        $this->field('first_title_string')->noSelect()->load('first_song')->hideByDefault();

        $this->filter('has_album_with_name')->description('Filter bands that have an album with a given name.');
        $this->filter('number_of_albums_in_year')->operators([
            '=',
            '>',
            '<',
            '<=',
            '>=',
            '<>',
            '!=',
        ])->description('Filter the bands that released a given number of albums in a year, pass value and year parameter.');

        $this->limitDefault(30)->limitMax(120);

        $this->relation('albums')->description('Get the related albums for the band.');
        $this->relation('people');
        $this->relation('songs');
        $this->relation('first_song');
        $this->relation('images');
    }

    public function scopeNumberOfAlbumsInYearFilter($query, $operator, $data)
    {
        $year = $data['year'];
        $value = $data['value'];

        $query->whereHas('albums', function ($query) use ($year) {
            $query->where('release_date', '>=', $year.'-01-01');
            $query->where('release_date', '<=', $year.'-12-31');
        }, $operator, $value);
    }

    public function authorize($query, $user = null): void
    {
        if($user && $user->id == 1){
            $query->where('id', '>=', 3);
        }
    }
}
