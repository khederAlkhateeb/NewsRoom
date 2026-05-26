<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    protected $articleRepo;
    protected $notificationService;

    /**
     * Dependency injection via Laravel Service Container.
     * * Thanks to Contextual Binding in AppServiceProvider, Laravel knows
     * that this specific controller MUST receive "DatabaseNotificationService".
     */
    public function __construct(
        ArticleRepositoryInterface $articleRepo,
        NotificationServiceInterface $notificationService
    ) {
        $this->articleRepo = $articleRepo;
        $this->notificationService = $notificationService; // Automatically resolved as DatabaseNotificationService
    }

    /**
     * Display admin dashboard stats and trigger a contextual notification.
     */
    public function index(): JsonResponse
    {
        $stats = $this->articleRepo->getDashboardStats();

        $admin = auth()->user() ?? User::where('role', 'admin')->first();
       
        if ($admin) {
            $this->notificationService->send($admin, "Admin Dashboard accessed successfully.");
        }

        return response()->json([
            'success' => true,
            'message' => 'Welcome to the Admin Dashboard',
            'data' => $stats
        ]);
    }
}