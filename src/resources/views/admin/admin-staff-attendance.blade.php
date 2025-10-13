@extends('admin.layouts.admin')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endpush

@section('content')

<div class="attendance-list">
    <div class="attendance__list__heading">
        <h2>{{ $staff->name }} さんの勤怠</h2>
    </div>


    <div class="attendance-month__heading">

        <div class="text-center">

            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id,'year' => $previousMonth->year, 'month' => $previousMonth->month]) }}">← 前月</a>

            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}">{{ $thisMonth->format('Y/m') }}</a>

            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id,'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">翌月 →</a>

        </div>
    </div>


    <div class="attendance__table">
        <table>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>勤務</th>
                <th>詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr>
                <!--指定月の日付を全表示-->
                <td>{{ \Carbon\Carbon::parse($attendance['date'])->format('Y年n月j日') }}</td>

                @if ($attendance['attendance'])
                <td>{{ \Carbon\Carbon::parse($attendance['attendance']->start_time)->isoFormat('H:mm') }}</td>
                <td>{{\Carbon\Carbon::parse($attendance['attendance']->end_time)->isoFormat('H:mm')}}</td>
                <td>
                    @if (!empty($attendance['rest_total_time']))
                    {{ sprintf('%02d:%02d', $attendance['rest_total_time']->h, $attendance['rest_total_time']->i) }}
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if (!empty($attendance['work_total_time']))
                    {{ sprintf('%02d:%02d', $attendance['work_total_time']->h, $attendance['work_total_time']->i) }}
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if ($attendance['attendance'])
                    <a href="{{ route('admin.attendance.show',['id' => $attendance['attendance']->id])}}">詳細</a>
                    @else
                    -
                    @endif
                </td>
                @else

                <td colspan="5" style="text-align: center;"></td>

                @endif
            </tr>
            @endforeach
        </table>
    </div>
</div>



@endsection