<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\ApiQuery;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use ApiQuery;
    
    protected $casts = [
        'withdraw_information' => 'object'
    ];

    protected $hidden = [
        'withdraw_information'
    ];

    public function curr()
    {
        return $this->belongsTo(Currency::class,'currency_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet() 
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }

    public function agent()
    {
        return  $this->belongsTo(Agent::class,'user_id');
    }

    public function merchant()
    {
        return  $this->belongsTo(Merchant::class,'user_id');
    }

    public function method()
    {
        return $this->belongsTo(WithdrawMethod::class, 'method_id');
    }
    

    public function statusBadge(): Attribute
    {
        return new Attribute(function(){
            $html = '';
            if($this->status == Status::PAYMENT_PENDING){
                $html = '<span class="badge badge--warning">'.trans('Pending').'</span>';
            }elseif($this->status == Status::PAYMENT_SUCCESS){
                $html = '<span><span class="badge badge--success">'.trans('Approved').'</span><br>'.diffForHumans($this->updated_at).'</span>';
            }elseif($this->status == Status::PAYMENT_REJECT){
                $html = '<span><span class="badge badge--danger">'.trans('Rejected').'</span><br>'.diffForHumans($this->updated_at).'</span>';
            }
            return $html;
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PAYMENT_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', Status::PAYMENT_SUCCESS);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', Status::PAYMENT_REJECT);
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
                    $html = '<a href='.route('admin.users.detail', $this->user_id).'>@'.@$this->user->username.'</a>';
                }
                elseif($this->user_type == 'AGENT'){
                    $html = '<a href='.route('admin.agents.detail', $this->user_id).'>@'.@$this->agent->username.'</a>';
                } 
                elseif($this->user_type == 'MERCHANT'){
                    $html = '<a href='.route('admin.merchants.detail', $this->user_id).'>@'.@$this->merchant->username.'</a>';
                }
              
                return @$html;
            },
        );
    }

}
