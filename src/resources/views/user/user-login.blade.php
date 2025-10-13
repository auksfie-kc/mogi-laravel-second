@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')

<div class="login-form__content">

    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif
    <div class="login-form__heading">
        <h2>ログイン</h2>
    </div>


    <form class="form" action="/login" method="post">
        @csrf


        <!--メールアドレス-->
        <div class="form__group-title">
                <span class="form__label--item">メールアドレス</span>
                <div class="form__input-text">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
        </div>

        <!--パスワード-->
        <div class="form__group-title">
                <span class="form__label--item">パスワード</span>
                <div class="form__input-text">
                    <input type="password" name="password">
                </div>
        </div>


        <!--ログインボタン-->
        <div class="form__button">
            <button class="form__button-submit" type="submit">ログインする</button>
        </div>

    </form>


    <!-- 会員登録画面へ行くボタン -->
    <div class="form__register">
        <form action="/register" method="GET">
            <button>会員登録はこちら</button>
        </form>
    </div>

</div>
@endsection