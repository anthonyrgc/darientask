<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function authUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        return $user;
    }

    /** @test */
    public function store_requires_title_and_valid_due_date()
    {
        $this->authUser();

        $this->postJson('/api/tasks', ['description' => 'x'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->postJson('/api/tasks', ['title' => 'ok', 'due_date' => 'not-a-date'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function update_validates_fields_when_present()
    {
        $user = $this->authUser();
        $task = $user->tasks()->create(['title' => 'One']); // 👈 usamos la relación del $user

        $this->putJson("/api/tasks/{$task->id}", ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->putJson("/api/tasks/{$task->id}", ['completed' => 'maybe'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['completed']);
    }
}
