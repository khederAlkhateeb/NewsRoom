<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreArticleRequest;
// Inject V1 Resource to standardize the output
use App\Http\Resources\Api\V1\ArticleResource; 

class WriterArticleController extends Controller
{
    protected $articleRepo;
    protected $notificationService;

    // Dependency injection via Service Container
    public function __construct(
        ArticleRepositoryInterface $articleRepo,
        NotificationServiceInterface $notificationService
    ) {
        $this->articleRepo = $articleRepo;
        $this->notificationService = $notificationService; // This will automatically be EmailNotificationService
    }

    /**
     * Display a listing of published articles for V1.
     */
    public function index()
    {
        // Controller is decoupled from Eloquent; it just asks the abstraction layer
        $articles = $this->articleRepo->getAllPublished();

        // Wrap the collection using V1 Resource to maintain strict backward compatibility
        return ArticleResource::collection($articles);
    }

    /**
     * Store a newly created article in storage safely.
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        // If the execution reaches here, it means both Authorization and Validation passed!
        // The data is already sanitized (trimmed and formatted) inside the Form Request
        $validatedData = $request->validated();
        
        // Add the authenticated user ID automatically as the writer
        $validatedData['user_id'] = auth()->id();

        // Store via repository layer
        $article = $this->articleRepo->create($validatedData);

        // Sync tags if they were supplied in the request
        if ($request->has('tags')) {
            $article->tags()->sync($request->tags);
        }

        return response()->json([
            'success' => true,
            'message' => 'Article validated and created successfully.',
            'data' => $article->load('tags')
        ], 201);
    }
}