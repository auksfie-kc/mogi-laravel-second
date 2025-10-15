<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_register_clockin_time_correctly()
    {
        //ユーザー作成
        $user = User::factory()->create();

        //ログイン状態にする
        $this->actingAs($user);

        //今の日時を取得
        $now = Carbon::now()->format('Y-m-d H:i:s');

        //出勤打刻をPOST
        $response = $this->post(route('timestamp.clockin'), [
            'start_time' => $now,
        ]);

        //正常終了（リダイレクト確認）
        $response->assertStatus(302);

        //DBに記録されているか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'start_time' => $now,
        ]);
    }
}
