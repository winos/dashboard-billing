<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Lib\Api\UserActionProcess;
use App\Models\RequestMoney;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\User;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserOperationController extends Controller
{

    public function checkUser(Request $request)
    {

        $findUser = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$findUser) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['User not found']],
            ]);
        }

        $user = auth()->user();
        if (@$findUser && $user->username == @$findUser->username || $user->email == @$findUser->email) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Can\'t transfer/request to your own']],
            ]);
        }

        return response()->json([
            'remark'  => 'check_user',
            'status'  => 'success',
            'message' => ['success' => ['Check User']],
            'data'    => [
                'agent' => $findUser,
            ],
        ]);
    }

    public function transfer()
    {

        $notify[]       = "Transfer Money";
        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();

        $wallets = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->with('currency')->where('balance', '>', 0)->orderBy('balance', 'DESC')->get();
        $wallets = $this->withTransferLimit($wallets, $transferCharge);

        return response()->json([
            'remark'  => 'money_out',
            'status'  => 'success',
            'message' => ['success' => $notify],

            'data'    => [
                'otp_type'        => otpType(),
                'wallets'         => $wallets,
                'transfer_charge' => $transferCharge,
            ],
        ]);
    }

    public function withTransferLimit($wallets, $moneyOutCharge)
    {
        foreach ($wallets ?? [] as $wallet) {

            $rate = $wallet->currency->rate;

            $min = $moneyOutCharge->min_limit / $rate;
            $max = $moneyOutCharge->max_limit / $rate;

            $wallet->currency->transfer_min_limit = $min;
            $wallet->currency->transfer_max_limit = $max;
        }

        return $wallets;
    }

    public function transferMoney(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'user'      => 'required',
            'otp_type'  => otpType(validation: true),
        ],
            [
                'wallet_id.required' => 'Please select a wallet',
            ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($user->username == $request->user || $user->email == $request->user) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Can\'t transfer balance to your own']],
            ]);
        }

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Wallet not found']],
            ]);
        }

        $rate = $wallet->currency->rate;

        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();
        if (!$transferCharge) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry, Transaction charge not found']],
            ]);
        }

        if ($request->amount < currencyConverter($transferCharge->min_limit, $rate) || $request->amount > currencyConverter($transferCharge->max_limit, $rate)) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Please follow the transfer limit']],
            ]);
        }

        $fixedCharge = currencyConverter($transferCharge->fixed_charge, $rate);
        $totalCharge = chargeCalculator($request->amount, $transferCharge->percent_charge, $fixedCharge);

        $cap = currencyConverter($transferCharge->cap, $rate);
        if ($transferCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }
        if ($wallet->currency->currency_type == 1) {
            $totalAmount = getAmount($request->amount + $totalCharge, 2);
        } else {
            $totalAmount = getAmount($request->amount + $totalCharge, 8);
        }

        if ($transferCharge->daily_limit != -1 && auth()->user()->trxLimit('transfer_money')['daily'] + toBaseCurrency($totalAmount, $rate) >= $transferCharge->daily_limit) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Daily transfer limit has been exceeded']],
            ]);
        }

        $receiver = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$receiver) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Receiver not found']],
            ]);
        }

        $receiverWallet = Wallet::checkWallet(['user' => $receiver, 'type' => 'USER'])->where('currency_id', $wallet->currency_id)->first();
        if (!$receiverWallet) {
            $receiverWallet = createWallet($wallet->currency, $receiver);
        }

        if ($totalAmount > $wallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance in this wallet']],
            ]);
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = 'USER';
        $userAction->act       = 'transfer_money';

        $userAction->details = [
            'wallet_id'   => $wallet->id,
            'amount'      => $request->amount,
            'totalAmount' => $totalAmount,
            'totalCharge' => $totalCharge,
            'receiver_id' => $receiver->id,
            'done_route'  => 'api.transfer.done',
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

    public function transferMoneyDone($actionId)
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

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->find($details->wallet_id);
        if (!$wallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Wallet not found']],
            ]);
        }

        $receiver = User::where('id', $details->receiver_id)->first();
        if (!$receiver) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Receiver not found']],
            ]);
        }

        $receiverWallet = Wallet::checkWallet(['user' => $receiver, 'type' => 'USER'])->where('currency_id', $wallet->currency_id)->first();
        if (!$receiverWallet) {
            $receiverWallet = createWallet($wallet->currency, $receiver);
        }

        if (@$userAction->details->totalAmount > $wallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance in wallet']],
            ]);
        }

        $userAction->used = 1;
        $userAction->save();

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

        return response()->json([
            'remark'  => 'money_transfer_done',
            'status'  => 'success',
            'message' => ['success' => ['Money transferred successfully']],
        ]);
    }

    public function requestMoney()
    {
        $notify[]       = "Request Money";
        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();

        $wallets = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->get();
        $wallets = $this->withMoneyRequestLimit($wallets, $transferCharge);

        return response()->json([
            'remark'  => 'request_money',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'otp_type'        => otpType(),
                'wallets'         => $wallets,
                'transfer_charge' => $transferCharge,
            ],
        ]);
    }

    public function withMoneyRequestLimit($wallets, $moneyOutCharge)
    {
        foreach ($wallets ?? [] as $wallet) {

            $rate = $wallet->currency->rate;

            $min = showAmount($moneyOutCharge->min_limit / $rate, $wallet->currency, currencyFormat:false);
            $max = showAmount($moneyOutCharge->max_limit / $rate, $wallet->currency, currencyFormat:false);

            $wallet->currency->money_request_limit = "$min - $max " . $wallet->currency->currency_code;
        }

        return $wallets;
    }

    public function confirmRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|integer',
            'amount'    => 'required|gt:0',
            'user'      => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        if (auth()->user()->username == $request->user || auth()->user()->email == $request->user) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Can\'t make request to your own']],
            ]);
        }

        $wallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->find($request->wallet_id);
        if (!$wallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Your wallet not found']],
            ]);
        }

        $rate = $wallet->currency->rate;

        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();
        if (!$transferCharge) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry, Transaction charge not found']],
            ]);
        }

        if ($request->amount < currencyConverter($transferCharge->min_limit, $rate) || $request->amount > currencyConverter($transferCharge->max_limit, $rate)) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Please follow the request amount limit']],
            ]);
        }

        $receiver = User::where('username', $request->user)->orWhere('email', $request->user)->first();
        if (!$receiver) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Request receiver not found']],
            ]);
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

        return response()->json([
            'remark'  => 'money_request_done',
            'status'  => 'success',
            'message' => ['success' => ['Request money successfully']],
        ]);
    }

    public function allRequests()
    {

        $notify[] = "Money Requests To Me";
        $requests = RequestMoney::where('receiver_id', auth()->id())->where('status', 0)->with(['currency', 'sender'])->whereHas('currency')->whereHas('sender')->apiQuery();

        return response()->json([
            'remark'  => 'money_request_to_me',
            'status'  => 'success',
            'message' => ['success' => ['Money requests to me']],
            'data'    => [
                'otp_type' => otpType(),
                'requests' => $requests,
            ],
        ]);

    }

    public function requestAccept(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'request_id' => 'required|integer',
                'otp_type'   => otpType(validation: true),
            ],
            [
                'request_id.required' => 'Transfer details is required',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $requestDetail = RequestMoney::where('receiver_id', auth()->user()->id)->find($request->request_id);
        if (!$requestDetail) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Invalid request']],
            ]);
        }

        $requestor = User::find($requestDetail->sender_id);
        if (!$requestor) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Requestor user not found']],
            ]);
        }

        $userWallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('currency_id', $requestDetail->currency_id)->first();
        if (!$userWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Your wallet not found']],
            ]);
        }

        $requestorWallet = Wallet::hasCurrency()->where('user_type', 'USER')->where('user_id', $requestDetail->sender_id)
            ->where('currency_id', $requestDetail->currency_id)
            ->first();
        if (!$requestorWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Receiver wallet not found']],
            ]);
        }

        if ($requestDetail->request_amount > $userWallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance to your wallet']],
            ]);
        }

        $transferCharge = TransactionCharge::where('slug', 'money_transfer')->first();
        if (!$transferCharge) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry, Transaction charge not found']],
            ]);
        }

        $rate = $userWallet->currency->rate;

        if ($transferCharge->daily_request_accept_limit != -1 && (auth()->user()->trxLimit('request_money')['daily'] + toBaseCurrency($requestDetail->request_amount, $rate) > $transferCharge->daily_request_accept_limit)) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Daily request accept limit has been exceeded']],
            ]);
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = 'USER';
        $userAction->act       = 'request_money_accept';

        $userAction->details = [
            'userWallet_id' => $userWallet->id,
            'amount'        => $requestDetail->request_amount,
            'charge'        => $requestDetail->charge,
            'request_id'    => $requestDetail->id,
            'requestor_id'  => $requestor->id,
            'done_route'    => 'api.request.accept.done',
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

    public function requestAcceptDone($actionId)
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

        $requestor = User::find($details->requestor_id);
        if (!$requestor) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Requestor user not found']],
            ]);
        }

        $userWallet = Wallet::checkWallet(['user' => auth()->user(), 'type' => 'USER'])->where('id', $details->userWallet_id)->first();
        if (!$userWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Your wallet not found']],
            ]);
        }

        $requestorWallet = Wallet::hasCurrency()->where('user_type', 'USER')->where('user_id', $requestor->id)->where('currency_id', $userWallet->currency_id)->first();
        if (!$requestorWallet) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Receiver wallet not found']],
            ]);
        }

        $requestDetail = RequestMoney::where('receiver_id', auth()->user()->id)->findOrFail($details->request_id);

        if (@$userAction->details->totalAmount > $userWallet->balance) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Insufficient balance in wallet']],
            ]);
        }

        $userAction->used = 1;
        $userAction->save();

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

        $requestDetail->status = 1;
        $requestDetail->save();

        return response()->json([
            'remark'  => 'request_accept_out_done',
            'status'  => 'success',
            'message' => ['success' => ['Money request has been accepted']],
        ]);
    }

    public function requestReject(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'request_id' => 'required|integer',
            ],

            [
                'request_id.required' => 'Transfer details is required',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $transfer = RequestMoney::where('receiver_id', auth()->user()->id)->find($request->request_id);
        if (!$transfer) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry, Invalid request']],
            ]);
        }

        $transfer->status = 1;
        $transfer->save();

        return response()->json([
            'status'  => 'success',
            'message' => ['success' => ['Request has been rejected']],
        ]);
    }

    public function requestedHistory()
    {

        $notify[] = "My Requested History";
        $requests = RequestMoney::where('sender_id', auth()->id())
            ->with(['currency', 'receiver'])->whereHas('currency')
            ->orderBy('id', 'DESC')->apiQuery();

        return response()->json([
            'remark'  => 'my_requested_history',
            'status'  => 'success',
            'message' => ['success' => ['My Requested History']],
            'data'    => [
                'requests' => $requests,
            ],
        ]);

    }

}
