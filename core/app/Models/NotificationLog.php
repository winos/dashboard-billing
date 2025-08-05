<?php

namespace App\Models;

use App\Traits\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class NotificationLog extends Model
{
    use ApiQuery;

    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function agent(){
    	return $this->belongsTo(Agent::class);
    }

    public function merchant(){
    	return $this->belongsTo(Merchant::class);
    }

    public function getUser(): Attribute{
        return new Attribute(
            get:function(){
                if ($this->user_type == 'USER'){
                    $user = $this->user;
                }
                elseif($this->user_type == 'AGENT'){
                    $user = $this->agent;
                }
                elseif($this->user_type == 'MERCHANT'){
                    $user = $this->merchant;
                }

                return @$user;
            },
        );
    }

    public function goToUserProfile(): Attribute{ 
        return new Attribute( 
            get:function(){
                if($this->user_type == 'USER'){
                    $html = '<a href='.route('admin.users.detail', $this->user_id).'>'.@$this->user->username.'</a>';
                }
                elseif($this->user_type == 'AGENT'){
                    $html = '<a href='.route('admin.agents.detail', $this->agent_id).'>'.@$this->agent->username.'</a>';
                }
                elseif($this->user_type == 'MERCHANT'){
                    $html = '<a href='.route('admin.merchants.detail', $this->merchant_id).'>'.@$this->merchant->username.'</a>';
                }
              
                return $html;
            },
        );
    }
}
