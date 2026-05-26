<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
class TaggableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = Article::all();
        $tags = Tag::all();

        if ($tags->isEmpty() || $articles->isEmpty()) {
            return;
        }

        foreach ($articles as $article) {
            $randomTags = $tags->random(rand(1, 3));

            foreach ($randomTags as $tag) {
                DB::table('taggables')->insertOrIgnore([
                    'tag_id' => $tag->id,
                    'taggable_id' => $article->id,
                    'taggable_type' => Article::class,
                ]);
            }
        }
    }
}
