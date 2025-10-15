<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_register_clockout_time_correctly()
    {
        //ユーザー作成
        $user = User::factory()->create();

        //事前に出勤記録を作成（退勤だけをテストするため）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::now()->subHours(8)->format('Y-m-d H:i:s'),
            'end_time' => null,
        ]);

        //ログイン状態にする
        $this->actingAs($user);

        //現在時刻を設定（退勤時刻）
        $now = Carbon::now()->format('Y-m-d H:i:s');

        //退勤打刻をPOST
        $response = $this->post(route('timestamp.clockout'), [
            'end_time' => $now,
            'attendance_id' => $attendance->id,
        ]);

        //正常終了を確認
        $response->assertStatus(302);

        //DBに正しい退勤時刻が入っているか確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $user->id,
            'end_time' => $now,
        ]);
    }
}
