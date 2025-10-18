<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'description' => $this->faker->sentence(4),
            'type' => $this->faker->randomElement(Expense::TYPES),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
        ];
    }
}

