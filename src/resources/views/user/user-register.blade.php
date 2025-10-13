@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')

<div class="register-form__content">

    <div class="register-form__heading">
        <h2>会員登録</h2>
    </div>


    <form class="form" action="/register" method="post">
        @csrf

        @if (count($errors) > 0)
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{$error}}</li>
            @endforeach
        </ul>
        @endif
        <!--名前-->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">名前</span>
                <div class="form__input-text">
                    <input type="text" name="name" value="{{ old('name') }}">
                </div>
            </div>
        </div>

        <!--メールアドレス-->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">メールアドレス</span>
                <div class="form__input-text">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
            </div>
        </div>

        <!--パスワード-->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">パスワード</span>
                <div class="form__input-text">
                    <input type="password" name="password">
                </div>
            </div>
        </div>

        <!--確認用パスワード-->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">確認用パスワード</span>
                <div class="form__input-text">
                    <input type="password" name="password_confirmation">
                </div>
            </div>
        </div>

        <!--登録ボタン-->
        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
        </div>

    </form>


    <!-- ログイン画面へ行くボタン -->
    <div class="form__login">
        <form action="/login" method="GET">
            <button>ログインはこちら</button>
        </form>
    </div>

</div>
@endsection