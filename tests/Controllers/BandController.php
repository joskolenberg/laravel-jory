<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandController extends Controller
{

    public function index(Request $request)
    {
        $data = Band::jory()->applyRequest($request)->get();

        return response()->json($data);
    }

    public function indexAsResponse(Request $request)
    {
        return Band::jory()->applyRequest($request);
    }

}
