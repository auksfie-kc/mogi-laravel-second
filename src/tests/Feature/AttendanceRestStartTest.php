<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Carbon;

class AttendanceRestStartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_register_rest_start_correctly()
    {
        //ユーザーと出勤データを作成
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now()->subHours(2),
            'end_time' => null,
        ]);

        //ログイン状態にする
        $this->actingAs($user);

        //休憩開始リクエストを送信
        $response = $this->post(route('rest.start'));

        //レスポンス確認
        $response->assertStatus(302);

        //DBに登録されているか確認
        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
        ]);
    }
}
