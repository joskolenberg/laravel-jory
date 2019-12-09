<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Http\Request;

class Time extends Metadata
{

    /**
     * @var float
     */
    protected $startTime;

    /**
     * Time constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->startTime = microtime(true);
    }

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     * @return mixed
     */
    public function get()
    {
        return number_format(microtime(true) - $this->startTime, 4);
    }
}