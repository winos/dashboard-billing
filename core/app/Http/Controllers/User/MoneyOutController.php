<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\UserActionProcess;
use App\Models\Agent;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class MoneyOutController extends Controller
{
    public function checkUser(Request $request)
    {
        $exist['data'] = Agent::where('username', $request->agent)->orWhere('email', $request->agent)->first();
        return response($exist);
    }

    public function moneyOut()
    {
        $pageTitle      = "Money Out";
        $moneyOutCharge = TransactionCharge::where('slug', 'money_out_charge')->first();
        $wallets        = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();
        return view('Template::user.money_out.form', compact('pageTitle', 'wallets', 'moneyOutCharge'));
    }

    public function moneyOutConfirm(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'agent'     => 'required',
            'otp_type'  => otpType(validation: true),
        ]);

        $user = auth()->user();

        //Find Wallet
        $wallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return back()->withNotify($notify)->withInput();
        }

        //Find agent
        $agent = Agent::where('username', $request->agent)->orWhere('email', $request->agent)->first();
        if (!$agent) {
            $notify[] = ['error', 'Sorry! Agent not found'];
            return back()->withNotify($notify)->withInput();
        }

        $agentWallet = Wallet::checkWallet(['user' => $agent, 'type' => 'AGENT'])->where('currency_id', $wallet->currency->id)->first();
        if (!$agentWallet) {
            $agentWallet = createWallet($wallet->currency, $agent);
        }

        $rate           = $wallet->currency->rate;
        $moneyOutCharge = TransactionCharge::where('slug', 'money_out_charge')->firstOrFail();

        $crypto = false;
        if ($wallet->currency->currency_type == Status::CRYPTO_CURRENCY) {
            $crypto = true;
        }

        $min = number_format(currencyConverter($moneyOutCharge->min_limit, $rate), $crypto ? 8 : 2, '.', '');
        $max = number_format(currencyConverter($moneyOutCharge->max_limit, $rate), $crypto ? 8 : 2, '.', '');

        if ($request->amount < $min || $request->amount > $max) {
            $notify[] = ['error', 'Please Follow the money out limit'];
            return back()->withNotify($notify)->withInput();
        }

        $fixedCharge   = currencyConverter($moneyOutCharge->fixed_charge, $rate);
        $percentCharge = $request->amount * $moneyOutCharge->percent_charge / 100;
        $totalCharge   = $fixedCharge + $percentCharge;

        //Agent commission
        $fixedCommission   = currencyConverter($moneyOutCharge->agent_commission_fixed, $rate);
        $percentCommission = $request->amount * $moneyOutCharge->agent_commission_percent / 100;

        if ($wallet->currency->currency_type == Status::FIAT_CURRENCY) {
            $precision = 2;
        } else {
            $precision = 8;
        }

        $totalAmount = getAmount($request->amount + $totalCharge, $precision);

        if ($moneyOutCharge->daily_limit != -1 && (auth()->user()->trxLimit('money_out')['daily'] + toBaseCurrency($totalAmount, $rate)) > $moneyOutCharge->daily_limit) {
            $notify[] = ['error', 'Your daily money out limit exceeded'];
            return back()->withNotify($notify)->withInput();
        }

        if ($moneyOutCharge->monthly_limit != -1 && (auth()->user()->trxLimit('money_out')['monthly'] + toBaseCurrency($totalAmount, $rate)) > $moneyOutCharge->monthly_limit) {
            $notify[] = ['error', 'Your monthly money out limit exceeded'];
            return back()->withNotify($notify)->withInput();
        }

        $totalCommission = getAmount($fixedCommission + $percentCommission, $precision);
        $totalCharge     = getAmount($totalCharge, $precision);

        if ($totalAmount > $wallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in this wallet'];
            return back()->withNotify($notify)->withInput();
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = userGuard()['type'];
        $userAction->act       = 'money_out';

        $userAction->details = [
            'wallet_id'        => $wallet->id,
            'amount'           => $request->amount,
            'totalAmount'      => $totalAmount,
            'totalCharge'      => $totalCharge,
            'agent_id'         => $agent->id,
            'agent_wallet_id'  => $agentWallet->id,
            'total_commission' => $totalCommission,
            'done_route'       => route('user.money.out.done'),
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function moneyOutDone()
    {

        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('id', session('action_id'))->first();
        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route('user.money.out')->withNotify($notify)->withInput();
        }

        $user    = auth()->user();
        $details = $userAction->details;

        $wallet = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($details->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return to_route('user.money.out')->withNotify($notify)->withInput();
        }

        $agent = Agent::where('id', $details->agent_id)->first();
        if (!$agent) {
            $notify[] = ['error', 'Sorry! Agent not found'];
            return to_route('user.money.out')->withNotify($notify)->withInput();
        }

        $agentWallet = Wallet::checkWallet(['user' => $agent, 'type' => 'AGENT'])->where('currency_id', $wallet->currency->id)->first();
        if (!$agentWallet) {
            $agentWallet = createWallet($wallet->currency, $agent);
        }

        if (@$userAction->details->totalAmount > $wallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in wallet'];
            return to_route('user.money.out')->withNotify($notify)->withInput();
        }

        $wallet->balance -= $details->totalAmount;
        $wallet->save();

        $senderTrx                = new Transaction();
        $senderTrx->user_id       = auth()->id();
        $senderTrx->user_type     = 'USER';
        $senderTrx->wallet_id     = $wallet->id;
        $senderTrx->currency_id   = $wallet->currency_id;
        $senderTrx->before_charge = $details->amount;
        $senderTrx->amount        = $details->totalAmount;
        $senderTrx->post_balance  = $wallet->balance;
        $senderTrx->charge        = $details->totalCharge;
        $senderTrx->charge_type   = '+';
        $senderTrx->trx_type      = '-';
        $senderTrx->remark        = 'money_out';
        $senderTrx->details       = 'Money out to';
        $senderTrx->receiver_id   = $agent->id;
        $senderTrx->receiver_type = "AGENT";
        $senderTrx->trx           = getTrx();
        $senderTrx->save();

        $agentWallet->balance += $details->amount;
        $agentWallet->save();

        $agentTrx                = new Transaction();
        $agentTrx->user_id       = $agent->id;
        $agentTrx->user_type     = 'AGENT';
        $agentTrx->wallet_id     = $agentWallet->id;
        $agentTrx->currency_id   = $agentWallet->currency_id;
        $agentTrx->before_charge = $details->amount;
        $agentTrx->amount        = $details->amount;
        $agentTrx->post_balance  = $agentWallet->balance;
        $agentTrx->charge        = 0;
        $agentTrx->charge_type   = '+';
        $agentTrx->trx_type      = '+';
        $agentTrx->remark        = 'money_out';
        $agentTrx->details       = 'Money out from ';
        $agentTrx->receiver_id   = auth()->id();
        $agentTrx->receiver_type = "USER";
        $agentTrx->trx           = $senderTrx->trx;
        $agentTrx->save();

        if ($details->total_commission) {
            //Agent commission
            $agentWallet->balance += $details->total_commission;
            $agentWallet->save();

            $agentCommissionTrx                = new Transaction();
            $agentCommissionTrx->user_id       = $agent->id;
            $agentCommissionTrx->user_type     = 'AGENT';
            $agentCommissionTrx->wallet_id     = $agentWallet->id;
            $agentCommissionTrx->currency_id   = $agentWallet->currency_id;
            $agentCommissionTrx->before_charge = $details->total_commission;
            $agentCommissionTrx->amount        = $details->total_commission;
            $agentCommissionTrx->post_balance  = $agentWallet->balance;
            $agentCommissionTrx->charge        = 0;
            $agentCommissionTrx->charge_type   = '+';
            $agentCommissionTrx->trx_type      = '+';
            $agentCommissionTrx->remark        = 'commission';
            $agentCommissionTrx->details       = 'Money out commission';
            $agentCommissionTrx->trx           = $senderTrx->trx;
            $agentCommissionTrx->save();

            //Agent commission
            notify($agent, 'MONEY_OUT_COMMISSION_AGENT', [
                'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat:false),
                'currency_code' => $wallet->currency->currency_code,
                'commission'    => showAmount($details->total_commission, $wallet->currency, currencyFormat:false),
                'trx'           => $senderTrx->trx,
                'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
                'balance'       => showAmount($agentWallet->balance, $wallet->currency, currencyFormat:false),
            ]);
        }

        //To user
        notify(auth()->user(), 'MONEY_OUT', [
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat:false),
            'charge'        => showAmount($details->totalCharge, $wallet->currency, currencyFormat:false),
            'currency_code' => $wallet->currency->currency_code,
            'agent'         => $agent->fullname . ' ( ' . $agent->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($wallet->balance, $wallet->currency, currencyFormat:false),
        ]);

        //To agent
        notify($agent, 'MONEY_OUT_TO_AGENT', [
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat:false),
            'currency_code' => $wallet->currency->currency_code,
            'user'          => auth()->user()->fullname . ' ( ' . auth()->user()->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($agentWallet->balance - $details->total_commission, $wallet->currency, currencyFormat:false),
        ]);

        $notify[] = ['success', 'Money out successful'];
        return to_route('user.money.out')->withNotify($notify);
    }
}
