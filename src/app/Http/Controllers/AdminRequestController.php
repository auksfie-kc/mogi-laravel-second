<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\RequestChange;
use Carbon\Carbon;
use App\Http\Requests\AdminAttendanceRequest;


class AdminRequestController extends Controller
{
    public function index(Request $request) {

        // ユーザーの承認待ち申請一覧を取得（最新順）
        $pendingRequests = RequestChange::with('attendance')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // ユーザーの承認済み申請一覧を取得（最新順）
        $approvedRequests = RequestChange::with('attendance')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        //tabの初期表示はpendingのtabを表示
        $activeTab = $request->query('tab', 'pending');

        return view('admin.admin-requests-index', compact('pendingRequests', 'approvedRequests', 'activeTab'));
    }

    public function show($id) {

        $attendance = Attendance::with('user')->find($id);

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


        return view('admin.admin-requests-approval', compact('attendance', 'requestChange', 'data'));
    }


    public function approve(AdminAttendanceRequest $request) {

        // 対象の申請を取得
        $requestChange = RequestChange::findOrFail($request->id);

        // ステータスをapprovedに変更して保存
        $requestChange->status = 'approved';
        $requestChange->save();


        //更新するattendance_idを取得
        $attendance = Attendance::findOrFail($request->attendance_id);
        // Attendance作成した日付のまま
        $attendanceDate = Carbon::parse($attendance->start_time)->format('Y-m-d');

        // Attendanceを更新
        $attendance->update([
            'start_time' => $attendanceDate . ' ' . Carbon::parse($requestChange->start_time)->format('H:i'),
            'end_time'   => $attendanceDate . ' ' . Carbon::parse($requestChange->end_time)->format('H:i'),
        ]);

        // Restテーブルの更新（複数休憩対応）
        $rests = Rest::where('attendance_id', $attendance->id)->orderBy('id')->get();

        // 1件目の更新または新規作成
        if ($requestChange->rest1_start && $requestChange->rest1_end) {
            if (isset($rests[0])) {
                $rests[0]->update([
                    'rest_start' => $attendanceDate . ' ' . Carbon::parse($requestChange->rest1_start)->format('H:i'),
                    'rest_end'   => $attendanceDate . ' ' . Carbon::parse($requestChange->rest1_end)->format('H:i'),
                ]);
            } else {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start' => $attendanceDate . ' ' . Carbon::parse($requestChange->rest1_start)->format('H:i'),
                    'rest_end'   => $attendanceDate . ' ' . Carbon::parse($requestChange->rest1_end)->format('H:i'),
                ]);
            }
        }

        // 2件目の更新または新規作成
        if ($requestChange->rest2_start && $requestChange->rest2_end) {
            if (isset($rests[1])) {
                $rests[1]->update([
                    'rest_start' => $attendanceDate . ' ' . Carbon::parse($requestChange->rest2_start)->format('H:i'),
                    'rest_end'   => $attendanceDate . ' ' . Carbon::parse($requestChange->rest2_end)->format('H:i'),
                ]);
            } else {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start' => $attendanceDate . ' ' . Carbon::parse($requestChange->rest2_start)->format('H:i'),
                    'rest_end'   => $attendanceDate . ' ' . Carbon::parse($requestChange->rest2_end)->format('H:i'),
                ]);
            }
        }

        return redirect()->back()->with('success', '申請を承認しました。勤怠情報を更新しました。');

    }
}