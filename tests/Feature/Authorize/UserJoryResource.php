<?php


namespace JosKolenberg\LaravelJory\Tests\Feature\Authorize;


use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class UserJoryResource extends JoryResource
{
    protected $modelClass = User::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
    }

    public function authorize($builder, $user = null): void
    {
        if($user && $user->name === 'Bert'){
            $builder->where('name', '<', 'Big Bird')
                ->orWhere('name', '>', 'Oscar');
        }
    }
}