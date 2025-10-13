<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Rest;

class RestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {

            // 出勤時間から退勤時間の間に1時間休憩を作成。出勤から3時間後に休憩を入れる
            $restStart = $attendance->start_time->copy()->addHours(3);
            $restEnd   = $restStart->copy()->addHour();

            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start'    => $restStart,
                'rest_end'      => $restEnd,
            ]);
    }
}
}