@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/requests-approval.css') }}">
@endpush

@section('content')
<div class="request-approval-show">
    <div class="request-approval__show__heading">
        <h2>勤怠詳細</h2>
    </div>

    <div class="request-approval__table">
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
                    {{ \Carbon\Carbon::parse($data['start_time'])->format('H:i') }}
                    〜
                    {{ \Carbon\Carbon::parse($data['end_time'])->format('H:i') }}
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td colspan="3">
                    @if($data['rest1_start'])
                    {{ \Carbon\Carbon::parse($data['rest1_start'])->format('H:i')}}
                    @else
                    -
                    @endif
                    〜
                    @if($data['rest1_end'])
                    {{ \Carbon\Carbon::parse($data['rest1_end'])->format('H:i')}}
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td colspan="3">
                    @if($data['rest2_start'])
                    {{ \Carbon\Carbon::parse($data['rest2_start'])->format('H:i')}}
                    @else
                    -
                    @endif
                    〜
                    @if($data['rest2_end'])
                    {{ \Carbon\Carbon::parse($data['rest2_end'])->format('H:i') }}
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td colspan="3">
                    {{ $requestChange->reason ?? '' }}
                </td>
            </tr>
        </table>
        <div class="request-approval__messages">
            <p>＊承認待ちのため修正はできません</p>
        </div>
    </div>

</div>
@endsection