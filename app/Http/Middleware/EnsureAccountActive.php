<?php

namespace App\Http\Middleware;

use App\Features\Users\Domain\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Handle an incoming request.
     *
     * Suspended or inactive accounts cannot use protected routes, even
     * with a still-valid access token.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->status !== UserStatus::Active) {
            abort(403, 'This account is not active.');
        }

        return $next($request);
    }
}
