<?php

namespace Uasoft\Badaso\Module\LMSModule\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Uasoft\Badaso\Module\LMSModule\Models\Assignment;
use Uasoft\Badaso\Module\LMSModule\Models\Submission;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory(),
        ];
    }
}
