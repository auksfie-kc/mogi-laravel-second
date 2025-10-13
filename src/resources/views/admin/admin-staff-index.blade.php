@extends('admin.layouts.admin')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-index.css') }}">
@endpush
@section('content')

<div class="attendance-list">
    <div class="attendance__list__heading">
        <h2>スタッフ一覧</h2>
    </div>

    <div class="attendance__table">
        <table>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><a href="{{ route('admin.attendance.staff',$user->id) }}"> 詳細 </a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection