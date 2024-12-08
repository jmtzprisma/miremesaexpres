<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AgentKycMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $module = null) {
        $modules = gs()->kyc_modules;
        if (@$modules->agent->$module) {
            $agent = authAgent();

            if ($agent->kv == 0) {
                $notify[] = ['info', 'For being KYC verified, please provide these information'];
                $notify[] = ['error', 'You are not KYC verified.'];
                return to_route('agent.kyc.form')->withNotify($notify);
            }

            if ($agent->kv == 2) {
                $notify[] = ['warning', 'Please wait for admin approval'];
                $notify[] = ['info', 'Your documents for KYC verification is under review.'];
                return to_route('agent.kyc.data')->withNotify($notify);
            }
        }

        return $next($request);
    }
}
