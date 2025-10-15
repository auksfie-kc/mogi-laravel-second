<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rest;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        return [
            'attendance_id' => 1,
            'rest_start' => $this->faker->dateTimeBetween('-2 hours', '-1 hours'),
            'rest_end' => null,
        ];
    }
}
