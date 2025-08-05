<?php

namespace App\Traits;

use App\Models\ApiPayment;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ApiPaymentProcess
{

    public function __construct()
    {
        parent::__construct();
    }

    public function validation($request)
    {
        return Validator::make($request->all(), [
            'identifier'     => 'required|string|max:20',
            'currency'       => 'required|string|max:4',
            'amount'         => 'required|numeric|gt:0',
            'details'        => 'required|string|max:100',
            'ipn_url'        => 'required|url',
            'cancel_url'     => 'required|url',
            'success_url'    => 'required|url',
            'public_key'     => 'required|string|max:50',
            'site_logo'      => 'nullable|url',
            'checkout_theme' => 'in:dark,light|string|max:5',
            'customer_name'  => 'required|string|max:30',
            'customer_email' => 'required|email|max:30',
        ]);
    }

    public function checkCurrency($currency)
    {
        return Currency::where('currency_code', strtoupper($currency))->where('status', 1)->first();
    }

    public function checkMerchant($public_key)
    {
        return Merchant::where('public_api_key', $public_key)->first();
    }

    public function initiatePayment(Request $request)
    {

        $validator = $this->validation($request);

        if ($validator->fails()) {
            return [
                'error'  => 'yes',
                'errors' => $validator->errors()->all(),
            ];
        }

        if ($request->payment_via) {
            $plugin = Plugin::where('status', 1)->where('plugin_for', $request->payment_via)->first();
            if (!$plugin) {
                return [
                    'error'   => 'true',
                    'message' => 'Plugin is not available.',
                ];
            }
        }

        $currency = $this->checkCurrency($request->currency);
        if (!$currency) {
            return [
                'error'   => 'true',
                'message' => 'Currency not supported.',
            ];
        }

        $merchant = $this->checkMerchant($request->public_key);
        if (!$merchant) {
            return [
                'error'   => 'true',
                'message' => 'Invalid api key.',
            ];
        }

        $data['identifier']     = $request->identifier;
        $data['amount']         = $request->amount;
        $data['details']        = $request->details;
        $data['public_key']     = @$merchant->public_api_key;
        $data['merchant_id']    = @$merchant->id;
        $data['currency_id']    = @$currency->id;
        $data['payer_name']     = @$request->customer_name;
        $data['ip']             = request()->ip();
        $data['trx']            = getTrx();
        $data['ipn_url']        = $request->ipn_url;
        $data['cancel_url']     = $request->cancel_url;
        $data['success_url']    = $request->success_url;
        $data['site_logo']      = $request->site_logo;
        $data['checkout_theme'] = @$request->checkout_theme;
        $data['type']           = $this->paymentType;
        $data['created_at']     = now();
        $apiPayment             = ApiPayment::create($data);
        $data                   = $apiPayment->trx;

        if ($this->paymentType == 'live') {
            $url = route('initiate.payment.auth.view', ['payment_id' => encrypt(json_encode($data))]);
        } else {
            $url = route('test.initiate.payment.auth.view', ['payment_id' => encrypt(json_encode($data))]);
        }

        return [
            "success" => "ok",
            "message" => "Payment Initiated. Redirect to url",
            "url"     => $url,
        ];
    }

    public function getPaymentInfo()
    {

        try {
            $trx = decrypt(session('trx'));
            $trx = str_replace('"', '', $trx);
        } catch (\Exception $e) {
            return [
                'error'   => 'true',
                'message' => 'Invalid transaction request',
            ];
        }

        $apiPayment = ApiPayment::where('trx', $trx)->first();
        if (!$apiPayment || $apiPayment->status == 1 || $apiPayment->status == 2) {
            return [
                'error'   => 'true',
                'message' => 'Invalid transaction request',
            ];
        }

        return $apiPayment;
    }

    public function initiatePaymentAuthView()
    {
        $pageTitle = "Payment Checkout";
        session()->put('trx', request('payment_id'));

        $apiPayment = $this->getPaymentInfo();

        if ($this->paymentType == 'live') {
            $mailCheckRoute = route('payment.check.email');
        } else {
            $mailCheckRoute = route('test.payment.check.email');
        }

        return view('Template::api_payment.checkout', compact('pageTitle', 'apiPayment', 'mailCheckRoute'));
    }

    public function verifyPayment()
    {

        $pageTitle  = "Verify Payment";
        $apiPayment = $this->getPaymentInfo();

        if ($this->paymentType == 'live') {
            $verifyRoute = route('confirm.payment');
        } else {
            $verifyRoute = route('test.confirm.payment');
        }

        return view('Template::api_payment.verify_payment', compact('pageTitle', 'apiPayment', 'verifyRoute'));
    }

    public function notify($type)
    {

        $notify['kyc_unverified'] = [
            'error'   => 'yes',
            'message' => 'You are not KYC verified, please verify your KYC information',
        ];
        $notify['kyc_pending'] = [
            'error'   => 'yes',
            'message' => 'Your documents for KYC verification is under review. Please wait for admin approval',
        ];
        $notify['email_validation'] = [
            'error'   => 'yes',
            'message' => 'Email field is required',
        ];
        $notify['email_check'] = [
            'error'   => 'yes',
            'message' => 'Email not matched',
        ];
        $notify['code_validation'] = [
            'error'   => 'yes',
            'message' => 'Code field is required',
        ];
        $notify['code_not_match'] = [
            'error'   => 'yes',
            'message' => 'Sorry! verification code mismatch',
        ];
        $notify['charge_not_found'] = [
            'error'   => 'yes',
            'message' => 'Sorry! something went wrong',
        ];
        $notify['merchant_not_found'] = [
            'error'   => 'yes',
            'message' => 'Sorry! something went wrong',
        ];
        $notify['user_not_found'] = [
            'error'   => 'yes',
            'message' => 'User account not found',
        ];
        $notify['wallet_not_found'] = [
            'error'   => 'yes',
            'message' => 'User wallet not found',
        ];
        $notify['insuf_balance'] = [
            'error'   => 'yes',
            'message' => 'Sorry! insufficient balance',
        ];
        $notify['email_check_done'] = [
            'error'        => 'no',
            'redirect_url' => $this->paymentType == 'test' ? route('test.payment.verify') : route('payment.verify'),
        ];

        return $notify[$type];
    }

    public function cancelPayment()
    {

        $apiPayment = $this->getPaymentInfo();

        if ($apiPayment->cancel_url) {
            if ($this->paymentType == 'live') {
                $apiPayment->status = 2;
                $apiPayment->save();
            }

            return redirect($apiPayment->cancel_url);
        }

    }
}
