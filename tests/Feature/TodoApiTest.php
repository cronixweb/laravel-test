<?php

namespace Tests\Feature;

use App\Enums\TodoStatus;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_todo_and_category_when_missing(): void
    {
        $payload = [
            'title' => 'Buy groceries',
            'description' => 'Milk, eggs, bread',
            'status' => TodoStatus::Processing->value,
            'category_name' => 'Errands',
        ];

        $response = $this->postJson('/api/todos', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Buy groceries')
            ->assertJsonPath('data.status', TodoStatus::Processing->value)
            ->assertJsonPath('data.category.name', 'Errands');

        $this->assertDatabaseHas('categories', ['name' => 'Errands']);
        $this->assertDatabaseHas('todos', [
            'title' => 'Buy groceries',
            'status' => TodoStatus::Processing->value,
        ]);
    }

    public function test_it_updates_a_todo_and_reassigns_category(): void
    {
        $todo = Todo::factory()->create([
            'title' => 'Write report',
            'status' => TodoStatus::Pending->value,
        ]);

        $response = $this->putJson("/api/todos/{$todo->id}", [
            'status' => TodoStatus::Completed->value,
            'category_name' => 'Work',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', TodoStatus::Completed->value)
            ->assertJsonPath('data.category.name', 'Work');

        $this->assertDatabaseHas('categories', ['name' => 'Work']);
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'status' => TodoStatus::Completed->value,
        ]);
    }

    public function test_it_lists_todos(): void
    {
        Todo::factory()->count(2)->create();

        $response = $this->getJson('/api/todos');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_deletes_a_todo(): void
    {
        $todo = Todo::factory()->create();

        $response = $this->deleteJson("/api/todos/{$todo->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }
}
