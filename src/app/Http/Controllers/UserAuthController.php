<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('start_time', Carbon::today())
            ->first();

        // 休憩中かどうか判定（rest_end がnullのレコードがあるか）
        $isResting = false;

        if ($attendance) {
            $isResting = $attendance->rests->whereNull('rest_end')->isNotEmpty();
        }
        return view('user.user-attendance-register',compact('now','attendance', 'isResting'));
    }
}
