<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\Currency;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use Common;

    public function methods()
    {
        $wallets = $this->gatewayWithLimit(auth()->user()->wallets);

        $notify[] = 'Payment Methods';
        return response()->json([
            'remark'  => 'deposit_methods',
            'message' => ['success' => $notify],
            'data'    => [
                'wallets' => $wallets,
            ],
        ]);
    }

    public function gatewayWithLimit($wallets)
    {
        foreach ($wallets ?? [] as $wallet) {

            $wallet->currency->gateways = $wallet->gateways();

            foreach ($wallet->currency->gateways ?? [] as $gateway) {
                $rate = $wallet->currency->rate;

                $min = $gateway->min_amount / $rate;
                $max = $gateway->max_amount / $rate;

                $gateway->deposit_min_limit = $min;
                $gateway->deposit_max_limit = $max;
            }
        }

        return $wallets;
    }

    public function depositInsert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'      => 'required|numeric|gt:0',
            'method_code' => 'required',
            'wallet_id'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user     = auth()->user();
        $userType = $this->guard()['user_type'];

        $wallet = Wallet::where('user_id', $user->id)->where('user_type', $userType)->where('id', $request->wallet_id)->first();

        if (!$wallet) {
            $notify[] = 'Invalid wallet';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $currency = Currency::enable()->where('currency_code', $wallet->currency_code)->first();

        if (!$currency) {
            $notify[] = 'Invalid gateway';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->method_code)->where('currency', $currency->currency_code)->first();

        if (!$gate) {
            $notify[] = 'Invalid gateway';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        if ($gate->min_amount / $currency->rate > $request->amount || $gate->max_amount / $currency->rate < $request->amount) {
            $notify[] = 'Please follow deposit limit';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $charge      = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $payable     = $request->amount + $charge;
        $finalAmount = $payable;

        $data          = new Deposit();
        $data->from_api = Status::YES;
        $data->user_id = $user->id;

        $data->user_type   = $userType;
        $data->wallet_id   = $request->wallet_id;
        $data->currency_id = $currency->id;

        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $request->amount;
        $data->charge          = $charge;
        $data->rate            = 1;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->success_url     = urlPath(strToLower($data->user_type) . '.deposit.history');
        $data->failed_url      = urlPath(strToLower($data->user_type) . '.deposit.history');
        $data->trx             = getTrx();
        $data->save();

        $notify[] = 'Deposit inserted';
        return response()->json([
            'remark'  => 'deposit_inserted',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'deposit'      => $data,
                'redirect_url' => route('deposit.app.confirm', encrypt($data->id)),
            ],
        ]);
    }
}
