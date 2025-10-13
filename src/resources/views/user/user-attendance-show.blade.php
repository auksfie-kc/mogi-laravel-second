@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-show.css') }}">
@endpush

@section('content')
<div class="attendance-show">

    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif

    <div class="attendance__show__heading">
        <h2>勤怠詳細</h2>
    </div>

    <div class="attendance__table">

        <form action="{{ route('request.store') }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <table>
                <tr>
                    <th>名前</th>
                    <td colspan="3">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td colspan="3">
                        <span class="att-date__year">
                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('Y年') }}
                        </span>
                        <span></span>
                        <span class="att-date__md">
                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('n月j日') }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td colspan="3">
                        <input type="time" name="start_time" value="{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}"> 〜
                        <input type="time" name="end_time" value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}">
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td colspan="3">
                        <input type="time" name="rest1_start" value="{{ $restsDisplay[0]['start'] }}">
                        〜
                        <input type="time" name="rest1_end" value="{{ $restsDisplay[0]['end'] }}">
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td colspan="3">
                        <input type="time" name="rest2_start" value="{{ $restsDisplay[1]['start'] }}">
                        〜
                        <input type="time" name="rest2_end" value="{{ $restsDisplay[1]['end'] }}">
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        <input type="text" name="reason" value="{{ $attendance->reason ?? '' }}" placeholder="記入必須" required>
                    </td>
                </tr>
            </table>
            <button type="submit">修正</button>
        </form>
    </div>

</div>
@endsection