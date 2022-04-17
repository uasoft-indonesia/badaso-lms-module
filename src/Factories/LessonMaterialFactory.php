<?php

namespace Uasoft\Badaso\Module\LMSModule\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class LessonMaterialFactory extends Factory
{
    protected $model = LessonMaterial::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory(),
            'title' => $this->faker->text(),
            'created_by' => User::factory(),
        ];
    }
}
