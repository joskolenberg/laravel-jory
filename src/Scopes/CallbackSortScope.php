<?php

namespace JosKolenberg\LaravelJory\Scopes;

class CallbackSortScope implements SortScope
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function apply($builder, string $order = 'asc'): void
    {
        call_user_func($this->callback, $builder, $order);
    }
}