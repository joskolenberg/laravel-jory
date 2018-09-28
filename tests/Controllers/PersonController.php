<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $data = Person::jory()->applyRequest($request)->get();

        return \response()->json($data);
    }
}
