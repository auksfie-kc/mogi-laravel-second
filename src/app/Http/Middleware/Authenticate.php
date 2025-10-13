<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        //adminでログインしていない場合に管理ログイン画面にリダイレクト
        if (request()->routeIs('admin.*')) {
            return $request->expectsJson() ? null : route('admin.login');
        }
        //それ以外の一般ユーザーはUser会員ログイン画面にリダイレクト
        return $request->expectsJson() ? null : route('login');
    }
}

