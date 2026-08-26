<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'tagline' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'category_label' => 'Projeto',
            'accent' => fake()->randomElement(['amber', 'stone', 'emerald', 'sky']),
            'home_route' => null,
            'is_published' => true,
            'sort_order' => 10,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => false,
        ]);
    }
}
