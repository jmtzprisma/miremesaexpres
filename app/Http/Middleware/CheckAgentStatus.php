<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;

class CheckAgentStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guard('agent')->check()) {
            $agent = authAgent();
            if ($agent->status  && $agent->tv) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You need to verify your account first.';
                    return response()->json([
                        'remark' => 'unverified',
                        'status' => 'error',
                        'message' => ['error' => $notify],
                        'data' => [
                            'is_ban' => $agent->status,
                            'email_verified' => $agent->ev,
                            'mobile_verified' => $agent->sv,
                            'twofa_verified' => $agent->tv,
                        ],
                    ]);
                } else {
                    return to_route('agent.authorization');
                }
            }
        }
        abort(403);
    }
}
