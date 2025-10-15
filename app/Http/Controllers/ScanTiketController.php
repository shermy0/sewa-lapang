<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScanTiketController extends Controller
{
    public function scan()
    {
        return view('pemilik.scan'); // pastikan file ini ada
    }
}