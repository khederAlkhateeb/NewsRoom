<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    /**
     * Handle an incoming request (Before execution).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Record the start time of the request with microsecond precision
        $request->attributes->set('start_time', microtime(true));

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser (After Middleware).
     * * This ensures that the execution time calculation and heavy logging 
     * don't delay the response sent to the employee.
     */
    public function terminate(Request $request, Response $response): void
    {
        $startTime = $request->attributes->get('start_time');
        $endTime = microtime(true);
        
        // Calculate total duration in milliseconds
        $duration = $startTime ? round(($endTime - $startTime) * 1000, 2) : 0;

        $logData = [
            'user_id'    => auth()->id() ?? 'Guest',
            'ip_address' => $request->ip(),
            'method'     => $request->method(),
            'url'        => $request->fullUrl(),
            'status'     => $response->getStatusCode(),
            'duration_ms'=> $duration . 'ms',
            'timestamp'  => now()->toDateTimeString(),
        ];

        // Store performance data into log file securely
        Log::channel('single')->info('API Request Logged:', $logData);
    }
}