<?php

namespace JosKolenberg\LaravelJory\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function toCurrent($string)
    {
        if($this->isCamel()){
            return Str::camel($string);
        }

        return Str::snake($string);
    }

    public function toCamel($string)
    {
        return Str::camel($string);
    }

    public function toSnake($string)
    {
        return Str::snake($string);
    }
}
