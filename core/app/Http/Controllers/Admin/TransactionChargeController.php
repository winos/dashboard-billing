<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransactionCharge;
use Illuminate\Http\Request;

class TransactionChargeController extends Controller{

    public function manageCharges(){   

        $pageTitle = "Transaction Charges";
        $charges = TransactionCharge::cursor();
        $moneyTransfer =  $charges->where('slug', 'money_transfer')->first();
        $invoiceCharge =  $charges->where('slug', 'invoice_charge')->first();
        $exchangeCharge =  $charges->where('slug', 'exchange_charge')->first();
        $apiCharge =  $charges->where('slug', 'api_charge')->first();
        $voucherCharge =  $charges->where('slug', 'voucher_charge')->first();
        $moneyOutCharge =  $charges->where('slug', 'money_out_charge')->first();
        $moneyInCharge =  $charges->where('slug', 'money_in_charge')->first();
        $paymentCharge =  $charges->where('slug', 'make_payment')->first();

        return view('admin.transaction_charges', compact('pageTitle', 'moneyTransfer', 'invoiceCharge', 'exchangeCharge', 'apiCharge', 'voucherCharge', 'moneyOutCharge', 'moneyInCharge', 'paymentCharge'));
    }

    public function updateCharges(Request $request){
   
        $request->validate([
            'percentage_charge' => 'numeric|between:0,100',
            'fixed_charge' => 'numeric|gte:0',
            'cap'   => 'numeric|gte:-1',
            'min_limit' => 'numeric|gte:0',
            'max_limit' => 'numeric|gt:min_limit',
            'monthly_limit' => 'numeric|gte:-1',
            'daily_limit' => 'numeric|gte:-1',
            'voucher_limit' => 'numeric|gte:-1',
            'agent_commission_fixed' => 'numeric|gte:0',
            'agent_commission_percent' => 'numeric|gte:0',
            'merchant_fixed_charge' => 'numeric|gte:0',
            'merchant_percent_charge' => 'numeric|gte:0',
        ]);

        $charge = TransactionCharge::findOrFail($request->id);

        $charge->daily_request_accept_limit = $request->daily_request_accept_limit;

        $charge->percent_charge = $request->percentage_charge;
        $charge->fixed_charge = $request->fixed_charge;
        $charge->min_limit = $request->min_limit;
        $charge->max_limit = $request->max_limit;
        $charge->cap = $request->cap;
        $charge->agent_commission_fixed = $request->agent_com_fixed;
        $charge->agent_commission_percent = $request->agent_com_percent;
        $charge->merchant_fixed_charge = $request->merchant_fixed_charge;
        $charge->merchant_percent_charge = $request->merchant_percent_charge;
        $charge->monthly_limit = $request->monthly_limit;
        $charge->daily_limit = $request->daily_limit;
        $charge->voucher_limit = $request->voucher_limit;
        $charge->save();

        $notify[]=['success','Charge updated successfully'];
        return back()->withNotify($notify);
    }

}
