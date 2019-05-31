<?php

namespace JosKolenberg\LaravelJory\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class CaseManager
 *
 * Class to define in which case mode
 * we are, camel or snake case.
 */
class CaseManager
{

    /**
     * @var string
     */
    private $case = 'snake';

    /**
     * CaseManager constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->case = config('jory.case');

        $inputCase = $request->input(config('jory.request.case-key'));
        if (in_array($inputCase, ['snake', 'camel'])) {
            $this->case = $inputCase;
        }
    }

    /**
     * Update a string to the current case mode.
     *
     * @param $string
     * @return string
     */
    public function toCurrent($string): string
    {
        if ($this->case === 'camel') {
            return Str::camel($string);
        }

        return Str::snake($string);
    }

}
