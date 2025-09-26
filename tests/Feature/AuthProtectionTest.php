<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProtectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function protected_routes_require_authentication()
    {
        $this->getJson('/api/tasks')->assertStatus(401);
        $this->postJson('/api/tasks', [])->assertStatus(401);
        $this->getJson('/api/tasks/1')->assertStatus(401);
        $this->putJson('/api/tasks/1', [])->assertStatus(401);
        $this->deleteJson('/api/tasks/1')->assertStatus(401);
    }

    /** @test */
    public function user_cannot_access_others_tasks()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);

        \Laravel\Sanctum\Sanctum::actingAs($me);

        $this->getJson("/api/tasks/{$task->id}")->assertStatus(403);
        $this->putJson("/api/tasks/{$task->id}", ['title' => 'x'])->assertStatus(403);
        $this->deleteJson("/api/tasks/{$task->id}")->assertStatus(403);
    }
}
