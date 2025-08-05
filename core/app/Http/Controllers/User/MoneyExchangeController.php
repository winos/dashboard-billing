<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\Wallet;
use Illuminate\Http\Request;

class MoneyExchangeController extends Controller
{

    public function exchangeForm()
    {
        $user           = auth()->user();
        $pageTitle      = "Exchange Money";
        $exchangeCharge = TransactionCharge::where('slug', 'exchange_charge')->first();
        return view('Template::user.exchange.form', compact('pageTitle', 'exchangeCharge', 'user'));
    }

    public function exchangeConfirm(Request $request)
    {

        $request->validate(
            [
                'amount'         => 'required|gt:0',
                'from_wallet_id' => 'required|integer',
                'to_wallet_id'   => 'required|integer',
            ],
            [
                'from_wallet_id.required' => 'Your wallet currency is required from which you want to exchange.',
                'to_wallet_id.required'   => 'Your wallet currency is required to which you want to exchange.',
            ]
        );

        $user           = auth()->user();
        $exchangeCharge = TransactionCharge::where('slug', 'exchange_charge')->first();
        $fromWallet     = Wallet::checkWallet(['user' => $user, 'type' => 'USER'])->find($request->from_wallet_id);

        if ($request->amount > $fromWallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in this wallet'];
            return back()->withNotify($notify)->withInput();
        }

        if (!$fromWallet) {
            $notify[] = ['error', 'Your wallet currency is not found from which you want to exchange.'];
            return back()->withNotify($notify);
        }

        $toWallet = Wallet::find($request->to_wallet_id);
        if (!$toWallet) {
            $notify[] = ['error', 'Your wallet currency is not found to which you want to exchange.'];
            return back()->withNotify($notify);
        }

        if ($fromWallet->id == $toWallet->id) {
            $notify[] = ['error', "Can\'t exchange money to same wallet"];
            return back()->withNotify($notify);
        }

        //Converting charges to FROM wallet currency
        $fromWalletAmount      = $request->amount;
        $fixedCharge           = $exchangeCharge->fixed_charge / $fromWallet->currency->rate;
        $totalFromWalletCharge = chargeCalculator($fromWalletAmount, $exchangeCharge->percent_charge, $fixedCharge);

        $cap = $exchangeCharge->cap / $fromWallet->currency->rate;
        if ($exchangeCharge->cap != -1 && $totalFromWalletCharge > $cap) {
            $totalFromWalletCharge = $cap;
        }

        if ($totalFromWalletCharge > $fromWallet->balance) {
            $notify[] = ['error', "Your don\'t have sufficient balance from which you want to exchange"];
            return back()->withNotify($notify);
        }

        $fromWalletAmount += $totalFromWalletCharge; //Total amount of FROM currency including charge
        $baseCurrAmount = $fromWallet->currency->rate * $request->amount; // Converting amount to site default currency

        // Converting amount to expected currency
        $finalAmount = getAmount($baseCurrAmount / $toWallet->currency->rate, 8);

        if ($toWallet->currency->currency_type == 1) {
            $finalAmount = getAmount($baseCurrAmount / $toWallet->currency->rate, 2);
        }

        $fromWallet->balance -= $fromWalletAmount;
        $fromWallet->save();

        $fromWalletTrx                = new Transaction();
        $fromWalletTrx->user_id       = $user->id;
        $fromWalletTrx->user_type     = 'USER';
        $fromWalletTrx->wallet_id     = $fromWallet->id;
        $fromWalletTrx->currency_id   = $fromWallet->currency_id;
        $fromWalletTrx->before_charge = $request->amount;
        $fromWalletTrx->amount        = $fromWalletAmount;
        $fromWalletTrx->post_balance  = $fromWallet->balance;
        $fromWalletTrx->charge        = $totalFromWalletCharge;
        $fromWalletTrx->charge_type   = '+';
        $fromWalletTrx->trx_type      = '-';
        $fromWalletTrx->remark        = 'exchange_money';
        $fromWalletTrx->details       = 'Exchange Money (From)';
        $fromWalletTrx->trx           = getTrx();
        $fromWalletTrx->save();

        $toWallet->balance += $finalAmount;
        $toWallet->save();

        $toWalletTrx                = new Transaction();
        $toWalletTrx->user_id       = $user->id;
        $toWalletTrx->user_type     = 'USER';
        $toWalletTrx->wallet_id     = $toWallet->id;
        $toWalletTrx->currency_id   = $toWallet->currency_id;
        $toWalletTrx->before_charge = $finalAmount;
        $toWalletTrx->amount        = $finalAmount;
        $toWalletTrx->post_balance  = $toWallet->balance;
        $toWalletTrx->charge        = 0;
        $toWalletTrx->charge_type   = '+';
        $toWalletTrx->trx_type      = '+';
        $toWalletTrx->remark        = 'exchange_money';
        $toWalletTrx->details       = 'Exchange Money (To)';
        $toWalletTrx->trx           = $fromWalletTrx->trx;
        $toWalletTrx->save();

        notify($user, 'EXCHANGE_MONEY', [
            'from_wallet_amount'   => $request->amount,
            'from_wallet_currency' => $fromWallet->currency->currency_code,
            'to_wallet_amount'     => showAmount($finalAmount, $fromWallet->currency, currencyFormat: false),
            'to_wallet_currency'   => $toWallet->currency->currency_code,
            'from_balance'         => showAmount($fromWallet->balance, $fromWallet->currency, currencyFormat: false),
            'to_balance'           => showAmount($toWallet->balance, $fromWallet->currency, currencyFormat: false),
            'trx'                  => $fromWalletTrx->trx,
            'time'                 => showDateTime($fromWalletTrx->created_at, 'd/M/Y @h:i a'),
        ]);

        $notify[] = ['success', 'Money exchanged successfully'];
        return back()->withNotify($notify);
    }
}
