<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
     
        try {
            $token = JWTAuth::getToken();

            if (empty($token)) {
                return response()->json([
                    "success" => false,
                    "error" => "Token Unavailable"
                ], 500);
            }
            // Authenticate the token
            $user = JWTAuth::authenticate($token);

            if (!$user) {
                return response()->json([
                    "success" => false,
                    "error" => "User not found"
                ], 401);
            }

            Auth::guard('api')->setUser($user);

            // \Illuminate\Support\Facades\Config::set('logging.channels.query_log', [
            //     'driver' => 'single',
            //     'path' => storage_path('logs/querylog.log'),
            //     'level' => 'info',
            // ]);

            // \Illuminate\Support\Facades\Log::channel('query_log')->info('Executed queries:');

            return $next($request);
        } catch (TokenExpiredException $e) {
            // Handle token expired
            return response()->json([
                "success" => false,
                "error" => "Token expired"
            ], 401);
        } catch (TokenInvalidException $e) {
            // Handle invalid token
            return response()->json([
                "success" => false,
                "error" => "Token invalid"
            ], 401);
        } catch (TokenBlacklistedException $e) {
            // Handle blacklisted token
            return response()->json([
                "success" => false,
                "error" => "Token blacklisted"
            ], 401);
        } catch (Exception $e) {
            // Handle other exceptions
            return response()->json([
                "success" => false,
                "error" => $e->getMessage()
            ], 401);
        }
    
    }
}
