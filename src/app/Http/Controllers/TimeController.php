<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeController extends Controller
{
    public function now()
    {
        $now = Carbon::now();
        return view('user-attendance-register', ['now' => $now]);
    }
}
