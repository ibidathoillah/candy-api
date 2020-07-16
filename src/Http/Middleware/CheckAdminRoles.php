<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\CandyApi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;


class CheckAdminRoles
{
    protected $api;

    public function __construct(CandyApi $api)
    {
        $this->api = $api;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->hasRole('admin')) {
            throw new AuthenticationException;
        }

        return $next($request);
    }
}
