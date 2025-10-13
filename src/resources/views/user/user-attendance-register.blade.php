@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-attendance-register.css') }}">
@endpush
@section('content')

<div class="user-attendance__form">
    <div>
        @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        @endif
    </div>

    <div class="user-attendance-form-message">
        @if (!$attendance)
        <p>勤務外</p>
        @elseif(!$attendance->end_time)
        @if ($isResting)
        <p>休憩中</p>
        @else
        <p>出勤中</p>
        @endif
        @else
        <p>退勤済</p>
        @endif
    </div>
    <div class="user-attendance-form-time">
        <p>{{ $now->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
        <p>{{ $now->isoFormat('H:mm') }}</p>

    </div>
    <div class="button-form">
        @if (!$attendance)
        <!-- 出勤前-->
        <form action="{{ route('timestamp.clockin') }}" method="POST">
            @csrf
            @method('POST')
            <button type="submit" class="btn btn-primary">出勤</button>
        </form>

        @elseif (!$attendance->end_time)
        <!-- 出勤済み・退勤前 -->
        @if ($isResting)
        <!-- 休憩中（まだ休憩終わっていない）：休憩終了ボタンのみ表示 -->
        <form action="{{ route('rest.end') }}" method="POST">
            @csrf
            @method('POST')
            <button type="submit" class="btn btn-warning">休憩戻</button>
        </form>
        @else
        <!-- 勤務中（休憩していない）：退勤ボタンと休憩開始ボタン -->
            <div class="work-buttons">
            <form action="{{ route('timestamp.clockout') }}" method="POST">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-success">退勤</button>
            </form>
            <form action="{{ route('rest.start') }}" method="POST">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-warning">休憩入</button>
            </form>
            @endif
            </div>
            @else
            <!-- 出勤・退勤済み -->
            <div class="form__message">
                <p>お疲れ様でした。</p>
            </div>
            @endif
        </div>
    </div>

    @endsection