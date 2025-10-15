<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceRestEndTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_register_rest_end_correctly()
    {
        //ユーザーを作成
        $user = User::factory()->create();

        //出勤中の勤怠データを用意（退勤していない）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now()->subHours(3),
            'end_time' => null,
        ]);

        //既存の休憩開始データを登録（休憩中の状態を再現）
        $rest = Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => now()->subMinutes(30),
            'rest_end' => null,
        ]);

        //ログイン状態にする
        $this->actingAs($user);

        //POST送信（終了時間はコントローラ内でCarbon::now()）
        $response = $this->post(route('rest.end'));

        //リダイレクト確認
        $response->assertStatus(302);

        //DB確認（rest_end が更新されていること）
        $this->assertDatabaseMissing('rests', [
            'attendance_id' => $attendance->id,
            'rest_end' => null,
        ]);
    }
}
