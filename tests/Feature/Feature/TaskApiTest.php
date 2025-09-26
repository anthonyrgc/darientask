<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_list_only_authenticated_user_tasks()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->count(2)->create(['user_id' => $me->id]);
        Task::factory()->count(3)->create(['user_id' => $other->id]);

        Sanctum::actingAs($me, ['*']);

        $res = $this->getJson('/api/tasks?per_page=50')->assertOk();
        $data = $res->json('data') ?? $res->json();
        $this->assertCount(2, $data);
    }

    /** @test */
    public function can_create_task_and_it_is_scoped_to_authenticated_user()
    {
        $me = User::factory()->create();
        Sanctum::actingAs($me, ['*']);

        $payload = [
            'title' => 'Tarea demo',
            'description' => 'Detalles',
            'due_date' => now()->addDay()->toDateTimeString(),
            'completed' => false,
        ];

        $res = $this->postJson('/api/tasks', $payload)
            ->assertCreated()
            ->assertJsonFragment(['title' => 'Tarea demo']);

        $taskId = $res->json('id') ?? data_get($res->json(), 'data.id');
        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'user_id' => $me->id,
            'title' => 'Tarea demo',
        ]);
    }

    /** @test */
    public function can_show_update_and_delete_own_task_but_not_others()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $myTask   = Task::factory()->create(['user_id' => $me->id, 'title' => 'Mine']);
        $hisTask  = Task::factory()->create(['user_id' => $other->id, 'title' => 'Not mine']);

        Sanctum::actingAs($me, ['*']);

        $this->getJson("/api/tasks/{$myTask->id}")
            ->assertOk()
            ->assertJsonFragment(['title' => 'Mine']);

        $this->getJson("/api/tasks/{$hisTask->id}")->assertStatus(403);

        $this->putJson("/api/tasks/{$myTask->id}", ['title' => 'Updated'])
            ->assertOk()
            ->assertJsonFragment(['title' => 'Updated']);

        $this->assertDatabaseHas('tasks', ['id' => $myTask->id, 'title' => 'Updated']);

        $this->putJson("/api/tasks/{$hisTask->id}", ['title' => 'XXX'])->assertStatus(403);

        $this->deleteJson("/api/tasks/{$myTask->id}")->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $myTask->id]);

        $this->deleteJson("/api/tasks/{$hisTask->id}")->assertStatus(403);
    }

    /** @test */
    public function list_supports_filters_and_sorting()
    {
        $me = User::factory()->create();
        Sanctum::actingAs($me, ['*']);

        $t1 = Task::factory()->create(['user_id' => $me->id, 'title' => 'aaa', 'completed' => false, 'due_date' => now()->addDays(2)]);
        $t2 = Task::factory()->create(['user_id' => $me->id, 'title' => 'bbb', 'completed' => true,  'due_date' => now()->addDay()]);
        $t3 = Task::factory()->create(['user_id' => $me->id, 'title' => 'ccc', 'completed' => false, 'due_date' => null]);

        $this->getJson('/api/tasks?title=aa&per_page=50')
            ->assertOk()
            ->assertJsonFragment(['title' => 'aaa']);

        $res = $this->getJson('/api/tasks?completed=true&per_page=50')->assertOk();
        $data = $res->json('data') ?? $res->json();
        $this->assertTrue(collect($data)->contains(fn($x) => $x['id'] == $t2->id));
        $this->assertFalse(collect($data)->contains(fn($x) => $x['id'] == $t1->id));

        $res = $this->getJson('/api/tasks?sort=due_date&dir=asc&per_page=50')->assertOk();
        $data = $res->json('data') ?? $res->json();
        $ids  = array_column($data, 'id');
        $this->assertEquals([$t2->id, $t1->id, $t3->id], $ids);
    }
}
