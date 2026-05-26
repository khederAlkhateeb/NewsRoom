<?php

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_path' => 'attachments/' . $this->faker->uuid() . '.jpg',
            'file_type' => 'image',
            'attachable_id' => 1,
            'attachable_type' => 'App\Models\Article',
        ];
    }
}
