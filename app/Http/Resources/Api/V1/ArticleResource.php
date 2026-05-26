<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array for V1 (Web App).
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? $this->id,
            'title' => $this['title'] ?? $this->title,
            'content' => $this['content'] ?? $this->content,
            // Accessing writer name from eager loaded relation or array structure
            'writer_name' => isset($this['writer']['name']) ? $this['writer']['name'] : ($this->writer->name ?? 'Unknown'),
            'published_at' => $this->formatPublishedAt(data_get($this, 'published_at')),
        ];
    }
    private function formatPublishedAt($date): ?string
    {
        if (!$date) return null;
        
        if ($date instanceof \Carbon\Carbon) {
            return $date->toIso8601String();
        }
        return is_string($date) ? date(DATE_ISO8601, strtotime($date)) : null;
    }
}