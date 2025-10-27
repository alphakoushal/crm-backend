<?php

namespace App\Http\Middleware\Miscellaneous;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Config, DB, Log};
use Symfony\Component\HttpFoundation\Response;

class TrackDBQueries
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        // Checking if Application Envirement is 'local' then Create Queries Log
        if (config('app.env') === 'local') {
            Config::set('logging.channels.query_log', [
                'driver' => 'single',
                'path' => storage_path('logs/Queries/queryTracking_' . date('Y-m-d') . '.log'),
                'level' => 'info',
            ]);

            $queries = [];

            DB::listen(function ($query) use (&$queries) {
                $queries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms'
                ];
            });

            $response = $next($request);

            Log::channel('query_log')->info('Query count: ' . count($queries));
            Log::channel('query_log')->info('Executed queries:', $queries);
            Log::channel('query_log')->info(PHP_EOL);

            return $response;
        }

        return $next($request);
    
    }
}
