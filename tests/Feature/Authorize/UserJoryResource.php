<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Authorize;


use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\NewModels\User;

class UserJoryResource extends JoryResource
{
    protected $modelClass = User::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('email')->filterable()->sortable();
        $this->field('password')->filterable()->sortable();
    }

    public function authorize($builder, $user = null): void
    {
        if($user && $user->name === 'John'){
            $builder->where('name', '<', 'John')
                ->orWhere('name', '>', 'Paul');
        }
    }
}