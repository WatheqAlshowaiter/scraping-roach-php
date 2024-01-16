<?php

namespace App\Http\Controllers;

use App\Traits\ImageDownloader;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    use ImageDownloader;

    public function __invoke(Request $request)
    {
        dd('debug');
    }
}
