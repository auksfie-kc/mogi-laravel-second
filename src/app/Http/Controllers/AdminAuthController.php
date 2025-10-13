<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminRegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * 管理者登録画面の表示
     */
    public function index(): View
    {
        return view('admin.admin-register');
    }

    public function register(AdminRegisterRequest $request): RedirectResponse
    {
        Admin::create([
            'name'     => $request['name'],
            'email'    => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
        return redirect()->route('admin.login')->with('success', '管理者を登録しました。ログインしてください。');
    }

    /**
     * 管理者ログイン画面の表示
     */
    public function create(): View
    {
        return view('admin.admin-login');
    }

    /**
     * 管理者ログイン処理
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.top'));
    }

    /**
     * 管理者ログアウト処理
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
