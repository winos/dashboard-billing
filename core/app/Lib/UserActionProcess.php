<?php
 
namespace App\Lib;

use App\Models\UserAction;

class UserActionProcess{

    public $user_id;
	public $user_type;
	public $act;
	public $type;
	public $next_route;
	public $expiration = 0;
	public $is_otp = 1;
	public $details = [];

    public function submit(){

        if($this->type){
            $this->is_otp = 1;
        }else{
            $this->is_otp = 0;
        }
       
        $general = gs();
        $userAction = new UserAction();
        $userAction->user_id = $this->user_id;
        $userAction->user_type = $this->user_type;
        $userAction->act = $this->act;
        $userAction->details = $this->details;
        $userAction->is_otp = $this->is_otp;

        if ($general->en || $general->sn || userGuard()['user']->ts) {
            $userAction->type = $this->type;
            $userAction->otp = verificationCode(6);
            $userAction->send_at = now();
            $userAction->expired_at = now()->addSeconds((float)$general->otp_expiration);
        }

        $userAction->save();    

        session()->put('action_id', $userAction->id);
        $this->next_route = $this->details['done_route'];
      
        if ($this->is_otp == 1) {
            $this->next_route = route('verify.otp'); 
            if ($this->type == 'email' || $this->type == 'sms') {
                notify(userGuard()['user'], 'OTP', 
                    ['code'=>$userAction->otp], [$this->type]
                );
            }
        }

    }
}
