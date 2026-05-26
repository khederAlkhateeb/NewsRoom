<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Article;
use App\Models\User;
use App\Models\Profile;
class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $articles = Article::where('status', 'published')->get();
        $profiles = Profile::all();

        foreach ($articles as $article) {
            $commentCount = rand(1, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                Comment::create([
                    'user_id' => $users->random()->id,
                    'body' => fake()->realText(100),
                    'commentable_id' => $article->id,
                    'commentable_type' => Article::class,
                ]);
            }
        }

        foreach ($profiles as $profile) {
            if (fake()->boolean(40)) {
                Comment::create([
                    'user_id' => $users->random()->id,
                    'body' => fake()->realText(80),
                    'commentable_id' => $profile->id,
                    'commentable_type' => Profile::class,
                ]);
            }
        }
    }
}
