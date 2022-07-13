<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;

class LessonMaterialModelTest extends TestCase
{
    public function testDeleteTopicGivenMaterialAttachedToTheTopicExpectTopicIdSetToNull()
    {
        $topic = Topic::factory()->create();
        $lessonMaterial = LessonMaterial::factory()->create([
            'topic_id' => $topic->id,
        ]);

        $topic->delete();
        $lessonMaterial = $lessonMaterial->fresh();

        $this->assertNull($lessonMaterial->topic_id);
    }
}
