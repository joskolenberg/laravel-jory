<?php

namespace JosKolenberg\LaravelJory\Helpers;

use Illuminate\Http\Request;

class CaseManager
{
    private $case = 'snake';

    public function __construct(Request $request)
    {
        $this->case = config('jory.case');

        $inputCase = $request->input(config('jory.request.case-key'));
        if (in_array($inputCase, ['snake', 'camel'])) {
            $this->case = $inputCase;
        }
    }

    public function isCamel()
    {
        return $this->case === 'camel';
    }

}
