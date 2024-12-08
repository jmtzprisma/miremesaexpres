<?php

namespace App\Http\Controllers;

use App\Traits\SupportTicketManager;

class TicketController extends Controller
{
    use SupportTicketManager;

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            if ($this->user) {
                $this->layout = 'master';
            }
            return $next($request);
        });

        $this->layout = 'frontend';
        if (auth()->check() && $this->user->isAuthorized()) {
            $this->layout = 'master';
        }

        $this->redirectLink = 'ticket.view';
        $this->userType     = 'user';
        $this->column       = 'user_id';
    }
}
