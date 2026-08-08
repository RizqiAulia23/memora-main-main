<?php

namespace Database\Seeders;

use App\Enums\LoveLetterMood;
use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::updateOrCreate(
            ['email' => 'demo@memorify.com'],
            [
                'name' => 'Riz & Alex',
                'password' => Hash::make('password'),
                'role' => 'user',
                'bio' => 'Every love story is beautiful, but ours is my favorite.',
                'partner_name' => 'Alex',
                'relationship_date' => now()->subYears(2)->format('Y-m-d'),
                'location' => 'Paris, France',
            ]
        );

        $demo->settings()->updateOrCreate(
            ['user_id' => $demo->id],
            ['theme' => 'light', 'notifications_enabled' => true]
        );

        if ($demo->memories()->count() === 0) {
            Memory::factory()
                ->count(12)
                ->for($demo)
                ->create()
                ->each(function (Memory $memory) use ($demo) {
                    if (fake()->boolean(40)) {
                        $demo->favorites()->create(['memory_id' => $memory->id]);
                    }
                });
        }

        if ($demo->loveLetters()->count() === 0) {
            LoveLetter::factory()
                ->count(5)
                ->for($demo)
                ->create();

            LoveLetter::factory()->for($demo)->pinned()->create([
                'title' => 'To the love of my life',
                'mood' => LoveLetterMood::Love,
                'letter_date' => now()->subDays(3)->format('Y-m-d'),
                'content' => '<p>My dearest,</p><p>Every day with you feels like a page torn straight out of a fairy tale.</p><blockquote>Forever & always.</blockquote>',
            ]);
        }
    }
}
