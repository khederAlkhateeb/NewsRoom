<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attachment;
use App\Models\Article;
use App\Models\Profile;
use Illuminate\Support\Arr;
class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = Article::all();
        $profiles = Profile::all();

        $fileTypes = ['image', 'pdf', 'zip', 'docx'];

        foreach ($articles as $article) {
            if (fake()->boolean(60)) {
                $type = Arr::random($fileTypes);
                
                Attachment::create([
                    'file_path' => 'uploads/articles/file_' . fake()->uuid() . '.' . ($type === 'image' ? 'jpg' : $type),
                    'file_type' => $type,
                    'attachable_id' => $article->id,
                    'attachable_type' => Article::class,
                ]);
            }
        }

        foreach ($profiles as $profile) {
            if (fake()->boolean(20)) { 
                Attachment::create([
                    'file_path' => 'uploads/docs/cv_' . fake()->uuid() . '.pdf',
                    'file_type' => 'pdf',
                    'attachable_id' => $profile->id,
                    'attachable_type' => Profile::class,
                ]);
            }
        }
    }
}
