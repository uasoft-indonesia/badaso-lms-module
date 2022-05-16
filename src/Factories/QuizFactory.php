<?php

namespace Uasoft\Badaso\Module\LMSModule\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\Quiz;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class QuizFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'start_time' => $this->faker->dateTime(),
            'start_time' => $this->faker->dateTime(),
            'duration' => $this->faker->randomDigit(),
            'created_by' => User::factory(),
        ];
    }
}
