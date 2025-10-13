<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\RequestChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\CarbonInterval;


class AdminAttendanceController extends Controller
{
    public function index($year = null, $month = null, $day = null){
        $now = Carbon::now();

        // Carbonクエリから日付を取得、なければ今日
        if ($year && $month && $day) {
            $date = Carbon::createFromDate($year, $month, $day);
        } else {
            $date = Carbon::today(); // 時刻なしの日付（00:00:00）
        }

        // 指定日の勤怠データを取得（出勤しているユーザーと休憩データを一緒に取得）退勤や休憩が翌日のも拾う。
        $attendances = Attendance::with(['user', 'rests'])
            ->where(function ($query) use ($date) {
                $query->whereDate('start_time', $date->toDateString())
                    ->orWhereDate('end_time', $date->toDateString());
            })
            ->get()
            ->map(function ($attendance) {
                $start = Carbon::parse($attendance->start_time);
                $end = $attendance->end_time ? Carbon::parse($attendance->end_time) : null;

                // 日付を跨ぐ（退勤が出勤より前の時刻）の場合は翌日扱いに修正
                if ($end && $end->lessThan($start)) {
                    $end = $end->copy()->addDay();
                }

                // 休憩時間（分単位）
                $restTotal = 0;
                foreach ($attendance->rests as $rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        $restStart = Carbon::parse($rest->rest_start);
                        $restEnd = Carbon::parse($rest->rest_end);

                        // 出勤が翌日になる場合、休憩も同様に補正
                        if ($restEnd->lessThan($restStart)) {
                            $restEnd->addDay();
                        }

                    // 勤務全体が日を跨ぐ場合、休憩も同様に補正
                        if ($restStart->lessThan($start) && $restEnd->greaterThan($start)) {
                        $restStart = $start;
                        }
                        if ($end && $restEnd->greaterThan($end)) {
                        $restEnd = $end;
                        }
                        $restTotal += $restEnd->diffInMinutes($restStart);
                    }
                }

                // 勤務時間（分単位）
                $workTotal = ($start && $end)
                    ? max(0, $end->diffInMinutes($start) - $restTotal)
                    : 0;

                return [
                    'id'  => $attendance->id,
                    'user_name' => $attendance->user->name,
                    'start_time' => $start,
                    'end_time' => $end,
                    'rest_total' => $restTotal,
                    'work_total' => $workTotal,
                ];
            });


        return view('admin.admin-attendance-index',[
            'now' => $now,
            'currentDate' => $date,
            'previousDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'attendances' => $attendances,
        ]);
    }


    public function show($id)
    {
        $attendance = Attendance::with('user','rests')->findOrFail($id);

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
        return view('admin.admin-attendance-show', compact('attendance','restsDisplay'));
    }


    public function update(Request $request, $attendanceId)
    {

        //バリデーション
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'rest1_start' => 'nullable|date_format:H:i',
            'rest1_end' => 'nullable|date_format:H:i',
            'rest2_start' => 'nullable|date_format:H:i',
            'rest2_end' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string|max:255',

        ]);

        //休憩のバリデーション（休憩１が空欄で休憩２に入れようとしたらダメ）
        if (
            empty($request->rest1_start) && empty($request->rest1_end)
            && (!empty($request->rest2_start) || !empty($request->rest2_end))) {
            return back()->withErrors([
                'rest2_start' => '休憩1を入力せずに休憩2だけ登録することはできません。',
            ])->withInput();
        }

        //更新するattendance_idを取得
        $attendance = Attendance::findOrFail($attendanceId);

        // Attendance作成した日付のまま
        $attendanceDate = Carbon::parse($attendance->start_time)->format('Y-m-d');

        //RequestChangeに保存（即承認）
        //Attendance作成した日付のままで時間を登録
        RequestChange::create([
            'attendance_id' => $attendanceId,
            'user_id'       => auth()->id(),
            'start_time'    => $attendanceDate . ' '. $validated['start_time'],
            'end_time'      => $attendanceDate . ' '. $validated['end_time'],
            'rest1_start'   => $validated['rest1_start'] ? $attendanceDate . ' ' . $validated['rest1_start'] : null,
            'rest1_end'     => $validated['rest1_end']   ? $attendanceDate . ' ' . $validated['rest1_end']   : null,
            'rest2_start'   => $validated['rest2_start'] ? $attendanceDate . ' ' . $validated['rest2_start'] : null,
            'rest2_end'     => $validated['rest2_end']   ? $attendanceDate . ' ' . $validated['rest2_end']   : null,
            'reason'        => $validated['reason'],
            'status'        => 'approved',
        ]);

        //Attendanceテーブル更新
        $attendance->update([
            'start_time' => $attendanceDate . ' ' .$validated['start_time'],
            'end_time'   => $attendanceDate . ' ' .$validated['end_time'],
        ]);

        // 既存の休憩テーブルを取得（順に）
        $rests = $attendance->rests()->orderBy('id')->get();

        // 休憩1に入力があり、Restsテーブルにもデータがあれば更新。なければ新規作成。
        if (!empty($validated['rest1_start']) || !empty($validated['rest1_end'])) {
            if (isset($rests[0])) {
                $rests[0]->update([
                    'rest_start' => $validated['rest1_start'] ? $attendanceDate . ' ' . $validated['rest1_start'] : null,
                    'rest_end'   => $validated['rest1_end']   ? $attendanceDate . ' ' . $validated['rest1_end']   : null,
                ]);
            } else {
                $attendance->rests()->create([
                    'rest_start' => $validated['rest1_start'] ? $attendanceDate . ' ' . $validated['rest1_start'] : null,
                    'rest_end'   => $validated['rest1_end']   ? $attendanceDate . ' ' . $validated['rest1_end']   : null,
                ]);
            }
        }

        // 休憩2に入力があり、Restsテーブルにもデータがあれば更新。なければ新規作成。
        if (!empty($validated['rest2_start']) || !empty($validated['rest2_end'])) {
            if (isset($rests[1])) {
                $rests[1]->update([
                    'rest_start' => $validated['rest2_start'] ? $attendanceDate . ' ' . $validated['rest2_start'] : null,
                    'rest_end'   => $validated['rest2_end']   ? $attendanceDate . ' ' . $validated['rest2_end']   : null,
                ]);
            } else {
                $attendance->rests()->create([
                    'rest_start' => $validated['rest2_start'] ? $attendanceDate . ' ' . $validated['rest2_start'] : null,
                    'rest_end'   => $validated['rest2_end']   ? $attendanceDate . ' ' . $validated['rest2_end']   : null,
                ]);
            }
        }


        return redirect()->route('admin.attendance.show', $attendance->id)->with('success', '勤怠情報を更新しました');
    }
}
