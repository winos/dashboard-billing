<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\UserActionProcess;
use App\Models\RequestMoney;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\User;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class UserOperationController extends Controller
{

    public function checkUser(Request $request)
    {

        $exist['data'] = User::where('username', $request->user)->orWhere('email', $request->user)->first();

        $user = auth()->user();
        if (@$exist['data'] && $user->username == @$exist['data']->username || $user->email == @$exist['data']->email) {
            return response()->json(['own' => 'Can\'t transfer/request to your own']);
        }
        return response($exist);
    }

    public function transfer()
    {

        $pageTitle      = "Transfer Money";
        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();
        $wallets        = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->with('currency')->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();
        return view('Template::user.operations.transfer_money', compact('pageTitle', 'transferCharge', 'wallets'));
    }

    public function transferMoney(Request $request)
    {

        $request->validate([
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'user'      => 'required',
            'otp_type'  => otpType(validation: true),
        ],
            [
                'wallet_id.required' => 'Please select a wallet',
            ]);

        $user = auth()->user();

        if ($user->username == $request->user || $user->email == $request->user) {
            $notify[] = ['error', 'Can\'t transfer balance to your own'];
            return back()->withNotify($notify)->withInput();
        }

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return back()->withNotify($notify)->withInput();
        }

        $rate           = $wallet->currency->rate;
        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->firstOrFail();

        if ($request->amount < currencyConverter($transferCharge->min_limit, $rate) || $request->amount > currencyConverter($transferCharge->max_limit, $rate)) {
            $notify[] = ['error', 'Please follow the transfer limit'];
            return back()->withNotify($notify)->withInput();
        }

        $fixedCharge = currencyConverter($transferCharge->fixed_charge, $rate);
        $totalCharge = chargeCalculator($request->amount, $transferCharge->percent_charge, $fixedCharge);

        $cap = currencyConverter($transferCharge->cap, $rate);
        if ($transferCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }
        if ($wallet->currency->currency_type == Status::FIAT_CURRENCY) {
            $totalAmount = getAmount($request->amount + $totalCharge, 2);
        } else {
            $totalAmount = getAmount($request->amount + $totalCharge, 8);
        }

        if ($transferCharge->daily_limit != -1 && auth()->user()->trxLimit('transfer_money')['daily'] + toBaseCurrency($totalAmount, $rate) >= $transferCharge->daily_limit) {
            $notify[] = ['error', 'Daily transfer limit has been exceeded'];
            return back()->withNotify($notify)->withInput();
        }

        $receiver = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$receiver) {
            $notify[] = ['error', 'Sorry! Receiver not found'];
            return back()->withNotify($notify)->withInput();
        }

        $receiverWallet = Wallet::checkWallet(['user' => $receiver, 'type' => 'USER'])->where('currency_id', $wallet->currency_id)->first();
        if (!$receiverWallet) {
            $receiverWallet = createWallet($wallet->currency, $receiver);
        }

        if ($totalAmount > $wallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in this wallet'];
            return back()->withNotify($notify)->withInput();
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = userGuard()['type'];
        $userAction->act       = 'transfer_money';

        $userAction->details = [
            'wallet_id'   => $wallet->id,
            'amount'      => $request->amount,
            'totalAmount' => $totalAmount,
            'totalCharge' => $totalCharge,
            'receiver_id' => $receiver->id,
            'done_route'  => route('user.transfer.done'),
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function transferMoneyDone()
    {

        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('id', session('action_id'))->first();
        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route('user.transfer')->withNotify($notify)->withInput();
        }

        $details = $userAction->details;

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->find($details->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Wallet not found'];
            return to_route('user.transfer')->withNotify($notify)->withInput();
        }

        $receiver = User::where('id', $details->receiver_id)->first();
        if (!$receiver) {
            $notify[] = ['error', 'Sorry! Receiver not found'];
            return to_route('user.transfer')->withNotify($notify)->withInput();
        }

        $receiverWallet = Wallet::checkWallet(['user' => $receiver, 'type' => 'USER'])->where('currency_id', $wallet->currency_id)->first();
        if (!$receiverWallet) {
            $receiverWallet = createWallet($wallet->currency, $receiver);
        }

        if (@$userAction->details->totalAmount > $wallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in wallet'];
            return to_route('user.transfer')->withNotify($notify)->withInput();
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
        $senderTrx->remark        = 'transfer_money';
        $senderTrx->details       = 'Transfer Money to';
        $senderTrx->receiver_id   = $receiver->id;
        $senderTrx->receiver_type = "USER";
        $senderTrx->trx           = getTrx();
        $senderTrx->save();

        $receiverWallet->balance += $details->amount;
        $receiverWallet->save();

        $receiverTrx                = new Transaction();
        $receiverTrx->user_id       = $receiver->id;
        $receiverTrx->user_type     = 'USER';
        $receiverTrx->wallet_id     = $receiverWallet->id;
        $receiverTrx->currency_id   = $receiverWallet->currency_id;
        $receiverTrx->before_charge = $details->amount;
        $receiverTrx->amount        = $details->amount;
        $receiverTrx->post_balance  = $receiverWallet->balance;
        $receiverTrx->charge        = 0;
        $receiverTrx->charge_type   = '+';
        $receiverTrx->trx_type      = '+';
        $receiverTrx->remark        = 'transfer_money';
        $receiverTrx->details       = 'Received Money From';
        $receiverTrx->receiver_id   = auth()->id();
        $receiverTrx->receiver_type = "USER";
        $receiverTrx->trx           = $senderTrx->trx;
        $receiverTrx->save();

        notify(auth()->user(), 'TRANSFER_MONEY', [
            'amount'        => showAmount($details->totalAmount, $wallet->currency, currencyFormat:false),
            'charge'        => showAmount($details->totalCharge, $wallet->currency, currencyFormat:false),
            'currency_code' => $wallet->currency->currency_code,
            'to_user'       => $receiver->fullname . ' ( ' . $receiver->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($wallet->balance, $wallet->currency, currencyFormat:false),
        ]);

        notify($receiver, 'RECEIVED_MONEY', [
            'amount'        => showAmount($details->amount, $wallet->currency, currencyFormat:false),
            'currency_code' => $receiverWallet->currency->currency_code,
            'from_user'     => auth()->user()->fullname . ' ( ' . auth()->user()->username . ' )',
            'trx'           => $senderTrx->trx,
            'time'          => showDateTime($senderTrx->created_at, 'd/M/Y @h:i a'),
            'balance'       => showAmount($receiverWallet->balance, $wallet->currency, currencyFormat:false),
        ]);

        $notify[] = ['success', 'Money transferred successfully'];
        return to_route('user.transfer')->withNotify($notify);
    }

    public function requestMoney()
    {
        $pageTitle      = "Request Money";
        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();
        $wallets        = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->get();
        return view('Template::user.operations.request_money', compact('pageTitle', 'transferCharge', 'wallets'));
    }

    public function confirmRequest(Request $request)
    {

        $request->validate([
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'user'      => 'required',
        ]);

        if (auth()->user()->username == $request->user || auth()->user()->email == $request->user) {
            $notify[] = ['error', 'Can\'t make request to your own'];
            return back()->withNotify($notify)->withInput();
        }

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            $notify[] = ['error', 'Your wallet not found'];
            return back()->withNotify($notify)->withInput();
        }

        $rate           = $wallet->currency->rate;
        $transferCharge = TransactionCharge::findOrFail($request->charge_id);

        if ($request->amount < currencyConverter($transferCharge->min_limit, $rate) || $request->amount > currencyConverter($transferCharge->max_limit, $rate)) {
            $notify[] = ['error', 'Please follow the request amount limit'];
            return back()->withNotify($notify)->withInput();
        }

        $receiver = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$receiver) {
            $notify[] = ['error', 'Sorry! Request receiver not found'];
            return back()->withNotify($notify)->withInput();
        }

        $rate = $wallet->currency->rate;

        $fixedCharge = currencyConverter($transferCharge->fixed_charge, $rate);
        $totalCharge = chargeCalculator($request->amount, $transferCharge->percent_charge, $fixedCharge);
        $cap         = currencyConverter($transferCharge->cap, $rate);
        if ($transferCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $requestDetail                 = new RequestMoney();
        $requestDetail->wallet_id      = $wallet->id;
        $requestDetail->currency_id    = $wallet->currency->id;
        $requestDetail->charge         = $totalCharge;
        $requestDetail->request_amount = $request->amount;
        $requestDetail->sender_id      = auth()->id();
        $requestDetail->receiver_id    = $receiver->id;
        $requestDetail->note           = $request->note;
        $requestDetail->save();

        notify($receiver, 'REQUEST_MONEY', [
            'amount'        => $request->amount,
            'currency_code' => $wallet->currency->currency_code,
            'requestor'     => auth()->user()->username,
            'time'          => showDateTime($requestDetail->created_at, 'd/M/Y @h:i a'),
            'note'          => $request->note,
        ]);

        $notify[] = ['success', 'Request money successful'];
        return back()->withNotify($notify);
    }

    public function allRequests()
    {

        $pageTitle = "Money Requests To Me";
        $requests  = RequestMoney::where('receiver_id', auth()->id())
            ->where('status', 0)
            ->with(['currency', 'sender'])->whereHas('currency')
            ->whereHas('sender')->latest()
            ->paginate(getPaginate());

        return view('Template::user.operations.money_requests', compact('pageTitle', 'requests'));
    }

    public function requestAccept(Request $request)
    {

        $request->validate(
            [
                'request_id' => 'required|integer',
                'otp_type'   => otpType(validation: true),
            ],

            [
                'request_id.required' => 'Transfer details is required',
            ]
        );

        $requestDetail = RequestMoney::where('receiver_id', auth()->user()->id)->findOrFail($request->request_id);
        $requestor     = User::find($requestDetail->sender_id);

        if (!$requestor) {
            $notify[] = ['error', 'Requestor user not found'];
            return back()->withNotify($notify);
        }

        $userWallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('currency_id', $requestDetail->currency_id)->first();
        if (!$userWallet) {
            $notify[] = ['error', 'Your wallet not found'];
            return back()->withNotify($notify);
        }

        $requestorWallet = Wallet::hasCurrency()->where('user_type', 'USER')->where('user_id', $requestDetail->sender_id)
            ->where('currency_id', $requestDetail->currency_id)
            ->first();

        if (!$requestorWallet) {
            $notify[] = ['error', 'Receiver wallet not found'];
            return back()->withNotify($notify);
        }

        if ($requestDetail->request_amount > $userWallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance to your wallet'];
            return back()->withNotify($notify);
        }

        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->firstOrFail();
        $rate           = $userWallet->currency->rate;

        if ($transferCharge->daily_request_accept_limit != -1 && (auth()->user()->trxLimit('request_money')['daily'] + toBaseCurrency($requestDetail->request_amount, $rate) > $transferCharge->daily_request_accept_limit)) {
            $notify[] = ['error', 'Daily request accept limit has been exceeded'];
            return back()->withNotify($notify)->withInput();
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = userGuard()['type'];
        $userAction->act       = 'request_money_accept';

        $userAction->details = [
            'userWallet_id' => $userWallet->id,
            'amount'        => $requestDetail->request_amount,
            'charge'        => $requestDetail->charge,
            'request_id'    => $requestDetail->id,
            'requestor_id'  => $requestor->id,
            'done_route'    => route('user.request.accept.done'),
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function requestAcceptDone()
    {

        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('id', session('action_id'))->first();
        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route('user.request.money')->withNotify($notify)->withInput();
        }

        $details = $userAction->details;

        $requestor = User::find($details->requestor_id);
        if (!$requestor) {
            $notify[] = ['error', 'Requestor user not found'];
            return to_route('user.request.money')->withNotify($notify)->withInput();
        }

        $userWallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('id', $details->userWallet_id)->first();
        if (!$userWallet) {
            $notify[] = ['error', 'Your wallet not found'];
            return to_route('user.request.money')->withNotify($notify)->withInput();
        }

        $requestorWallet = Wallet::hasCurrency()->where('user_type', 'USER')->where('user_id', $requestor->id)->where('currency_id', $userWallet->currency_id)->first();
        if (!$requestorWallet) {
            $notify[] = ['error', 'Receiver wallet not found'];
            return to_route('user.request.money')->withNotify($notify)->withInput();
        }

        $requestDetail = RequestMoney::where('receiver_id', auth()->user()->id)->findOrFail($details->request_id);

        if (@$userAction->details->totalAmount > $userWallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance in wallet'];
            return to_route('user.request.money')->withNotify($notify)->withInput();
        }

        $userWallet->balance -= $details->amount;
        $userWallet->save();

        $userTrx                = new Transaction();
        $userTrx->user_id       = auth()->id();
        $userTrx->user_type     = 'USER';
        $userTrx->wallet_id     = $userWallet->id;
        $userTrx->currency_id   = $userWallet->currency_id;
        $userTrx->before_charge = $details->amount;
        $userTrx->amount        = $details->amount;
        $userTrx->post_balance  = $userWallet->balance;
        $userTrx->charge        = 0;
        $userTrx->trx_type      = '-';
        $userTrx->charge_type   = '+';
        $userTrx->remark        = 'request_money';
        $userTrx->details       = 'Accept money request from';
        $userTrx->receiver_id   = $requestor->id;
        $userTrx->receiver_type = "USER";
        $userTrx->trx           = getTrx();
        $userTrx->save();

        $afterCharge = ($details->amount - $details->charge);
        $requestorWallet->balance += $afterCharge;
        $requestorWallet->save();

        $requestorTrx                = new Transaction();
        $requestorTrx->user_id       = $requestor->id;
        $requestorTrx->user_type     = 'USER';
        $requestorTrx->wallet_id     = $requestorWallet->id;
        $requestorTrx->currency_id   = $requestorWallet->currency_id;
        $requestorTrx->before_charge = $details->amount;
        $requestorTrx->amount        = $afterCharge;
        $requestorTrx->post_balance  = $requestorWallet->balance;
        $requestorTrx->charge        = $details->charge;
        $requestorTrx->charge_type   = '-';
        $requestorTrx->trx_type      = '+';
        $requestorTrx->remark        = 'request_money';
        $requestorTrx->details       = 'Money request has been accepted from';
        $requestorTrx->receiver_id   = auth()->id();
        $requestorTrx->receiver_type = "USER";
        $requestorTrx->trx           = $userTrx->trx;
        $requestorTrx->save();

        notify($requestor, 'ACCEPT_REQUEST_MONEY_REQUESTOR', [
            'amount'        => showAmount($details->amount, $userWallet->currency, currencyFormat:false),
            'currency_code' => $userWallet->currency->currency_code,
            'to_requested'  => auth()->user()->username,
            'charge'        => showAmount($details->charge, $userWallet->currency, currencyFormat:false),
            'balance'       => showAmount($requestorWallet->balance, $userWallet->currency, currencyFormat:false),
            'trx'           => $userTrx->trx,
            'time'          => showDateTime($userTrx->created_at, 'd/M/Y @h:i a'),
        ]);

        notify(auth()->user(), 'ACCEPT_REQUEST_MONEY', [
            'amount'        => showAmount($details->amount, $userWallet->currency, currencyFormat:false),
            'currency_code' => $userWallet->currency->currency_code,
            'requestor'     => $requestor->username,
            'balance'       => showAmount($userWallet->balance, $userWallet->currency, currencyFormat:false),
            'trx'           => $userTrx->trx,
            'time'          => showDateTime($userTrx->created_at, 'd/M/Y @h:i a'),
        ]);

        $requestDetail->status = 2;
        $requestDetail->save();

        $notify[] = ['success', 'Money request has been accepted'];
        return to_route('user.requests')->withNotify($notify);
    }

    public function requestReject(Request $request)
    {
        $request->validate(
            [
                'request_id' => 'required|integer',
            ],

            [
                'request_id.required' => 'Transfer details is required',
            ]
        );

        $transfer         = RequestMoney::where('receiver_id', auth()->user()->id)->findOrFail($request->request_id);
        $transfer->status = 1;
        $transfer->save();

        $notify[] = ['success', 'Request has been rejected'];
        return back()->withNotify($notify);
    }

    public function requestedHistory()
    {
        $pageTitle = "My Requested History";
        $requests  = RequestMoney::where('sender_id', auth()->id())
            ->with(['currency', 'receiver'])->whereHas('currency')
            ->orderBy('id', 'DESC')->paginate(getPaginate());

        return view('Template::user.operations.my_requested_history', compact('pageTitle', 'requests'));
    }

}
