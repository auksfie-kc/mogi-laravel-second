<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\CarbonInterval;

class UserAttendanceController extends Controller
{
    public function storeClockIn()
    {
        $user = Auth::user();

        $oldAttendanceDay = null;

        //打刻は１日１回まで
        $oldAttendance = Attendance::where('user_id', $user->id)->latest()->first();
        if ($oldAttendance) {
            $oldAttendanceClockIn = new Carbon($oldAttendance->start_time);
            $oldAttendanceDay = $oldAttendanceClockIn->startOfDay();
        }

        $newAttendanceDay = Carbon::today();

        //同じ日で既に退勤していたらエラー表示する
        if (($oldAttendanceDay == $newAttendanceDay) && (empty($oldAttendance->end_time))) {
            return redirect()->back()->with('error', 'すでに出勤打刻がされています');
        }

        $timestamp = Attendance::create([
            'user_id' => $user->id,
            'date' =>  $newAttendanceDay,
            'start_time' => Carbon::now(),
        ]);


        return redirect('/attendance')->with('message', '出勤中');
    }


    public function storeClockOut()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)->latest()->first();

        if (!empty($attendance->end_time)) {
            return redirect()->back()->with('error', '既に退勤の打刻がされているか、出勤打刻されていません');
        }
        $attendance->update([
            'end_time' => Carbon::now()
        ]);

        return redirect()->back()->with('message', '退勤打刻が完了しました');
    }


    public function storeRestStart(Request $request)
    {
        $user = Auth::user();

        // 今の出勤情報を取得（まだ退勤していない出勤記録）
        $attendance = Attendance::where('user_id', $user->id)->whereNull('end_time')->latest()->first();

        if (!$attendance) {
            return back()->withErrors('出勤中ではありません。');
        }

        // すでに休憩中かどうかをチェック（rest_end が null の rest があるか）
        $existingRest = $attendance->rests()->whereNull('rest_end')->first();
        if ($existingRest) {
            return back()->withErrors('すでに休憩中です。');
        }

        // 休憩開始を登録
        $attendance->rests()->create([
            'rest_start' => Carbon::now()
        ]);
        return back()->with('success', '休憩を開始しました。');
    }


    public function storeRestEnd(Request $request)
    {
        $user = Auth::user();

        // 出勤中の記録を取得（退勤していない）
        $attendance = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return back()->withErrors('出勤中の記録が見つかりません。');
        }

        // まだ rest_end が null の休憩レコードを取得
        $ongoingRest = $attendance->rests->whereNull('rest_end')->first();

        if (!$ongoingRest) {
            return back()->withErrors('休憩中ではありません。');
        }

        // 現在時刻で休憩終了を記録
        $ongoingRest->update([
            'rest_end' => Carbon::now()
        ]);

        return back()->with('success', '休憩を終了しました。');
    }


    public function index($year = null, $month = null)
    {

        $user = Auth::user();

        if ($year && $month) {
            $thisMonth = Carbon::createFromDate($year, $month, 1); //指定年月があれば取得
        } else {
            $thisMonth = Carbon::now()->startOfMonth();
        } //なければ今月の始まり関数を取得


        $previousMonth = $thisMonth->copy()->subMonth();  //前月の関数を取得
        $nextMonth = $thisMonth->copy()->addMonth();  //翌月の関数を取得

        $startDate = $thisMonth->copy()->startOfMonth(); //指定月の１日０時を取得
        $endDate = $thisMonth->copy()->endOfMonth(); //指定月の３１日２３時５９分５９秒を取得

        //指定月の勤怠データを取得（出勤している日だけ）＋休憩時間も
        $attendances = Attendance::with('rests')
            ->where('user_id',$user->id)
            ->whereBetween('start_time', [$startDate, $endDate]) //期間の指定
            ->orderby('start_time','asc') //値が小さい方から順に並べる
            ->get();

        // 各出勤データに「休憩合計時間（CarbonIntervalにて）と勤務合計時間」を追加
        foreach ($attendances as $attendance) {
            $restTotalSeconds = 0;

            //戻り休憩-入り休憩で休憩時間を算出
            foreach ($attendance->rests as $rest) {
                if ($rest->rest_start && $rest->rest_end) {

                    //Carbonインスタンスに揃える
                    $restStart = \Carbon\Carbon::parse($rest->rest_start);
                    $restEnd = \Carbon\Carbon::parse($rest->rest_end);
                    $restTotalSeconds += $rest->rest_end->diffInSeconds($rest->rest_start);
                }
            }
            // CarbonIntervalで休憩合計時間を追加
            $attendance->rest_total_time = CarbonInterval::seconds($restTotalSeconds)->cascade();


            // 勤務時間を計算（退勤 - 出勤 - 休憩）
            if ($attendance->start_time && $attendance->end_time) {

                $start = \Carbon\Carbon::parse($attendance->start_time);
                $end = \Carbon\Carbon::parse($attendance->end_time);

                //日を跨ぐときは翌日で処理
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $workTotalSeconds = $end->diffInSeconds($start) - $restTotalSeconds;

                // マイナス防止
                $workTotalSeconds = max(0, $workTotalSeconds);

                // 合計勤務時間（CarbonInterval）を追加
                $attendance->work_total_time = CarbonInterval::seconds($workTotalSeconds)->cascade();
            } else {
                $attendance->work_total_time = null; // 出勤 or 退勤が未入力
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
                'attendance' => $attendance,
                'rest_total_time' => $attendance?->rest_total_time, //$attendance?がnullでなければ合計休憩時間を取得
                'work_total_time' => $attendance?->work_total_time,
            ];
        }

        return view('user.user-attendance-index',[
            'thisMonth'=> $thisMonth,
            'previousMonth'=> $previousMonth,
            'nextMonth'=> $nextMonth,
            'attendances' =>$attendancesForView]);
    }

    public function show($id){

        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 既に修正申請がある場合は承認待ち画面へリダイレクト
        if ($attendance->requestChanges()->exists()) {
            return redirect()->route('request.approval', $attendance->id);
        }

        //１日の勤務データに紐づく休憩を２回分準備
        $restsDisplay = [];
        for ($i = 0; $i < 2; $i++) {
            if (isset($attendance->rests[$i])) {
                $rest = $attendance->rests[$i];
                $start = $rest->rest_start ? \Carbon\Carbon::parse($rest->rest_start)->format('H:i') : '-';
                $end   = $rest->rest_end ? \Carbon\Carbon::parse($rest->rest_end)->format('H:i') : '-';
                $restsDisplay[] = ['start' => $start, 'end' => $end];
            } else {
                $restsDisplay[] = ['start' => '', 'end' => ''];
            }
        }

        return view('user.user-attendance-show', compact('attendance', 'restsDisplay'));
    }

}