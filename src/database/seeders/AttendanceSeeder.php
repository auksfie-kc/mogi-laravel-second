<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run():void
    {
        $users = User::all();

        foreach ($users as $user) {
            // 1〜10件の勤務記録を作成
            Attendance::factory()
                ->count(rand(1, 10))
                ->for($user) // user_id に紐づける
                ->create();
        }
    }
}