<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserLogin extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent(){
        return $this->belongsTo(Agent::class,'agent_id');
    }
    
    public function merchant(){
        return $this->belongsTo(Merchant::class,'merchant_id');
    }

    public function showUser(): Attribute{
        return new Attribute(
            get:function(){
                if ($this->user_id){
                    $user = $this->user;
                }
                elseif($this->agent_id){
                    $user = $this->agent;
                }
                elseif($this->merchant_id){
                    $user = $this->merchant;
                }

                return $user;
            },
        );
    }

    public function userType(): Attribute{
        return new Attribute(
            get:function(){
                if ($this->user_id){
                    $userType = trans('USER');
                }
                elseif($this->agent_id){
                    $userType = trans('AGENT');
                }
                elseif($this->merchant_id){
                    $userType = trans('MERCHANT');
                }

                $html = "<span class='fw-bold'>$userType</span>";

                return $html;
            },
        );
    }

    public function goToUserProfile(): Attribute{ 
        return new Attribute( 
            get:function(){
                if ($this->user_id){
                    $html = '<span><span class="small"><a href='.route('admin.users.detail', $this->user_id).'></span>@</span>'.@$this->user->username.'</a></span>';
                }
                elseif($this->agent_id){
                    $html = '<span><span class="small"><a href='.route('admin.agents.detail', $this->agent_id).'></span>@</span>'.@$this->agent->username.'</a></span>';
                }
                elseif($this->merchant_id){
                    $html = '<span><span class="small"><a href='.route('admin.merchants.detail', $this->merchant_id).'></span>@</span>'.@$this->merchant->username.'</a></span>';
                }
              
                return $html;
            },
        );
    }

}
