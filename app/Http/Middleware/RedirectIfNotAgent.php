<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAgent {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'agent') {
        if (!Auth::guard($guard)->check()) {
            return to_route('agent.login');
        }

        if (gs()->agent_module == Status::DISABLE) {
            $notify[] = ['error', 'Agent module is currently disabled'];
            Auth::guard($guard)->logout();
            return to_route('agent.login')->withNotify($notify);
        }

        return $next($request);
    }
}
