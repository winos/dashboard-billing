<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChargeLog extends Model
{
    use HasFactory;

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
    
    public function user() { 
        return $this->belongsTo(User::class);
    }

    public function agent(){
        return  $this->belongsTo(Agent::class, 'user_id', 'id');
    }
    
    public function merchant(){
        return  $this->belongsTo(Merchant::class, 'user_id', 'id');
    }

    public function showUserType(): Attribute{
        return new Attribute(
            get:function(){
                if ($this->user_type == 'USER'){
                    $userType = trans('USER');
                }
                elseif($this->user_type == 'AGENT'){
                    $userType = trans('AGENT');
                }
                elseif($this->user_type == 'MERCHANT'){
                    $userType = trans('MERCHANT');
                }

                $html = "<span class='fw-bold'>$userType</span>";

                return @$html;
            },
        );
    }

    public function goToUserProfile(): Attribute{
        return new Attribute(
            get:function(){
                if($this->user_type == 'USER'){
                    $html = '<span><span class="small"><a href='.route('admin.users.detail', $this->user_id).'></span>@</span>'.@$this->user->username.'</a></span>';
                }
                elseif($this->user_type == 'AGENT'){
                    $html = '<span><span class="small"><a href='.route('admin.agents.detail', $this->user_id).'></span>@</span>'.@$this->agent->username.'</a></span>';
                }
                elseif($this->user_type == 'MERCHANT'){
                    $html = '<span><span class="small"><a href='.route('admin.merchants.detail', $this->user_id).'></span>@</span>'.@$this->merchant->username.'</a></span>';
                }
              
                return @$html;
            },
        );
    }
}
