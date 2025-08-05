<?php

namespace App\Lib\Api;

use App\Models\UserAction;

class UserActionProcess
{

    public $user_id;
    public $user_type;
    public $act;
    public $type;
    public $next_route;
    public $expiration = 0;
    public $is_otp     = 1;
    public $details    = [];

    public $action_id      = 0;
    public $verify_api_otp = 0;

    public function submit()
    {

        if ($this->type) {
            $this->is_otp = 1;
        } else {
            $this->is_otp = 0;
        }

        $general               = gs();
        $userAction            = new UserAction();
        $userAction->user_id   = $this->user_id;
        $userAction->user_type = $this->user_type;
        $userAction->act       = $this->act;
        $userAction->details   = $this->details;
        $userAction->is_otp    = $this->is_otp;
        $userAction->is_api    = 1;

        if ($general->en || $general->sn || $this->guard()['user']->ts) {
            $userAction->type       = $this->type;
            $userAction->otp        = verificationCode(6);
            $userAction->send_at    = now();
            $userAction->expired_at = now()->addSeconds((float) $general->otp_expiration);
        }

        $userAction->save();

        $this->action_id  = $userAction->id;
        $this->next_route = $this->details['done_route'];

        if ($this->is_otp == 1) {
            $this->verify_api_otp = 1;
            if ($this->type == 'email' || $this->type == 'sms') {
                notify($this->guard()['user'], 'OTP',
                    ['code' => $userAction->otp], [$this->type]
                );
            }
        }

    }

    public function guard()
    {

        $user     = auth()->user();
        $userType = strtoupper(substr($user->getTable(), 0, -1));

        if ($userType == 'USER') {
            $guard = 1;
        } elseif ($userType == 'AGENT') {
            $guard = 2;
        } elseif ($userType == 'MERCHANT') {
            $guard = 3;
        }

        return ['guard' => $guard, 'user_type' => $userType, 'user' => $user];
    }
}
