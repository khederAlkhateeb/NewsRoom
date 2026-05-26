<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Arr;
class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $avatars = [
            'avatars/male1.png',
            'avatars/male2.png',
            'avatars/female1.png',
            'avatars/female2.png',
            null
        ];

        foreach ($users as $user) {
            Profile::create([
                'user_id' => $user->id,
                'bio' => fake()->boolean(80) ? fake()->realText(150) : null,
                'avatar' => Arr::random($avatars),
                'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
