<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueryCount extends Metadata
{
    /**
     * QueryCount constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

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