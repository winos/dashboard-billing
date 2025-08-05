<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\SupportTicketManager;

class TicketController extends Controller
{
    use SupportTicketManager;

    public function __construct()
    {
        $userType = substr(auth()->user()->getTable(), 0, -1);

        parent::__construct();
        $this->userType   = $userType;
        $this->column     = 'user_id';
        $this->user       = auth()->user();
        $this->apiRequest = true;
    }
}
