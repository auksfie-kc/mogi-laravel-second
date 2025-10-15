<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\UserRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminRequestController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/register');
});


Route::middleware(['auth'])->group(function () {

    // メール確認待ち画面
    Route::get('/email/verify', function () {
        return view('user.verify-email');
    })->name('verification.notice');

    // メール内のリンクをクリックした時
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/attendance'); // 認証後のリダイレクト先
    })->middleware(['signed'])->name('verification.verify');

    // 再送信ボタン
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware(['throttle:6,1'])->name('verification.send');
});



Route::middleware(['auth' ,'verified'])->group(function () {

    Route::get('/attendance', [UserAuthController::class, 'index'])->name('attendance.index');

    Route::post('/attendance/clockin', [UserAttendanceController::class, 'storeClockIn'])->name('timestamp.clockin');
    Route::post('/attendance/clockout', [UserAttendanceController::class,'storeClockOut'])->name('timestamp.clockout');
    Route::post('/attendance/rest-start', [UserAttendanceController::class, 'storeRestStart'])->name('rest.start');
    Route::post('/rest-end', [UserAttendanceController::class, 'storeRestEnd'])->name('rest.end');

    Route::get('/attendance/list/{year?}/{month?}', [UserAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id?}', [UserAttendanceController::class, 'show'])->name('attendance.show');

    Route::get('/stamp_correction_request/list', [UserRequestController::class, 'index'])->name('requests.index');
    Route::post('/user/requests/completed', [UserRequestController::class, 'store'])->name('request.store');
    Route::get('/user/requests/approval/{attendance?}', [UserRequestController::class, 'approval'])->name('request.approval');
});



Route::prefix('admin')->name('admin.')->group(function () {

    //管理者登録
    Route::get('/register', [AdminAuthController::class, 'index'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register'])->name('register.store');

    // 管理ログイン
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    Route::delete('/login', [AdminAuthController::class, 'destroy'])->name('login.destroy');

});

//管理者ログイン後よりアクセス可能
Route::middleware('auth:admin')->group(function () {

    Route::get('/admin/attendance/list/{year?}/{month?}/{day?}', [AdminAttendanceController::class, 'index'])->name('admin.top');
    Route::get('/admin/attendance/{id?}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/admin/attendance/update/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/admin/staff/list',[AdminStaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id?}/{year?}/{month?}', [AdminStaffController::class, 'show'])->name('admin.attendance.staff');
    Route::get('/admin/requests', [AdminRequestController::class, 'index'])->name('admin.request.index');
    Route::get('/admin/requests/{id}', [AdminRequestController::class, 'show'])->name('admin.request.show');
    Route::post('/admin/requests/completed', [AdminRequestController::class, 'approve'])->name('admin.request.approve');
});