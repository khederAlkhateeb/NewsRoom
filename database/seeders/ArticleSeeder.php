<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = User::whereIn('role', ['writer', 'admin'])->get();

        if ($authors->isEmpty()) {
            return;
        }

        foreach ($authors as $author) {
            $articleCount = rand(3, 7);

            for ($i = 0; $i < $articleCount; $i++) {
                $status = fake()->randomElement(['draft', 'published', 'archived']);
                
                Article::create([
                    'user_id' => $author->id,
                    'title' => fake()->unique()->realText(50),
                    'content' => fake()->realText(1000), 
                    'status' => $status,
                    'published_at' => $status === 'published' ? fake()->dateTimeBetween('-6 months', 'now') : null,
                    'created_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
