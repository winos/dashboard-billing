<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\UserAction;
use App\Lib\Api\UserActionProcess;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller{

    public function userVoucherList(){
        $notify[] = "Voucher List";
        $vouchers = Voucher::where('user_type', 'USER')->where('user_id', auth()->id())->whereHas('currency')->with('currency')->orderBy('is_used', "ASC")->apiQuery();

        return response()->json([
            'remark'=>'voucher_list',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'vouchers'=>$vouchers,
            ]
        ]);
    }

    public function userVoucher(){ 
        $notify[] = "Create Voucher";
        $voucherCharge = TransactionCharge::where('slug', 'voucher_charge')->first();
        
        $wallets = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();
        $wallets = $this->withVoucherLimit($wallets, $voucherCharge);

        return response()->json([
            'remark'=>'create_voucher',
            'status'=>'success',
            'message'=>['success'=>$notify],

            'data'=>[
                'otp_type'=>otpType(),
                'wallets'=>$wallets,
                'voucher_charge'=>$voucherCharge,
            ]
        ]);
    }

    public function withVoucherLimit($wallets, $moneyOutCharge){
        foreach($wallets ?? [] as $wallet){ 
        
            $rate = $wallet->currency->rate;
            
            $min = $moneyOutCharge->min_limit/$rate;
            $max = $moneyOutCharge->max_limit/$rate;
    
            $wallet->currency->voucher_min_limit = $min;
            $wallet->currency->voucher_max_limit = $max;
        }
    
        return $wallets;
    }

    public function userVoucherCreate(Request $request){
       
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0',
            'wallet_id' => 'required|integer',
            'otp_type' => otpType(validation:true)
        ]);
      
        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $voucherCharge = TransactionCharge::where('slug', 'voucher_charge')->first();
        if (!$voucherCharge) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry, Transaction charge not found']],
            ]);
        }

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('id', $request->wallet_id)->first();
        if (!$wallet) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Wallet not found']],
            ]);
        }

        $rate = $wallet->currency->rate;
        if ($request->amount < currencyConverter($voucherCharge->min_limit, $rate) || $request->amount > currencyConverter($voucherCharge->max_limit, $rate)) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Please Follow the voucher limit']],
            ]);
        }

        $myVouchers = Voucher::where('user_type', 'USER')->where('user_id',auth()->id())->whereDate('created_at',now())->count();
        if ($voucherCharge->voucher_limit != -1 && $myVouchers >= $voucherCharge->voucher_limit) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Daily voucher create limit has been exceeded']],
            ]);
        }

        $rate = $wallet->currency->rate;
        $fixedCharge = currencyConverter($voucherCharge->fixed_charge, $rate);
        $totalCharge = chargeCalculator($request->amount, $voucherCharge->percent_charge, $fixedCharge);

        $cap = currencyConverter($voucherCharge->cap, $rate);
        if ($voucherCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        if ($wallet->currency->currency_type == 1) {
            $precision = 2;
        }else{
            $precision = 8;
        }

        $totalCharge = getAmount($totalCharge, $precision);
        $totalAmount = getAmount($request->amount + $totalCharge, $precision);

        if ($totalAmount > $wallet->balance) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Insufficient balance']],
            ]);
        }

        $userAction = new UserActionProcess();
        $userAction->user_id = auth()->user()->id;
        $userAction->user_type = 'USER';
        $userAction->act = 'create_voucher';

        $userAction->details = [
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'totalAmount' => $totalAmount,
            'totalCharge' => $totalCharge,
            'done_route' => 'api.voucher.create.done',
        ];

        if(count(otpType())){
            $userAction->type = $request->otp_type; 
        }
        $userAction->submit();
        $actionId = $userAction->action_id;
    
        if($userAction->verify_api_otp){
            return response()->json([
                'remark'=>'verify_otp',
                'status'=>'success',
                'message'=>['success'=>['Verify otp']],
                'data'=>[
                    'action_id'=>$actionId,
                ]
            ]);
        }
      
        return callApiMethod($userAction->next_route, $actionId);
    }

    public function userVoucherCreateDone($actionId){
      
        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('is_api', 1)->where('used', 0)->where('id', $actionId)->first();
        if (!$userAction) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Unable to process']],
            ]);
        }

        $details = $userAction->details;

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('id', $details->wallet_id)->first();
        if (!$wallet) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Wallet not found']],
            ]);
        }

        if(@$userAction->details->totalAmount > $wallet->balance){ 
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Insufficient balance in wallet']],
            ]);
        }

        $userAction->used = 1;
        $userAction->save();

        $wallet->balance -= $details->totalAmount;
        $wallet->save();

        $voucher = new Voucher();
        $voucher->user_id = auth()->id();
        $voucher->user_type =  'USER';
        $voucher->currency_id = $wallet->currency_id;
        $voucher->amount = $details->amount;
        $voucher->voucher_code = getVoucher();
        $voucher->save();

        $trx = new Transaction();
        $trx->user_id = auth()->id();
        $trx->user_type = 'USER';
        $trx->wallet_id = $wallet->id;
        $trx->currency_id = $wallet->currency_id;
        $trx->before_charge = $details->amount;
        $trx->amount = $details->totalAmount;
        $trx->post_balance =  $wallet->balance;
        $trx->charge =  $details->totalCharge;
        $trx->charge_type = '+';
        $trx->trx_type = '-';
        $trx->remark = 'create_voucher';
        $trx->details = 'Voucher created successfully';
        $trx->trx = getTrx();
        $trx->save();

        $withdraw = new Withdrawal();
        $withdraw->method_id = 0;
        $withdraw->user_id = auth()->id();
        $withdraw->user_type = 'USER';
        $withdraw->amount = $details->totalAmount;
        $withdraw->rate = $wallet->currency->rate;
        $withdraw->currency_id = $wallet->currency_id;
        $withdraw->wallet_id = $wallet->id;
        $withdraw->currency = $wallet->currency->currency_code;
        $withdraw->charge = $details->totalCharge;
        $withdraw->final_amount = $details->amount;
        $withdraw->after_charge = $withdraw->final_amount;
        $withdraw->withdraw_information = null;
        $withdraw->trx = getTrx();  
        $withdraw->status = 1;
        $withdraw->save();
        
        return response()->json([
            'remark'=>'voucher_create_done',
            'status'=>'success',
            'message'=>['success'=>['Voucher created successfully']],
        ]);
    } 

    public function userVoucherRedeemConfirm(Request $request){
        
        $request->validate([
            'code' => 'required'
        ]);
  
        $voucher = Voucher::where('voucher_code', $request->code)->where('user_id', '!=', auth()->user()->id)->where('is_used', 0)->first(); 
        if (!$voucher) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Invalid voucher code or This is one of your voucher']],
            ]);
        }
   
        $user = auth()->user();
        
        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('currency_id', $voucher->currency_id)->first();
        if (!$wallet) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Wallet not found']],
            ]);
        }

        $deposit = new Deposit();
        $deposit->user_id = $user->id;
        $deposit->user_type = 'USER';
        $deposit->method_code = 0;
        $deposit->amount = $voucher->amount;
        $deposit->wallet_id = $wallet->id;
        $deposit->currency_id = $wallet->currency_id;
        $deposit->method_currency = $wallet->currency->currency_code;
        $deposit->charge = 0;
        $deposit->rate = $wallet->currency->rate;
        $deposit->final_amount = $voucher->amount;
        $deposit->btc_amount = 0;
        $deposit->btc_wallet = "";
        $deposit->trx = getTrx();
        $deposit->status = 1;
        $deposit->save();

        $wallet->balance += $voucher->amount;
        $wallet->save();

        $trx = new Transaction();
        $trx->user_id = $user->id;
        $trx->user_type = 'USER';
        $trx->wallet_id = $wallet->id;
        $trx->currency_id = $wallet->currency_id;
        $trx->before_charge = $voucher->amount;
        $trx->amount = $voucher->amount;
        $trx->post_balance = $wallet->balance;
        $trx->charge =  0;
        $trx->charge_type = '+';
        $trx->trx_type = '+';
        $trx->remark = 'redeem_voucher';
        $trx->details = 'Redeemed Voucher ';
        $trx->trx = $deposit->trx;
        $trx->save();

        $voucher->is_used = 1;
        $voucher->redeemer_id = $user->id;
        $voucher->save();

        return response()->json([
            'remark'=>'voucher_redeem_done',
            'status'=>'success',
            'message'=>['success'=>[getAmount($voucher->amount) . ' ' . $deposit->method_currency . ' has been added to your wallet']],
        ]);
    }

    public function userVoucherRedeemLog(){ 
        $notify[] = "Voucher Redeem Log";
        $logs = Voucher::where('redeemer_id', auth()->id())->where('is_used', 1)->whereHas('currency')->apiQuery();

        return response()->json([
            'remark'=>'check_agent',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'logs'=>$logs,
            ]
        ]);
    }
}
