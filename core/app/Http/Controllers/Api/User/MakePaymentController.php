<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Api\UserActionProcess;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MakePaymentController extends Controller
{

    public function checkUser(Request $request)
    {
        $merchant = Merchant::where('username', $request->merchant)->orWhere('email', $request->merchant)->first();
        if (!$merchant) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Merchant nout found']],
            ]);
        }

        return response()->json([
            'remark'  => 'check_merchant',
            'status'  => 'success',
            'message' => ['success' => ['Check Merchant']],
            'data'    => [
                'agent' => $merchant,
            ],
        ]);
    }

    public function paymentFrom()
    {
        $notify[] = "Make Payment";

        $paymentCharge = TransactionCharge::where('slug', 'make_payment')->first();
        $wallets       = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();

        return response()->json([
            'remark'  => 'money_out',
            'status'  => 'success',
            'message' => ['success' => $notify],

            'data'    => [
                'otp_type'       => otpType(),
                'wallets'        => $wallets,
                'payment_charge' => $paymentCharge,
            ],
        ]);

    }

    public function paymentConfirm(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'merchant'  => 'required',
            'otp_type'  => otpType(validation: true),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        $paymentCharge = TransactionCharge::where('slug', 'make_payment')->first();
        if (!$paymentCharge) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry, Transaction charge not found']],
            ]);
        }

        $wallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Wallet not found']],
            ]);
        }

        $merchant = Merchant::where('username', $request->merchant)->orWhere('email', $request->merchant)->first();
        if (!$merchant) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Merchant not found']],
            ]);
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

        if ($wallet->currency->currency_type == Status::FIAT_CURRENCY) {
            $precision = 2;
        } else {
            $precision = 8;
        }

        $userTotalAmount     = getAmount($request->amount + $totalCharge, $precision);
        $merchantTotalAmount = getAmount($request->amount - $merchantTotalCharge, $precision);
        $totalCharge         = getAmount($totalCharge, $precision);
        $merchantTotalCharge = getAmount($merchantTotalCharge, $precision);

        $merchantAfterBalance = $merchantWallet->balance + $request->amount;
        if ($merchantAfterBalance < $merchantTotalCharge) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! payment couldn\'t be processed']],
            ]);
        }

        if ($userTotalAmount > $wallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance in wallet']],
            ]);
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = 'USER';
        $userAction->act       = 'make_payment';

        $userAction->details = [
            'wallet_id'           => $wallet->id,
            'amount'              => $request->amount,
            'userTotalAmount'     => $userTotalAmount,
            'totalCharge'         => $totalCharge,
            'merchant_id'         => $merchant->id,
            'merchantTotalCharge' => $merchantTotalCharge,
            'done_route'          => 'api.payment.done',
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();
        $actionId = $userAction->action_id;

        if ($userAction->verify_api_otp) {
            return response()->json([
                'remark'  => 'verify_otp',
                'status'  => 'success',
                'message' => ['success' => ['Verify otp']],
                'data'    => [
                    'action_id' => $actionId,
                ],
            ]);
        }

        return callApiMethod($userAction->next_route, $actionId);
    }

    public function paymentDone($actionId)
    {

        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('is_api', 1)->where('used', 0)->where('id', $actionId)->first();

        if (!$userAction) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Unable to process']],
            ]);
        }

        $details = $userAction->details;
        $user    = auth()->user();

        $wallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($details->wallet_id);
        if (!$wallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Wallet not found']],
            ]);
            $notify[] = ['error', 'Wallet not found'];
        }

        $merchant = Merchant::where('id', $details->merchant_id)->first();
        if (!$merchant) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Merchant not found']],
            ]);
        }

        $merchantWallet = Wallet::checkWallet(['user' => $merchant, 'type' => 'MERCHANT'])->where('currency_id', $wallet->currency->id)->first();
        if (!$merchantWallet) {
            $merchantWallet = createWallet($wallet->currency, $merchant);
        }

        if (@$userAction->details->userTotalAmount > $wallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance in wallet']],
            ]);
        }

        $userAction->used = 1;
        $userAction->save();

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
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat: false),
            'charge'        => showAmount($details->totalCharge, $wallet->currency, currencyFormat: false),
            'currency_code' => $wallet->currency->currency_code,
            'merchant'      => $merchant->fullname . ' ( ' . $merchant->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($wallet->balance, $wallet->currency, currencyFormat: false),
        ]);

        notify($merchant, 'MAKE_PAYMENT_MERCHANT', [
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat: false),
            'charge'        => showAmount($details->merchantTotalCharge, $wallet->currency, currencyFormat: false),
            'currency_code' => $wallet->currency->currency_code,
            'user'          => $user->fullname . ' ( ' . $user->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($merchantWallet->balance, $wallet->currency, currencyFormat: false),
        ]);

        return response()->json([
            'remark'  => 'make_payment_done',
            'status'  => 'success',
            'message' => ['success' => ['Payment successfully']],
        ]);

    }

}
