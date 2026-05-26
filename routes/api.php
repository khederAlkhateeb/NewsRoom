<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Writer\WriterArticleController;
use App\Http\Controllers\Api\V2\ArticleController as ArticleControllerV2;

/*
|--------------------------------------------------------------------------
| API Routes with Versioning Architecture
|--------------------------------------------------------------------------
*/

// Public Global Middleware Group (Throttle applied to all API entry points)
Route::middleware(['api', 'throttle:api'])->group(function () {
    
    // Auth Endpoints (Global for all versions)
    Route::post('/login', [AuthController::class, 'login']);

    // =========================================================================
    // API VERSION 1 - STRICT BACKWARD COMPATIBILITY (Existing Web App Client)
    // =========================================================================
    Route::prefix('v1')->group(function () {
        
        // V1 Public Feed (Returns lightweight payload via V1 Resource)
        Route::get('/articles', [WriterArticleController::class, 'index']);

        // Strictly Protected V1 Routes (Requires valid Sanctum Token)
        Route::middleware(['auth:sanctum'])->group(function () {
            
            // Writer/Admin Content Creation for V1
            Route::middleware(['role:admin,writer'])->group(function () {
                Route::post('/articles', [WriterArticleController::class, 'store']);
            });

            // Executive Admin Only Control Panel for V1
            Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->middleware('role:admin');
        });
    });

    // =========================================================================
    // API VERSION 2 - NEW FEATURING (Enhanced Mobile App Client)
    // =========================================================================
    Route::prefix('v2')->group(function () {
        
        // V2 Public Feed (Returns enriched payload: tags, comments_count, reading_time)
        Route::get('/articles', [ArticleControllerV2::class, 'index']);
        
    });

    // =========================================================================
    // Global Protected Actions (Not bound to specific API view version)
    // =========================================================================
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});