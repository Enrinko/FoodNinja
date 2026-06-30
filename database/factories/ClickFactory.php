<?php

namespace Database\Factories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Click>
 */
class ClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referer' => fake()->url(),
            'created_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
