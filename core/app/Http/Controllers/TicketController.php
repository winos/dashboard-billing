<?php

namespace App\Http\Controllers;

use App\Traits\SupportTicketManager;

class TicketController extends Controller
{
    use SupportTicketManager;

    public function __construct()
    {
        parent::__construct();

        $data               = $this->processFormat();
        $this->user         = $data['user'];
        $this->layout       = $data['layout'];
        $this->userType     = $data['userType'];
        $this->redirectLink = 'ticket.view';
        $this->column       = 'user_id';
    }

    private function processFormat()
    {
        $user     = @userGuard();
        $userType = strtolower(@$user['type']);

        $array = [
            'user'     => $user['user'],
            'userType' => $userType ? $userType : 'user',
            'layout'   => $userType . '_master',
            'column'   => 'user_id',
        ];

        if (!$array['user']) {
            $array['layout'] = 'frontend';
        }

        return $array;
    }
}
