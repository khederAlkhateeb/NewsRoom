<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Str;
class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Politics',
            'Sports',
            'Technology',
            'Health',
            'Entertainment',
            'Business',
            'Science',
            'World',
            'Education',
            'Travel'
        ];

        foreach ($categories as $category) {
            Tag::create([
                'name' => $category,
                'slug' => Str::slug($category) ?: fake()->unique()->slug(2),
            ]);
        }
    }
}
