<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Support\Facades\Auth;

class User implements Metadata
{

    /**
     * Do any preparation for the metadata.
     * Called at the start of the request.
     *
     */
    public function init(){}

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     */
    public function get()
    {
        return optional(Auth::user())->email;
    }
}