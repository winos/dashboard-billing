<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;
use App\Lib\Api\UserActionProcess;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\User;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MoneyInController extends Controller
{

    use Common;

    public function checkUser(Request $request)
    {
        $user = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$user) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['User not found']],
            ]);
        }

        return response()->json([
            'remark'  => 'check_user',
            'status'  => 'success',
            'message' => ['success' => ['Check user']],
            'data'    => [
                'user' => $user,
            ],
        ]);
    }

    public function moneyInForm()
    {
        $notify[]      = "Money In";
        $moneyInCharge = TransactionCharge::where('slug', 'money_in_charge')->first();

        $wallets = Wallet::checkWallet(['user' => auth()->user('agent'), 'type' => 'AGENT'])->where('balance', '>', 0)->with('currency')->get();
        $wallets = $this->withMoneyInLimit($wallets, $moneyInCharge);

        return response()->json([
            'remark'  => 'money_in',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'otp_type'        => otpType(),
                'wallets'         => $wallets,
                'money_in_charge' => $moneyInCharge,
            ],
        ]);
    }

    public function withMoneyInLimit($wallets, $moneyOutCharge)
    {
        foreach ($wallets ?? [] as $wallet) {

            $rate = $wallet->currency->rate;

            $min = $moneyOutCharge->min_limit / $rate;
            $max = $moneyOutCharge->max_limit / $rate;

            $wallet->currency->money_out_min_limit = $min;
            $wallet->currency->money_out_max_limit = $max;
        }

        return $wallets;
    }

    public function confirmMoneyIn(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'user'      => 'required',
            'otp_type'  => otpType(validation: true),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $moneyInCharge = TransactionCharge::where('slug', 'money_in_charge')->first();
        if (!$moneyInCharge) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry, Transaction charge not found']],
            ]);
        }

        if ($moneyInCharge->daily_limit != -1 && $this->trxLimit('money_out')['daily'] > $moneyInCharge->daily_limit) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Your daily money in limit exceeded']],
            ]);
        }

        if ($moneyInCharge->monthly_limit != 1 && $this->trxLimit('money_out')['monthly'] > $moneyInCharge->monthly_limit) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Your monthly money in limit exceeded']],
            ]);
        }

        $agentWallet = Wallet::checkWallet(['user' => $this->guard()['user'], 'type' => 'AGENT'])->find($request->wallet_id);
        if (!$agentWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Wallet not found']],
            ]);
        }

        $user = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$user) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! User not found']],
            ]);
        }

        $currency   = $agentWallet->currency;
        $userWallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->where('currency_id', $currency->id)->first();

        if (!$userWallet) {
            createWallet($currency, $user);
        }

        $rate = $currency->rate;

        if ($request->amount < currencyConverter($moneyInCharge->min_limit, $rate) || $request->amount > currencyConverter($moneyInCharge->max_limit, $rate)) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Please Follow the money in limit']],
            ]);
        }

        //Agent commission
        $fixedCommission   = currencyConverter($moneyInCharge->agent_commission_fixed, $rate);
        $percentCommission = $request->amount * $moneyInCharge->agent_commission_percent / 100;
        $totalCommission   = $fixedCommission + $percentCommission;

        if ($request->amount > $agentWallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance in this wallet']],
            ]);
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = $this->guard()['user']->id;
        $userAction->user_type = $this->guard()['user_type'];
        $userAction->act       = 'money_in';

        $userAction->details = [
            'agentWallet_id'  => $agentWallet->id,
            'user_id'         => $user->id,
            'amount'          => $request->amount,
            'totalCommission' => $totalCommission,
            'done_route'      => 'api.agent.money.in.done',
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

    public function moneyInDone($actionId)
    {

        $userAction = UserAction::where('user_id', $this->guard()['user']->id)->where('user_type', 'AGENT')->where('is_api', 1)->where('used', 0)->where('id', $actionId)->first();
        if (!$userAction) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Unable to process']],
            ]);
        }

        $details = $userAction->details;

        $agentWallet = Wallet::checkWallet(['user' => $this->guard()['user'], 'type' => 'AGENT'])->find($details->agentWallet_id);
        if (!$agentWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Wallet not found']],
            ]);
        }

        $user = User::where('id', $details->user_id)->first();
        if (!$user) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! User not found']],
            ]);
        }

        $userWallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->where('currency_id', $agentWallet->currency->id)->first();
        if (!$userWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['User wallet not found']],
            ]);
        }

        if (@$userAction->details->amount > $agentWallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance']],
            ]);
        }

        $userAction->used = 1;
        $userAction->save();

        $agentWallet->balance -= $details->amount;
        $agentWallet->save();

        $agentTrx                = new Transaction();
        $agentTrx->user_id       = $this->guard()['user']->id;
        $agentTrx->user_type     = 'AGENT';
        $agentTrx->wallet_id     = $agentWallet->id;
        $agentTrx->currency_id   = $agentWallet->currency_id;
        $agentTrx->before_charge = $details->amount;
        $agentTrx->amount        = $details->amount;
        $agentTrx->post_balance  = $agentWallet->balance;
        $agentTrx->charge        = 0;
        $agentTrx->charge_type   = '+';
        $agentTrx->trx_type      = '-';
        $agentTrx->remark        = 'money_in';
        $agentTrx->details       = 'Money in to';
        $agentTrx->receiver_id   = $user->id;
        $agentTrx->receiver_type = 'USER';
        $agentTrx->trx           = getTrx();
        $agentTrx->save();

        $userWallet->balance += $details->amount;
        $userWallet->save();

        $userTrx                = new Transaction();
        $userTrx->user_id       = $user->id;
        $userTrx->user_type     = 'USER';
        $userTrx->wallet_id     = $userWallet->id;
        $userTrx->currency_id   = $userWallet->currency_id;
        $userTrx->before_charge = $details->amount;
        $userTrx->amount        = $details->amount;
        $userTrx->post_balance  = $userWallet->balance;
        $userTrx->charge        = 0;
        $userTrx->charge_type   = '+';
        $userTrx->trx_type      = '+';
        $userTrx->remark        = 'money_in';
        $userTrx->details       = 'Money in money from';
        $userTrx->receiver_id   = $this->guard()['user']->id;
        $userTrx->receiver_type = 'AGENT';
        $userTrx->trx           = $agentTrx->trx;
        $userTrx->save();

        if ($details->totalCommission) {
            $agentWallet->balance += $details->totalCommission;
            $agentWallet->save();

            $agentCommissionTrx                = new Transaction();
            $agentCommissionTrx->user_id       = $this->guard()['user']->id;
            $agentCommissionTrx->user_type     = 'AGENT';
            $agentCommissionTrx->wallet_id     = $agentWallet->id;
            $agentCommissionTrx->currency_id   = $agentWallet->currency_id;
            $agentCommissionTrx->before_charge = $details->totalCommission;
            $agentCommissionTrx->amount        = $details->totalCommission;
            $agentCommissionTrx->post_balance  = $agentWallet->balance;
            $agentCommissionTrx->charge        = 0;
            $agentCommissionTrx->charge_type   = '+';
            $agentCommissionTrx->trx_type      = '+';
            $agentCommissionTrx->remark        = 'commission';
            $agentCommissionTrx->details       = 'Money in commission';
            $agentCommissionTrx->trx           = $agentTrx->trx;
            $agentCommissionTrx->save();

            //Agent commission
            notify($this->guard()['user'], 'MONEY_IN_COMMISSION_AGENT', [
                'amount'        => showAmount($details->amount, $agentWallet->currency, currencyFormat:false),
                'currency_code' => $agentWallet->currency->currency_code,
                'commission'    => showAmount($details->totalCommission, $agentWallet->currency, currencyFormat:false),
                'trx'           => $agentTrx->trx,
                'time'          => showDateTime($agentTrx->created_at, 'd/M/Y @h:i a'),
                'balance'       => showAmount($agentWallet->balance, $agentWallet->currency, currencyFormat:false),
            ]);
        }

        //To user
        notify($user, 'MONEY_IN', [
            'amount'        => showAmount($details->amount, $userWallet->currency, currencyFormat:false),
            'currency_code' => $userWallet->currency->currency_code,
            'agent'         => $this->guard()['user']->username,
            'trx'           => $agentTrx->trx,
            'time'          => showDateTime($agentTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($userWallet->balance, $userWallet->currency, currencyFormat:false),
        ]);

        //To agent
        notify($this->guard()['user'], 'MONEY_IN_AGENT', [
            'amount'        => showAmount($details->amount, $agentWallet->currency, currencyFormat:false),
            'charge'        => 0,
            'currency_code' => $agentWallet->currency->currency_code,
            'user'          => $user->fullname,
            'trx'           => $agentTrx->trx,
            'time'          => showDateTime($agentTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($agentWallet->balance - $details->totalCommission, $agentWallet->currency, currencyFormat:false),
        ]);

        return response()->json([
            'remark'  => 'money_in_done',
            'status'  => 'success',
            'message' => ['success' => ['Money in successfully']],
        ]);
    }
}
