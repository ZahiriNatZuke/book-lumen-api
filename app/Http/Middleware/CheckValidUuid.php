<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class CheckValidUuid
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $baseUrl
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $baseUrl): mixed
    {
        $url = ltrim($request->getUri(), env('APP_URL'));
        $uuid = ltrim($url, $baseUrl);

        if (Uuid::isValid($uuid)) {
            return $next($request);
        } else {
            return response()->json(['error' => 'The UUID is not valid.'], 400);
        }
    }
}
