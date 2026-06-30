<?php

namespace Database\Seeders;

use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a demo account and sample data.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        Link::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->each(function (Link $link): void {
                Click::factory()
                    ->count(random_int(0, 25))
                    ->for($link)
                    ->create();
            });
    }
}
