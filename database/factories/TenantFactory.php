<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $company = $this->faker->unique()->company();

        return [
            'name' => $company,
            'slug' => Str::slug($company . '-' . $this->faker->unique()->randomNumber(3)),
        ];
    }
}

