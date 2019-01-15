<?php

namespace JosKolenberg\LaravelJory\Tests\Parsers;

use JosKolenberg\LaravelJory\Config\Sort;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Config\Filter;
use JosKolenberg\LaravelJory\Tests\TestCase;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;

class ToCamelCaseTest extends TestCase
{
    /** @test */
    public function it_can_camel_case_on_an_empty_config()
    {
        $emptyConfig = new Config(Band::class);
        $emptyConfig->toCamelCase();
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_camel_case_the_defined_fields()
    {
        $config = new Config(Band::class);
        $config->field('first_name')
            ->hideByDefault();
        $config->field('lastName')
            ->description('Last name field');
        $config->toCamelCase();

        $firstNameField = $config->getFields()[0];
        $this->assertEquals('firstName', $firstNameField->getField());
        $this->assertEquals('The firstName field.', $firstNameField->getDescription());
        $this->assertFalse($firstNameField->isShownByDefault());

        $lastNameField = $config->getFields()[1];
        $this->assertEquals('lastName', $lastNameField->getField());
        $this->assertEquals('Last name field', $lastNameField->getDescription());
        $this->assertTrue($lastNameField->isShownByDefault());
    }

    /** @test */
    public function it_can_camel_case_the_defined_filters()
    {
        $config = new Config(Band::class);

        $config->filter('first_name')->operators(['=', '!=']);
        $config->filter('lastName')->description('Filter by last name.')->operators(['=']);
        $config->field('middle_name')->filterable(function (Filter $filter){
            $filter->operators(['like']);
        });
        $config->toCamelCase();

        $firstNameFilter = $config->getFilters()[0];
        $this->assertEquals('firstName', $firstNameFilter->getField());
        $this->assertEquals('Filter on the firstName field.', $firstNameFilter->getDescription());
        $this->assertEquals(['=', '!='], $firstNameFilter->getOperators());

        $lastNameFilter = $config->getFilters()[1];
        $this->assertEquals('lastName', $lastNameFilter->getField());
        $this->assertEquals('Filter by last name.', $lastNameFilter->getDescription());
        $this->assertEquals(['='], $lastNameFilter->getOperators());

        $middleNameFilter = $config->getFilters()[2];
        $this->assertEquals('middleName', $middleNameFilter->getField());
        $this->assertEquals('Filter on the middleName field.', $middleNameFilter->getDescription());
        $this->assertEquals(['like'], $middleNameFilter->getOperators());
    }

    /** @test */
    public function it_can_camel_case_the_defined_sorts()
    {
        $config = new Config(Band::class);

        $config->sort('first_name')->default(3, 'desc');
        $config->sort('lastName')->description('Sort by last name.');
        $config->field('middle_name')->sortable(function (Sort $sort){
            $sort->default(2);
        });
        $config->toCamelCase();

        $firstNameSort = $config->getSorts()[0];
        $this->assertEquals('firstName', $firstNameSort->getField());
        $this->assertEquals('Sort by the firstName field.', $firstNameSort->getDescription());
        $this->assertEquals(3, $firstNameSort->getDefaultIndex());
        $this->assertEquals('desc', $firstNameSort->getDefaultOrder());

        $lastNameSort = $config->getSorts()[1];
        $this->assertEquals('lastName', $lastNameSort->getField());
        $this->assertEquals('Sort by last name.', $lastNameSort->getDescription());
        $this->assertEquals(null, $lastNameSort->getDefaultIndex());
        $this->assertEquals('asc', $lastNameSort->getDefaultOrder());

        $middleNameSort = $config->getSorts()[2];
        $this->assertEquals('middleName', $middleNameSort->getField());
        $this->assertEquals('Sort by the middleName field.', $middleNameSort->getDescription());
        $this->assertEquals(2, $middleNameSort->getDefaultIndex());
        $this->assertEquals('asc', $middleNameSort->getDefaultOrder());
    }

    /** @test */
    public function it_can_camel_case_the_defined_relations()
    {
        $config = new Config(Album::class);

        $config->relation('album_cover');
        $config->relation('camelCaseAlbumCover')->description('The ccac relation.');
        $config->toCamelCase();

        $albumCoverRelation = $config->getRelations()[0];
        $this->assertEquals('albumCover', $albumCoverRelation->getName());
        $this->assertEquals('The albumCover relation.', $albumCoverRelation->getDescription());
        $this->assertEquals(AlbumCover::class, $albumCoverRelation->getModelClass());
        $this->assertEquals('album-cover', $albumCoverRelation->getType());

        $camelCaseAlbumCoverRelation = $config->getRelations()[1];
        $this->assertEquals('camelCaseAlbumCover', $camelCaseAlbumCoverRelation->getName());
        $this->assertEquals('The ccac relation.', $camelCaseAlbumCoverRelation->getDescription());
        $this->assertEquals(AlbumCover::class, $camelCaseAlbumCoverRelation->getModelClass());
        $this->assertEquals('album-cover', $camelCaseAlbumCoverRelation->getType());
    }

}
