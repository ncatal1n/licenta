<?php

namespace App\Http\Middleware;

use App\Http\Controllers\UserHandler;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $userService;
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if(!$this->userService->validate($request))
        {
            return redirect("login");
        }
        return $next($request);
    }
}
