<?php

namespace App\Traits;

use App\Lib\FormProcessor;
use App\Lib\UserActionProcess;
use App\Models\AdminNotification;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\UserAction;
use App\Models\UserWithdrawMethod;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;

trait WithdrawProcess
{

    public function addWithdrawMethodPage()
    {
        $pageTitle      = "Add Withdraw Method";
        $guard          = userGuard()['guard'];
        $withdrawMethod = WithdrawMethod::whereJsonContains('user_guards', "$guard")->where('status', 1)->get();
        $currencies     = Currency::pluck('id', 'currency_code');
        return view('Template::' . strtolower(userGuard()['type']) . '.withdraw.add_method', compact('pageTitle', 'withdrawMethod', 'currencies'));
    }

    public function addWithdrawMethod(Request $request)
    {

        $rules       = ['name' => 'required', 'method_id' => 'required', 'currency_id' => 'required'];
        $storeHelper = $this->storeHelper($request, $rules);
        $request->validate($storeHelper['rules']);

        $userMethod              = new UserWithdrawMethod();
        $userMethod->name        = $request->name;
        $userMethod->user_id     = userGuard()['user']->id;
        $userMethod->user_type   = userGuard()['type'];
        $userMethod->method_id   = $request->method_id;
        $userMethod->currency_id = $request->currency_id;
        $userMethod->user_data   = $storeHelper['user_data'];
        $userMethod->save();

        $notify[] = ['success', 'Withdraw method updated successfully'];
        return redirect(route(strtolower(userGuard()['type']) . '.withdraw.methods'))->withNotify($notify);
    }

    protected function storeHelper($request, $rules, $isUpdate = false)
    {

        $guard          = userGuard()['guard'];
        $withdrawMethod = WithdrawMethod::where('id', $request->method_id)->whereJsonContains('user_guards', "$guard")->where('status', 1)->firstOrFail();

        if (!$withdrawMethod) {
            $notify[] = ['error', 'Something went wrong!'];
            return back()->withNotify($notify);
        }

        $formData = $withdrawMethod->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        return ['user_data' => $userData, 'rules' => $rules];
    }

    public function editWithdrawMethod($id)
    {

        $pageTitle  = 'Withdraw Method Edit';
        $userMethod = UserWithdrawMethod::myWithdrawMethod()->where('id', $id)->first();

        if (!$userMethod) {
            $notify[] = ['error', 'Withdraw method not found'];
            return back()->withNotify($notify);
        }

        $currencies = Currency::pluck('id', 'currency_code');
        return view('Template::'. strtolower(userGuard()['type']) . '.withdraw.edit_method', compact('pageTitle', 'userMethod', 'currencies'));
    }

    public function withdrawMethodUpdate(Request $request)
    {

        $userMethod = UserWithdrawMethod::where('user_type', userGuard()['type'])->where('user_id', userGuard()['user']->id)->where('id', $request->id)->first();
        if (!$userMethod) {
            $notify[] = ['error', 'Withdraw method not found'];
            return back()->withNotify($notify);
        }

        $rules       = ['name' => 'required'];
        $storeHelper = $this->storeHelper($request, $rules, true);

        $request->validate($storeHelper['rules']);

        $userMethod->name      = $request->name;
        $userMethod->user_id   = userGuard()['user']->id;
        $userMethod->user_type = userGuard()['type'];
        $userMethod->user_data = $storeHelper['user_data'];
        $userMethod->status    = $request->status ? 1 : 0;
        $userMethod->save();

        $notify[] = ['success', 'Withdraw method updated successfully'];
        return back()->withNotify($notify);
    }

    public function withdrawMoney(Request $request)
    {

        $request->validate([
            'method_id'      => 'required',
            'user_method_id' => 'required',
            'amount'         => 'required|numeric',
        ]);

        $getGuard = userGuard();
        $user     = $getGuard['user'];
        $guard    = $getGuard['guard'];

        $method     = WithdrawMethod::where('id', $request->method_id)->where('status', 1)->whereJsonContains('user_guards', "$guard")->firstOrFail();
        $userMethod = UserWithdrawMethod::myWithdrawMethod()->findOrFail($request->user_method_id);

        $currency = Currency::find($userMethod->currency_id);
        if (!$currency) {
            $notify[] = ['error', 'Currency not found'];
            return back()->withNotify($notify);
        }

        $wallet = Wallet::hasCurrency()->where('user_type', userGuard()['type'])->where('user_id', $user->id)->where('currency_id', $currency->id)->first();
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return back()->withNotify($notify);
        }

        if ($method->min_limit / $currency->rate > $request->amount || $method->max_limit / $currency->rate < $request->amount) {
            $notify[] = ['error', 'Please follow the limits'];
            return back()->withNotify($notify);
        }

        if ($request->amount > $wallet->balance) {
            $notify[] = ['error', 'You do not have sufficient balance for withdraw.'];
            return back()->withNotify($notify);
        }

        $charge      = ($method->fixed_charge / $currency->rate) + ($request->amount * $method->percent_charge / 100);
        $finalAmount = $request->amount - $charge;

        $withdraw                       = new Withdrawal();
        $withdraw->method_id            = $method->id;
        $withdraw->user_id              = $user->id;
        $withdraw->user_type            = userGuard()['type'];
        $withdraw->amount               = $request->amount;
        $withdraw->rate                 = $currency->rate;
        $withdraw->currency_id          = $currency->id;
        $withdraw->wallet_id            = $wallet->id;
        $withdraw->currency             = $currency->currency_code;
        $withdraw->charge               = $charge;
        $withdraw->final_amount         = $finalAmount;
        $withdraw->after_charge         = $finalAmount;
        $withdraw->withdraw_information = $userMethod->user_data;
        $withdraw->trx                  = getTrx();
        $withdraw->save();

        session()->put('wtrx', $withdraw->trx);
        return redirect(route(strtolower($getGuard['type']) . '.withdraw.preview'));
    }

    public function withdrawSubmit(Request $request)
    {

        $request->validate(
            [
                'otp_type' => otpType(validation: true),
            ]
        );

        $getGuard = userGuard();

        $userAction            = new UserActionProcess();
        $userAction->user_id   = $getGuard['user']->id;
        $userAction->user_type = $getGuard['type'];
        $userAction->act       = 'withdraw_money';

        $userAction->details = [
            'done_route' => route(strtolower(userGuard()['type']) . '.withdraw.submit.done'),
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function withdrawSubmitDone()
    {

        if (!session('wtrx')) {
            $notify[] = ['error', 'Sorry! Something went wrong'];
            return back()->withNotify($notify);
        }

        $getGuard   = userGuard();
        $userAction = UserAction::where('user_id', $getGuard['user']->id)->where('user_type', $getGuard['type'])->where('id', session('action_id'))->first();

        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route(strtolower($getGuard['type']) . '.withdraw')->withNotify($notify);
        }

        $withdraw = Withdrawal::with('method', strtolower($getGuard['type']))->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id', 'desc')->firstOrFail();

        $wallet = Wallet::checkWallet(['user' => $getGuard['user'], 'type' => $getGuard['type']])->find($withdraw->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return back()->withNotify($notify);
        }

        if ($withdraw->amount > $wallet->balance) {
            $notify[] = ['error', 'You do not have sufficient balance for withdraw.'];
            return to_route(strtolower($getGuard['type']) . '.withdraw')->withNotify($notify);
        }

        $withdraw->status = 2;
        $withdraw->save();

        $wallet->balance -= $withdraw->amount;
        $wallet->save();

        $transaction                = new Transaction();
        $transaction->user_id       = $withdraw->user_id;
        $transaction->user_type     = $withdraw->user_type;
        $transaction->wallet_id     = $wallet->id;
        $transaction->currency_id   = $withdraw->currency_id;
        $transaction->before_charge = $withdraw->amount;
        $transaction->amount        = $withdraw->amount;
        $transaction->post_balance  = $wallet->balance;
        $transaction->charge        = 0;
        $transaction->charge_type   = '+';
        $transaction->trx_type      = '-';
        $transaction->remark        = 'withdraw';
        $transaction->details       = 'Money withdrawal';
        $transaction->trx           = $withdraw->trx;
        $transaction->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $getGuard['user']->id;
        $adminNotification->user_type = $withdraw->user_type;
        $adminNotification->title     = 'New withdraw request from ' . $getGuard['user']->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        $general = gs();

        notify($getGuard['user'], 'WITHDRAW_REQUEST', [
            'method_name'     => $withdraw->method->name,
            'method_currency' => $wallet->currency->currency_code,
            'method_amount'   => showAmount($withdraw->final_amount, $general->currency, currencyFormat:false),
            'amount'          => showAmount($withdraw->amount, $general->currency, currencyFormat:false),
            'charge'          => showAmount($withdraw->charge, $general->currency, currencyFormat:false),
            'currency'        => $wallet->currency->currency_code,
            'trx'             => $withdraw->trx,
            'post_balance'    => showAmount($wallet->balance, $general->currency, currencyFormat:false),
        ]);

        $notify[] = ['success', 'Withdraw request sent successfully'];
        return to_route(strtolower($getGuard['type']) . '.withdraw.history')->withNotify($notify);
    }
}
