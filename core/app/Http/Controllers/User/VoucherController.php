<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Voucher;
use App\Constants\Status;
use App\Models\UserAction;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Lib\UserActionProcess;
use App\Models\TransactionCharge;
use App\Http\Controllers\Controller;

class VoucherController extends Controller{

    public function userVoucherList(){
        $pageTitle = "Voucher List";
        $vouchers = Voucher::where('user_type', 'USER')->where('user_id', auth()->id())->whereHas('currency')
        ->with('currency')->orderBy('is_used', "ASC")->orderBy('id', "DESC")->paginate(getPaginate());
        return view('Template::user.voucher.list', compact('pageTitle', 'vouchers'));
    }

    public function userVoucher(){
        $pageTitle = "Create Voucher";
        $wallets = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();
        $voucherCharge = TransactionCharge::where('slug', 'voucher_charge')->first();
        return view('Template::user.voucher.create', compact('pageTitle', 'wallets', 'voucherCharge'));
    }

    public function userVoucherCreate(Request $request){
   
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'wallet_id' => 'required|integer',
            'otp_type' => otpType(validation:true)
        ]);

        $voucherCharge = TransactionCharge::where('slug', 'voucher_charge')->first();
        if (!$voucherCharge) {
            $notify[] = ['error', 'Sorry! Something went wrong. Please try again'];
            return redirect(route('user.voucher.create'))->withNotify($notify);
        }

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('id', $request->wallet_id)->first();
        if (!$wallet) {
            $notify[] = ['error', 'Sorry! Wallet not found'];
            return redirect(route('user.voucher.create'))->withNotify($notify);
        }

        $rate = $wallet->currency->rate;
        if ($request->amount < currencyConverter($voucherCharge->min_limit, $rate) || $request->amount > currencyConverter($voucherCharge->max_limit, $rate)) {
            $notify[] = ['error', 'Please Follow the voucher limit'];
            return back()->withNotify($notify)->withInput();
        }

        $myVouchers = Voucher::where('user_type',userGuard()['type'])->where('user_id',auth()->id())->whereDate('created_at',now())->count();

        if ($voucherCharge->voucher_limit != -1 && $myVouchers >= $voucherCharge->voucher_limit) {
            $notify[] = ['error', 'Daily voucher create limit has been exceeded'];
            return back()->withNotify($notify)->withInput();
        }

        $rate = $wallet->currency->rate;
        $fixedCharge = currencyConverter($voucherCharge->fixed_charge, $rate);
        $totalCharge = chargeCalculator($request->amount, $voucherCharge->percent_charge, $fixedCharge);

        $cap = currencyConverter($voucherCharge->cap, $rate);
        if ($voucherCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        if ($wallet->currency->currency_type == Status::FIAT_CURRENCY) {
            $precision = 2;
        }else{
            $precision = 8;
        }

        $totalCharge = getAmount($totalCharge, $precision);
        $totalAmount = getAmount($request->amount + $totalCharge, $precision);

        if ($totalAmount > $wallet->balance) {
            $notify[] = ['error', 'Insufficient balance'];
            return back()->withNotify($notify)->withInput();
        }

        $userAction = new UserActionProcess();
        $userAction->user_id = auth()->user()->id;
        $userAction->user_type = userGuard()['type'];
        $userAction->act = 'create_voucher';

        $userAction->details = [
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'totalAmount' => $totalAmount,
            'totalCharge' => $totalCharge,
            'done_route' => route('user.voucher.create.done'),
        ];

        if(count(otpType())){
            $userAction->type = $request->otp_type; 
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function userVoucherCreateDone(){
    
        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('id', session('action_id'))->first();
        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route('user.voucher.create')->withNotify($notify)->withInput();
        }

        $details = $userAction->details;

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('id', $details->wallet_id)->first();
        if (!$wallet) {
            $notify[] = ['error', 'Sorry! Wallet not found'];
            return to_route('user.voucher.create')->withNotify($notify)->withInput();
        }

        if(@$userAction->details->totalAmount > $wallet->balance){ 
            $notify[]=['error','Sorry! Insufficient balance in wallet'];
            return to_route('user.voucher.create')->withNotify($notify)->withInput();
        }

        $wallet->balance -=  $details->totalAmount;
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
        
        $notify[] = ['success', 'Voucher created successfully'];
        return to_route('user.voucher.list')->withNotify($notify);
    }

    public function userVoucherRedeem(){
        $pageTitle = "Redeem Voucher";
        return view('Template::user.voucher.redeem', compact('pageTitle'));
    }

    public function userVoucherRedeemConfirm(Request $request){
 
        $request->validate([
            'code' => 'required'
        ]);

        $voucher = Voucher::where('voucher_code', $request->code)->where('user_id', '!=', auth()->user()->id)->where('is_used', 0)->first();
        if (!$voucher) {
            $notify[] = ['error', 'Invalid voucher code or This is one of your voucher'];
            return back()->withNotify($notify);
        }

        $user = auth()->user();
        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('currency_id', $voucher->currency_id)->first();

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

        $notify[] = ['success', getAmount($voucher->amount) . ' ' . $deposit->method_currency . ' has been added to your wallet'];
        return back()->withNotify($notify);
    }

    public function userVoucherRedeemLog(){
        $pageTitle = "Voucher Redeem Log";
        $logs = Voucher::where('redeemer_id', auth()->id())->where('is_used', 1)->whereHas('currency')->paginate(getPaginate());
        return view('Template::user.voucher.redeem_log', compact('pageTitle', 'logs'));
    }
}
