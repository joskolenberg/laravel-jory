<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Scopes\HasAlbumWithNameFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\NumberOfAlbumsInYearFilter;

class BandJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Band::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        $this->field('id')->filterable(function (Filter $filter) {
            $filter->operators(['=', '>', '<', '<=', '>=', '<>', '!=', 'in', 'not_in']);
        })->sortable();

        $this->field('name')->filterable()->sortable();

        $this->field('year_start')->filterable()->sortable();

        $this->field('year_end')->filterable()->sortable();

        $this->field('all_albums_string')->noSelect()->load('albums');
        $this->field('titles_string')->noSelect()->load('songs');
        $this->field('first_title_string')->noSelect()->load('firstSong');
        $this->field('image_urls_string')->noSelect()->load('images');

        $this->filter('has_album_with_name', new HasAlbumWithNameFilter);
        $this->filter('number_of_albums_in_year', new NumberOfAlbumsInYearFilter)->operators([
            '=',
            '>',
            '<',
            '<=',
            '>=',
            '<>',
            '!=',
        ]);

        $this->limitDefault(30)->limitMax(120);

        $this->relation('albums');
        $this->relation('people');
        $this->relation('songs');
        $this->relation('firstSong');
        $this->relation('images');
    }

    public function authorize($builder, $user = null): void
    {
        if($user && $user->id == 1){
            $builder->where('id', '>=', 3);
        }
    }
}
