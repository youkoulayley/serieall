<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AmIThisUser.
 */
class AmIThisUser
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $wantUser = $request->route('user');

        if (strtolower($wantUser) != strtolower(Auth::user()->username)) {
            return redirect()->back();
        }

        return $next($request);
    }
}
