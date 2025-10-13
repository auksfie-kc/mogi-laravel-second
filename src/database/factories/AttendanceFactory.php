<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', 'now');
        $end = (clone $start)->modify('+8 hours'); // 8時間勤務

        return [
            'date' => $start->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
        ];
    }
}
