<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\WithTraits\BandWithTrait;

class BandController extends Controller
{

    public function index(Request $request)
    {
        $data = BandWithTrait::jory()->applyRequest($request)->get();

        return \response()->json($data);
    }

}