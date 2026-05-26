<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array for V2 (Mobile App) with extra data.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $content = $this['content'] ?? $this->content;

        return [
            'id' => $this['id'] ?? $this->id,
            'title' => $this['title'] ?? $this->title,
            'content' => $content,
            'writer_name' => isset($this['writer']['name']) ? $this['writer']['name'] : ($this->writer->name ?? 'Unknown'),
            'published_at' => isset($this['published_at']) ? $this['published_at'] : ($this->published_at ? $this->published_at->toIso8601String() : null),
            
            //Enhanced payloads for Mobile App without breaking V1
            'tags' => isset($this['tags']) ? collect($this['tags'])->pluck('name')->toArray() : $this->tags->pluck('name')->toArray(),
            'comments_count' => (int) ($this['comments_count'] ?? $this->comments_count ?? 0),
            'reading_time' => $this->calculateReadingTime($content),
        ];
    }

    /**
     * Calculate approximate reading time in minutes (Based on average 200 words per minute).
     *
     * @param  string|null  $content
     * @return int
     */
    private function calculateReadingTime(?string $content): int
    {
        if (!$content) {
            return 1;
        }
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / 200);
        return (int) max(1, $minutes);
    }
}