<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class AdminStaffController extends Controller
{
    public function index(){

        $users = User::all();
        $attendances = Attendance::with(['user', 'rests'])
            ->get();

        return view('admin.admin-staff-index',compact('users','attendances'));
    }


    public function show($id, $year = null, $month = null ){

        $now = Carbon::now();

        if ($year && $month) {

            //指定年月があれば取得
            $thisMonth = Carbon::createFromDate($year, $month, 1);
        }else{
            $thisMonth = $now->copy()->startOfMonth();
        }

        $previousMonth = $thisMonth->copy()->subMonth();  //前月の関数を取得
        $nextMonth = $thisMonth->copy()->addMonth();  //翌月の関数を取得
        $startDate = $thisMonth->copy()->startOfMonth(); //指定月の１日０時を取得
        $endDate = $thisMonth->copy()->endOfMonth(); //指定月の３１日２３時５９分５９秒を取得


        //Userに紐づく勤怠DBと休憩DBより取得して格納
        $staff = User::with(['attendances.rests'])->findOrFail($id);

        $attendances = Attendance::with('rests')
            ->where('user_id', $staff->id)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time', 'asc')
            ->get();

        // 勤怠ごとに休憩合計時間・勤務時間を追加
        foreach ($attendances as $attendance) {
            $restTotalSeconds = 0;

            // 休憩時間計算（休憩も日跨ぎ対応）
            foreach ($attendance->rests as $rest) {
                if ($rest->rest_start && $rest->rest_end) {
                    $restStart = Carbon::parse($rest->rest_start);
                    $restEnd   = Carbon::parse($rest->rest_end);

                    if ($restEnd->lessThan($restStart)) {
                        $restEnd->addDay();
                    }
                    $restTotalSeconds += $restEnd->diffInSeconds($restStart);
                }
            }
                $attendance->rest_total_time = CarbonInterval::seconds($restTotalSeconds)->cascade();

            if ($attendance->start_time && $attendance->end_time) {

                $start = Carbon::parse($attendance->start_time);
                $end   = Carbon::parse($attendance->end_time);

                // 勤務が日跨ぎの場合
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $workTotalSeconds = max(0, $end->diffInSeconds($start) - $restTotalSeconds);
                $attendance->work_total_time = CarbonInterval::seconds($workTotalSeconds)->cascade();

            } else {
                $attendance->work_total_time = null;
            }
        }

        // 日付ごとにまとめる（キーが 'YYYY-MM-DD' になる）
        $attendanceData = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->start_time)->toDateString();
        });



        $attendancesForView = []; // 出勤していない日を空欄とし、月内の日付を1日ずつループする
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString(); // 'YYYY-MM-DD文字列に変換'
            $attendance = $attendanceData->get($dateString)?->first(); // その日の勤怠があれば取得,なければ無視で空欄

            $attendancesForView[] = [

                'date' => $dateString,
                'currentDate' => $date,
                'attendance' => $attendance,
                'rest_total_time' => $attendance?->rest_total_time,
                'work_total_time' => $attendance?->work_total_time,
            ];
            }

        return view('admin.admin-staff-attendance', [
            'thisMonth' => $thisMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'staff' => $staff,
            'attendances' => $attendancesForView,
        ]);
    }
}