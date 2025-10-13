@extends('admin.layouts.admin')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endpush

@section('content')

<div class="attendance-list">
    <div class="attendance__list__heading">
        <h2>
            @if ($currentDate->isToday())
            <p>本日（{{ $currentDate->isoFormat('YYYY年MM月DD日') }}）の勤怠</p>
            @else
            <p>{{ $currentDate->isoFormat('YYYY年MM月DD日') }}の勤怠</p>
            @endif
        </h2>
    </div>

    <div class="attendance-month__heading">
        <div class="text-center">

            <!--前日のボタン-->
            <a href="{{ route('admin.top', [
            'year' => \Carbon\Carbon::parse($previousDate)->year,
            'month' => \Carbon\Carbon::parse($previousDate)->month,
            'day' => \Carbon\Carbon::parse($previousDate)->day,
            ]) }}">← 前日</a>

            <!--指定日のボタン なければ本日-->
            <a href="{{ route('admin.top') }}">{{ $currentDate->format('Y年m月d日') }}</a>

            <!--翌日のボタン-->
            <a href="{{ route('admin.top', [
            'year' => \Carbon\Carbon::parse($nextDate)->year,
            'month' => \Carbon\Carbon::parse($nextDate)->month,
            'day' => \Carbon\Carbon::parse($nextDate)->day,
            ]) }}">翌日 →</a>
        </div>
    </div>

    <div class="attendance__table">
        <table>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>

            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance['user_name'] }}</td>
                <td>{{ $attendance['start_time'] ? \Carbon\Carbon::parse($attendance['start_time'])->format('H:i') : '-' }}</td>
                <td>{{ $attendance['end_time'] ? \Carbon\Carbon::parse($attendance['end_time'])->format('H:i') : '-' }}</td>
                <td>{{ sprintf('%2d:%02d', floor($attendance['rest_total'] / 60), $attendance['rest_total'] % 60) }}</td>
                <td>{{ sprintf('%2d:%02d', floor($attendance['work_total'] / 60), $attendance['work_total'] % 60) }}</td>
                <td>
                    <a href="{{ route('admin.attendance.show',$attendance['id']) }}"> 詳細 </a>
                </td>

            </tr>
            @endforeach
        </table>


    </div>

</div>


@endsection