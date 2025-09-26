<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_task_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertTrue($task->user->is($user));
    }

    /** @test */
    public function casts_are_applied_completed_boolean_and_due_date_datetime()
    {
        $task = Task::factory()->create([
            'completed' => 1,
            'due_date' => now()->addDay(),
        ]);

        $this->assertIsBool($task->completed);
        $this->assertInstanceOf(\Carbon\Carbon::class, $task->due_date);
    }
}
