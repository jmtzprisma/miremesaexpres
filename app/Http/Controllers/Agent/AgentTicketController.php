<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Traits\SupportTicketManager;

class AgentTicketController extends Controller
{
    use SupportTicketManager;

    public function __construct()
    {
        $this->activeTemplate = '';

        $this->middleware(function ($request, $next) {
            $this->user = authAgent();
            return $next($request);
        });

        $this->redirectLink = 'agent.ticket.view';
        $this->layout = 'master';
        $this->userType = 'agent';
        $this->column = 'agent_id';
    }
}
