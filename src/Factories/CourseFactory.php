<?php

namespace Uasoft\Badaso\Module\LMSModule\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Uasoft\Badaso\Module\LMSModule\Models\Course;

class CourseFactory extends Factory
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
            'subject' => $this->faker->name(),
            'room' => $this->faker->buildingNumber(),
            'join_code' => $this->faker->unique()->randomAscii(),
            'created_by' => User::factory(),
        ];
    }
}
