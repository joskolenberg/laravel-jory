<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\WithTraits\PersonWithTrait;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $data = PersonWithTrait::jory()->applyRequest($request)->get();

        return \response()->json($data);
    }
}
