@extends('user.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="verify-email__content">
        <div class="form-verify-body">
            <!-- 再送成功メッセージ -->
            @if (session('resent'))
            <div class="alert alert-success" role="alert">
                {{ __('ご登録いただいたメールアドレスに認証メールを再送信しました') }}
            </div>
            @endif

            <!-- 認証文章 -->
            <p>{{ __('登録していただいたメールアドレスに認証メールを送付しました。') }}</p>
            <p>{{ __('メール認証を完了してください。') }}</p>

            <!-- 外部メール確認ボタン -->
            <div class="form__verify">
                <a href="https://mailtrap.io/inboxes" target="_blank" class="form__verify-button">
                    認証はこちらから
                </a>
            </div>

            <!-- 再送ボタン -->
            <form class="form-resent-button" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resent-button">
                    {{ __('確認メールを再送する') }}</button>
            </form>

        </div>
    </div>
</div>
@endsection