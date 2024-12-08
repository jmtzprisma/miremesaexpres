<?php

namespace App\Http\Controllers\Agent;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;


class AuthorizationController extends Controller
{

    public function authorizeForm()
    {
        $agent = authAgent();
        if (!$agent->status) {
            $pageTitle = 'Banned';
            $type = 'ban';
        } elseif (!$agent->tv) {
            $pageTitle = '2FA Verification';
            $type = '2fa';
        } else {
            return to_route('agent.dashboard');
        }

        return view('agent.auth.authorization.' . $type, compact('agent', 'pageTitle'));
    }

    public function g2faVerification(Request $request)
    {
        $agent = authAgent();
        $request->validate([
            'code' => 'required',
        ]);
        $response = verifyG2fa($agent, $request->code);
        if ($response) {
            $notify[] = ['success', 'Verification successful'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }
}
