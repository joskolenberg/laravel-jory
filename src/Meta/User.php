<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Support\Facades\Auth;

class User extends Metadata
{

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     * @return mixed
     */
    public function get()
    {
        return optional(Auth::user())->email;
    }
}