<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Support\Facades\DB;

class QueryCount implements Metadata
{

    /**
     * Do any preparation for the metadata.
     * Called at the start of the request.
     *
     */
    public function init()
    {
        DB::enableQueryLog();
    }

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     */
    public function get()
    {
        return count(DB::getQueryLog());
    }
}