<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\UserActionProcess;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class MakePaymentController extends Controller
{

    public function checkUser(Request $request)
    {
        $exist['data'] = Merchant::where('username', $request->merchant)->orWhere('email', $request->merchant)->first();
        return response($exist);
    }

    public function paymentFrom()
    {
        $pageTitle     = "Make Payment";
        $paymentCharge = TransactionCharge::where('slug', 'make_payment')->first();
        $wallets       = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();
        return view('Template::user.make_payment.index', compact('pageTitle', 'wallets', 'paymentCharge'));
    }

    public function paymentConfirm(Request $request)
    {

        $request->validate([
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'merchant'  => 'required',
            'otp_type'  => otpType(validation: true),
        ]);

        $user          = auth()->user();
        $paymentCharge = TransactionCharge::where('slug', 'make_payment')->firstOrFail();

        $wallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return back()->withNotify($notify)->withInput();
        }

        $merchant = Merchant::where('username', $request->merchant)->orWhere('email', $request->merchant)->first();
        if (!$merchant) {
            $notify[] = ['error', 'Sorry! Merchant not found'];
            return back()->withNotify($notify)->withInput();
        }

        $merchantWallet = Wallet::checkWallet(['user' => $merchant, 'type' => 'MERCHANT'])->where('currency_id', $wallet->currency->id)->first();
        if (!$merchantWallet) {
            $merchantWallet = createWallet($wallet->currency, $merchant);
        }

        //user charge
        $rate = $wallet->currency->rate;

        $fixedCharge = currencyConverter($paymentCharge->fixed_charge, $rate);
        $totalCharge = chargeCalculator($request->amount, $paymentCharge->percent_charge, $fixedCharge);

        //merchant charge
        $merchantFixedCharge = currencyConverter($paymentCharge->merchant_fixed_charge, $rate);
        $merchantTotalCharge = chargeCalculator($request->amount, $paymentCharge->merchant_percent_charge, $merchantFixedCharge);

        if ($wallet->currency->currency_type == 1) {
            $precision = 2;
        } else {
            $precision = 8;
        }

        $userTotalAmount     = getAmount($request->amount + $totalCharge, $precision);
        $totalCharge         = getAmount($totalCharge, $precision);
        $merchantTotalCharge = getAmount($merchantTotalCharge, $precision);

        $merchantAfterBalance = $merchantWallet->balance + $request->amount;
        if ($merchantAfterBalance < $merchantTotalCharge) {
            $notify[] = ['error', 'Sorry! payment couldn\'t be processed'];
            return back()->withNotify($notify)->withInput();
        }

        if ($userTotalAmount > $wallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in wallet'];
            return back()->withNotify($notify)->withInput();
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = userGuard()['type'];
        $userAction->act       = 'make_payment';

        $userAction->details = [
            'wallet_id'           => $wallet->id,
            'amount'              => $request->amount,
            'userTotalAmount'     => $userTotalAmount,
            'totalCharge'         => $totalCharge,
            'merchant_id'         => $merchant->id,
            'merchantTotalCharge' => $merchantTotalCharge,
            'done_route'          => route('user.payment.done'),
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function paymentDone()
    {

        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('id', session('action_id'))->first();
        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route('user.payment')->withNotify($notify)->withInput();
        }

        $details = $userAction->details;
        $user    = auth()->user();

        $wallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($details->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return to_route('user.payment')->withNotify($notify)->withInput();
        }

        $merchant = Merchant::where('id', $details->merchant_id)->first();
        if (!$merchant) {
            $notify[] = ['error', 'Sorry! Merchant not found'];
            return to_route('user.payment')->withNotify($notify)->withInput();
        }

        $merchantWallet = Wallet::checkWallet(['user' => $merchant, 'type' => 'MERCHANT'])->where('currency_id', $wallet->currency->id)->first();
        if (!$merchantWallet) {
            $merchantWallet = createWallet($wallet->currency, $merchant);
        }

        if (@$userAction->details->userTotalAmount > $wallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in wallet'];
            return to_route('user.payment')->withNotify($notify)->withInput();
        }

        $wallet->balance -= $details->userTotalAmount;
        $wallet->save();

        $senderTrx                = new Transaction();
        $senderTrx->user_id       = auth()->id();
        $senderTrx->user_type     = 'USER';
        $senderTrx->wallet_id     = $wallet->id;
        $senderTrx->currency_id   = $wallet->currency_id;
        $senderTrx->before_charge = $details->amount;
        $senderTrx->amount        = $details->userTotalAmount;
        $senderTrx->post_balance  = $wallet->balance;
        $senderTrx->charge        = $details->totalCharge;
        $senderTrx->charge_type   = '+';
        $senderTrx->trx_type      = '-';
        $senderTrx->remark        = 'make_payment';
        $senderTrx->details       = 'Payment successful to';
        $senderTrx->receiver_id   = $merchant->id;
        $senderTrx->receiver_type = "MERCHANT";
        $senderTrx->trx           = getTrx();
        $senderTrx->save();

        $merchantWallet->balance += $details->amount;
        $merchantWallet->save();

        $merchantTrx                = new Transaction();
        $merchantTrx->user_id       = $merchant->id;
        $merchantTrx->user_type     = 'MERCHANT';
        $merchantTrx->wallet_id     = $merchantWallet->id;
        $merchantTrx->currency_id   = $merchantWallet->currency_id;
        $merchantTrx->before_charge = $details->amount;
        $merchantTrx->amount        = $details->amount;
        $merchantTrx->post_balance  = $merchantWallet->balance;
        $merchantTrx->charge        = 0;
        $merchantTrx->charge_type   = '+';
        $merchantTrx->trx_type      = '+';
        $merchantTrx->remark        = 'make_payment';
        $merchantTrx->details       = 'Payment successful from';
        $merchantTrx->receiver_id   = auth()->id();
        $merchantTrx->receiver_type = "USER";
        $merchantTrx->trx           = $senderTrx->trx;
        $merchantTrx->save();

        if ($details->merchantTotalCharge > 0) {
            $merchantWallet->balance -= $details->merchantTotalCharge;
            $merchantWallet->save();

            $merchantTrx                = new Transaction();
            $merchantTrx->user_id       = $merchant->id;
            $merchantTrx->user_type     = 'MERCHANT';
            $merchantTrx->wallet_id     = $merchantWallet->id;
            $merchantTrx->currency_id   = $merchantWallet->currency_id;
            $merchantTrx->before_charge = $details->merchantTotalCharge;
            $merchantTrx->amount        = $details->merchantTotalCharge;
            $merchantTrx->post_balance  = $merchantWallet->balance;
            $merchantTrx->charge        = 0;
            $merchantTrx->charge_type   = '+';
            $merchantTrx->trx_type      = '-';
            $merchantTrx->remark        = 'payment_charge';
            $merchantTrx->details       = 'Payment charge';
            $merchantTrx->receiver_id   = auth()->id();
            $merchantTrx->receiver_type = "USER";
            $merchantTrx->trx           = $senderTrx->trx;
            $merchantTrx->save();
        }

        notify($user, 'MAKE_PAYMENT', [
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat:false),
            'charge'        => showAmount($details->totalCharge, $wallet->currency, currencyFormat:false),
            'currency_code' => $wallet->currency->currency_code,
            'merchant'      => $merchant->fullname . ' ( ' . $merchant->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($wallet->balance, $wallet->currency, currencyFormat:false),
        ]);

        notify($merchant, 'MAKE_PAYMENT_MERCHANT', [
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat:false),
            'charge'        => showAmount($details->merchantTotalCharge, $wallet->currency, currencyFormat:false),
            'currency_code' => $wallet->currency->currency_code,
            'user'          => $user->fullname . ' ( ' . $user->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($merchantWallet->balance, $wallet->currency, currencyFormat:false),
        ]);

        $notify[] = ['success', 'Payment successful'];
        return to_route('user.payment')->withNotify($notify);
    }

}
