<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use App\Models\Article;

interface ArticleRepositoryInterface
{
    /**
     * Get all published articles optimized with relations.
     * * @return Collection
     */
    public function getAllPublished(): Collection;

    /**
     * Create a new article.
     * * @param array $data
     * @return Article
     */
    public function create(array $data): Article;

    /**
     * Get dashboard statistics related to articles.
     * * This method is specifically designed for the Admin Dashboard context.
     * * @return array
     */
    public function getDashboardStats(): array;
}