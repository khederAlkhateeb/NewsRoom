<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Resources\Api\V2\ArticleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    /**
     * Display a listing of published articles for V2 with extended payload.
     * * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        // Eager load writer, tags and append comments count specifically for V2 consumers
        $articles = Article::where('status', 'published')
            ->with(['writer', 'tags'])
            ->withCount('comments')
            ->latest('published_at')
            ->get();

        // Wrap the dynamic eloquent models inside the structured V2 Resource
        return ArticleResource::collection($articles);
    }
}