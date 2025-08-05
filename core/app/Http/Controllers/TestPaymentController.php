<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiPaymentProcess;
use App\Models\Merchant;
use App\Models\TransactionCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Lib\CurlRequest;

class TestPaymentController extends Controller{

    use ApiPaymentProcess;
 
    protected $paymentType = 'test';

    public function __construct(){
        parent::__construct();
    } 

    public function checkEmail(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required'
        ]);

        if($validator->fails()) {
            return $this->notify('email_validation');
        }

        if($request->email != 'test_mode@mail.com'){
            return $this->notify('email_check');
        }

        return $this->notify('email_check_done');
    }

    public function verifyPaymentConfirm(Request $request){

        $validator = Validator::make($request->all(),[
            'code'      => 'required',
        ]);

        if($validator->fails()) {
            return $this->notify('code_validation');
        }

        $apiPayment = $this->getPaymentInfo();
    
        if($request->code != '222666'){
            return $this->notify('code_not_match');
        }
        
        $paymentCharge = TransactionCharge::where('slug','api_charge')->first();
        if(!$paymentCharge){
            return $this->notify('charge_not_found');
        }

        $rate  = 1;
        $fixedCharge    = currencyConverter($paymentCharge->fixed_charge,$rate);
        $totalCharge = chargeCalculator($apiPayment->amount,$paymentCharge->percent_charge,$fixedCharge);

        $customKey = $apiPayment->amount.$apiPayment->identifier;

        $merchant = Merchant::find($apiPayment->merchant_id);
        if(!$merchant){ 
            return $this->notify('merchant_not_found');
        }

        $apiPayment->status = 1;
        $apiPayment->save();

        $res = CurlRequest::curlPostContent($apiPayment->ipn_url, [
            'status'     => 'success',
            'signature' => strtoupper(hash_hmac('sha256', $customKey , $merchant->secret_api_key)),
            'identifier' => $apiPayment->identifier,
            'data' => [
                'payment_trx' =>  $apiPayment->trx,
                'amount'      => $apiPayment->amount,
                'account_holder'   => @$apiPayment->payer_name,
                'payment_type'   => 'hosted',
                'payment_timestamp' => $apiPayment->created_at,
                'charge' => $totalCharge,
                'currency' => [
                    'code'   => @$apiPayment->currency->currency_code,
                    'symbol' => @$apiPayment->currency->currency_symbol,
                ]
            ],
        ]);

        return [
            'error'=>'no',
            'redirect_url'=>$apiPayment->success_url
        ];

    }
    
}
