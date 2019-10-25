<?php


namespace JosKolenberg\LaravelJory\Meta;


class Time implements Metadata
{

    protected $startTime;

    /**
     * Do any preparation for the metadata.
     * Called at the start of the request.
     *
     */
    public function init()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     */
    public function get()
    {
        return number_format(microtime(true) - $this->startTime, 4);
    }
}