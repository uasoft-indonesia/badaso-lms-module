<?php

namespace Uasoft\Badaso\Module\LMSModule\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Uasoft\Badaso\Module\LMSModule\Models\Announcement;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory(),
            'content' => $this->faker->text(),
            'created_by' => User::factory(),
        ];
    }
}
