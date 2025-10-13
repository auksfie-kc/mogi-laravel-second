<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAttendanceRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RequestChange;
use Illuminate\Support\Facades\Auth;

class UserRequestController extends Controller
{
    public function index(Request $request)
    {
        // ログイン中のユーザーの承認待ち申請一覧を取得（最新順）
        $pendingRequests = RequestChange::with('attendance')
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // ログイン中のユーザーの承認済み申請一覧を取得（最新順）
        $approvedRequests = RequestChange::with('attendance')
            ->where('user_id', Auth::id())
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        //tabの初期表示はpendingのtabを表示
        $activeTab = $request->query('tab', 'pending');

        return view('user.user-requests-index', compact('pendingRequests', 'approvedRequests', 'activeTab'));
    }


    public function store(UserAttendanceRequest $request)
    {
        $attendance = Attendance::findOrFail($request->attendance_id);

        $validated = $request->validate();

        RequestChange::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'start_time' => $validated['start_time'] ?? null, //←空の場合はnull以下同様
            'end_time' => $validated['end_time'] ?? null,
            'rest1_start' => $validated['rest1_start'] ?? null,
            'rest1_end' => $validated['rest1_end'] ?? null,
            'rest2_start' => $validated['rest2_start'] ?? null,
            'rest2_end' => $validated['rest2_end'] ?? null,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('request.approval', $attendance->id);
    }

    public function approval(Attendance $attendance)
    {
        //修正申請の内容を取得
        $requestChange = $attendance->requestChanges()->first();

        //出退勤、休憩時間を格納
        $data = [];

        // 出勤時間(request_changesに修正申請データがあればそちらをなければattendancesテーブルのを取得)
        if ($requestChange && $requestChange->start_time) {
            $data['start_time'] = $requestChange->start_time;
        } else {
            $data['start_time'] = $attendance->start_time;
        }

        // 退勤時間
        if ($requestChange && $requestChange->end_time) {
            $data['end_time'] = $requestChange->end_time;
        } else {
            $data['end_time'] = $attendance->end_time;
        }

        // 休憩1開始
        if ($requestChange && $requestChange->rest1_start) {
            $data['rest1_start'] = $requestChange->rest1_start;
        } else {
            $data['rest1_start'] = $attendance->rest1_start ?? ''; // nullなら空文字で
        }

        // 休憩1終了
        if ($requestChange && $requestChange->rest1_end) {
            $data['rest1_end'] = $requestChange->rest1_end;
        } else {
            $data['rest1_end'] = $attendance->rest1_end ?? '';
        }
        // 休憩2開始
        if ($requestChange && $requestChange->rest2_start) {
            $data['rest2_start'] = $requestChange->rest2_start;
        } else {
            $data['rest2_start'] = $attendance->rest2_start ?? '';
        }

        // 休憩2終了
        if ($requestChange && $requestChange->rest2_end) {
            $data['rest2_end'] = $requestChange->rest2_end;
        } else {
            $data['rest2_end'] = $attendance->rest2_end ?? '';
        }

        return view('user.user-requests-approval', compact('attendance', 'requestChange', 'data'));
    }
}
