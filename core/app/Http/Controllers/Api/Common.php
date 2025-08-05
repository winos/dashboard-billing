<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Carbon\Carbon;

trait Common{

    public function trxLimit($type){ 

        $user = $this->guard()['user']; 
        $userType = $this->guard()['user_type'];

        $transactions = $user->transactions()->leftJoin('currencies','currencies.id','=','transactions.currency_id')->where('user_type',$userType)
        ->where('remark', $type)
        ->selectRaw("SUM(amount * currencies.rate) as totalAmount");
        
        if ($type == 'request_money') {
            $transactions = $transactions->where('trx_type','-');
        }

        return [
            'daily' => $transactions->whereDate('transactions.created_at', Carbon::now())->get()->sum('totalAmount'),
            'monthly' => $transactions->whereMonth('transactions.created_at', Carbon::now())->get()->sum('totalAmount'),
        ];
    }

    public function guard(){

        $user = auth()->user();
        $userType = strtoupper(substr($user->getTable(), 0, -1));

        if ($userType == 'USER') {
            $guard = 1;
        } 
        elseif ($userType == 'AGENT') {
            $guard = 2;
        } 
        elseif ($userType == 'MERCHANT') {
            $guard = 3;
        }

        return ['guard'=>$guard, 'user_type'=>$userType, 'user'=>$user];
    }

    public function trxLog($request){
        
        $search = $request->search;
        $type = $request->type;
        $operation = $request->operation;

        if ($type && $type == 'plus_trx') {
            $type = '+';
        }
        elseif($type && $type == 'minus_trx') {
            $type = '-';
        }

        $time = $request->time;
        if ($time) {
            if($time == '7days')      $time = 7;
            elseif($time == '15days') $time = 15;
            elseif($time == '1month') $time = 31;
            elseif($time == '1year')  $time = 365;
        }

        $currency = strtoupper($request->currency);
        $user = $this->guard()['user']; 
        $userType = $this->guard()['user_type'];
        
        $histories = Transaction::where('user_id', $user->id)->where('user_type', $userType)
            ->when($search, function ($trx, $search) {
                return  $trx->where('trx', $search); 
            })
            ->when($type, function ($trx, $type) {
                return  $trx->where('trx_type', $type);
            })
            ->when($time, function ($trx, $time) {
                return  $trx->where('created_at', '>=', Carbon::today()->subDays($time));
            })
            ->when($operation, function ($trx, $operation) {
                return  $trx->where('remark', $operation);
            }) 
            ->when($currency, function ($trx, $currency) {
                return  $trx->whereHas('currency', function ($curr) use ($currency) {
                    $curr->where('currency_code', $currency);
                });
            })
        ->with(['currency', 'receiverUser', 'receiverAgent', 'receiverMerchant'])->apiQuery();
            
        return $histories;
    }
}
