@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/requests-index.css') }}">
@endpush

@section('content')
<div class="requests-list">
    <div class="requests-list__heading">
        <h2>申請一覧</h2>
    </div>

    <div class="requests__list__tabs">
        <!-- タブで切り替え -->
        <a href="{{ route('requests.index', ['tab' => 'pending']) }}">
            <button class="{{ $activeTab === 'pending' ? 'active' : '' }}">承認待ち</button>
        </a>
        <a href="{{ route('requests.index', ['tab' => 'approved']) }}">
            <button class="{{ $activeTab === 'approved' ? 'active' : '' }}">承認済み</button>
        </a>
    </div>
    <div class="requests__list">
        <table class="requests__table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <!-- 承認待ち一覧 -->
            @if ($activeTab === 'pending')
            <tbody>
                @foreach($pendingRequests as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->start_time->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td><a href="{{ route('request.approval', $request->attendance->id) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>

            <!-- 承認済み一覧 -->
            @elseif ($activeTab === 'approved')
            <tbody>
                @foreach($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->start_time->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td><a href="{{ route('request.approval', $request->attendance->id) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
            @endif
        </table>
    </div>


</div>
@endsection