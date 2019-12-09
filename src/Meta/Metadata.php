<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Http\Request;

abstract class Metadata
{

    protected $request;

    /**
     * Metadata constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     * @return mixed
     */
    abstract public function get();

}