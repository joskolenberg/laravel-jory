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

    /**
     * Convert all keys in the array to camelCase.
     *
     * @param array $array
     * @return array
     */
    public function arrayKeysToCamel(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[Str::camel($key)] = $value;
        }

        return $result;
    }
}
