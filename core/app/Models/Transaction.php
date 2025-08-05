<?php

namespace App\Models;

use App\Traits\ApiQuery;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use ApiQuery;

    protected $appends = [];

    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->appends = ['apiDetails'];
        }
    }
    public function getApiDetailsAttribute()
    {
        $details = $this->details;
        $data    = $this->receiver ? @$this->receiver->username : '';
        return $details . ' ' . $data;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function receiverAgent()
    {
        return $this->belongsTo(Agent::class, 'receiver_id');
    }

    public function receiverMerchant()
    {
        return $this->belongsTo(Merchant::class, 'receiver_id');
    }

    public function getReceiverAttribute()
    {

        $user = null;
        if ($this->receiverUser && $this->receiver_type == 'USER') {
            $user = $this->receiverUser;
        } elseif ($this->receiverAgent && $this->receiver_type == 'AGENT') {
            $user = $this->receiverAgent;
        } elseif ($this->receiverMerchant && $this->receiver_type == 'MERCHANT') {
            $user = $this->receiverMerchant;
        }

        return $user;
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id')->withDefault();
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'user_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function getUser(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->user_type == 'USER') {
                    $user = $this->user;
                } elseif ($this->user_type == 'AGENT') {
                    $user = $this->agent;
                } elseif ($this->user_type == 'MERCHANT') {
                    $user = $this->merchant;
                }

                return @$user;
            },
        );
    }

    public function showUserType(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->user_type == 'USER') {
                    $userType = trans('USER');
                } elseif ($this->user_type == 'AGENT') {
                    $userType = trans('AGENT');
                } elseif ($this->user_type == 'MERCHANT') {
                    $userType = trans('MERCHANT');
                }

                $html = "<span class='fw-bold'>$userType</span>";

                return @$html;
            },
        );
    }

    public function save(array $options = [])
    {
        $result = parent::save($options);
        if ($result) {
            $trx = static::find($this->id);

            if ($trx->charge > 0 || $trx->remark == 'commission') {
                $chargeLog              = new ChargeLog();
                $chargeLog->user_id     = $trx->user_id;
                $chargeLog->user_type   = $trx->user_type;
                $chargeLog->amount      = $trx->charge == 0 ? '-' . $trx->amount : $trx->charge;
                $chargeLog->currency_id = $trx->currency_id;
                $chargeLog->trx         = $trx->trx;
                $chargeLog->remark      = $trx->remark;
                $chargeLog->save();
            }
        }
    }

}
