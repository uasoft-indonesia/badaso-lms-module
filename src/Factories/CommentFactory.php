<?php

namespace Uasoft\Badaso\Module\LMSModule\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Uasoft\Badaso\Module\LMSModule\Models\Announcement;
use Uasoft\Badaso\Module\LMSModule\Models\Comment;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'announcement_id' => Announcement::factory(),
            'content' => $this->faker->text(),
            'created_by' => User::factory(),
        ];
    }
}
