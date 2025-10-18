<?php

namespace Database\Factories;

use App\Enums\TodoStatus;
use App\Models\Category;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Todo>
 */
class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(TodoStatus::values()),
            'category_id' => Category::factory(),
        ];
    }
}
