<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Invoice;
use App\Models\Currency;
use App\Constants\Status;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller{

    public function invoices(){ 
        $notify[] = "All Invoice";
        $invoices = Invoice::where('user_id', auth()->id())->where('user_type', 'USER')->orderBy('pay_status', 'DESC')->with(['items', 'currency'])->whereHas('currency')->apiQuery();

        return response()->json([
            'remark'=>'all_invoice',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'invoices'=>$invoices,
            ]
        ]);

        return view('Template::user.invoice.list', compact('pageTitle', 'invoices'));
    }

    public function createInvoice(){ 
        $notify[] = "Create Invoice";
        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();
        $currencies = Currency::enable()->get();

        return response()->json([
            'remark'=>'create_invoice',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'invoice_charge'=>$invoiceCharge,
                'currencies'=>$currencies,
            ]
        ]);
    }

    public function createInvoiceConfirm(Request $request){
       
        $validator = Validator::make($request->all(), [
            'invoice_to' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'item_name' => 'required',
            'item_name.*' => 'required',
            'amount' => 'required',
            'amount.*' => 'required|numeric|gt:0',
            'currency_id' => 'required|integer'
        ], 
        [
            'item_name.*.required' => 'Item name fields required',
            'amount.*.required' => 'Amount fields required',
            'amount.*.gt' => 'Amount fields must be greater than 0',
            'amount.*.numeric' => 'Amount fields value should be numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }
   
        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();
        if(!$invoiceCharge){
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry, Transaction charge not found']],
            ]);
        }

        $currency = Currency::enable()->find($request->currency_id);
        if (!$currency) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Currency not found']],
            ]);
        }

        $rate = $currency->rate;
        $initialAmount = array_sum($request->amount);

        if ($currency->currency_type == Status::FIAT_CURRENCY
        ) {
            $cap = getAmount(currencyConverter($invoiceCharge->cap, $rate), 2);
            $fixedCharge = getAmount(currencyConverter($invoiceCharge->fixed_charge, $rate), 2);
        }
        else{
            $cap = getAmount($invoiceCharge->cap / $rate, 8);
            $fixedCharge = getAmount(currencyConverter($invoiceCharge->fixed_charge, $rate), 8);
        }

        $totalCharge = chargeCalculator($initialAmount, $invoiceCharge->percent_charge, $fixedCharge);

        if ($invoiceCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $getAmount = $initialAmount - $totalCharge;

        $invoice = new Invoice();
        $invoice->user_id = auth()->id();
        $invoice->user_type = 'USER';
        $invoice->invoice_num = getTrx(12);
        $invoice->invoice_to = $request->invoice_to;
        $invoice->email = $request->email;
        $invoice->address = $request->address;
        $invoice->currency_id = $request->currency_id;
        $invoice->charge = $totalCharge;
        $invoice->total_amount = $initialAmount;
        $invoice->get_amount = $getAmount;
        $invoice->pay_status = 0;
        $invoice->status = 0;
        $invoice->save();

        $items = array_combine($request->item_name, $request->amount);

        foreach ($items as $itemName => $itemAmount) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_name     = $itemName;
            $invoiceItem->amount     = $itemAmount;
            $invoiceItem->save();
        }
  
        return response()->json([
            'remark'=>'create_invoice_done',
            'status'=>'success',
            'message'=>['success'=>['Invoice created successfully']],
        ]);
    } 

    public function editInvoice($invoiceNum){
    
        $notify[] = "Update Invoice"; 
        $invoice = Invoice::where('invoice_num', $invoiceNum)->where('user_id', auth()->id())->where('user_type', 'USER')->first();
        if (!$invoice) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! invoice not found']],
            ]);
        }

        $currencies = Currency::get();
        $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();

        return response()->json([
            'remark'=>'check_agent',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'invoice'=>$invoice,
                'invoice_items'=>$invoiceItems,
                'currencies'=>$currencies,
                'invoice_charge'=>$invoiceCharge,
            ]
        ]);
    }

    public function discardInvoice($id){
 
        $user = auth()->user();
        $invoice = Invoice::where('user_id', $user->id)->where('user_type', 'USER')->where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Invoice not found']],
            ]);
        }

        $invoice->status = 2;
        $invoice->save();

        return response()->json([
            'remark'=>'discard_invoice',
            'status'=>'success',
            'message'=>['success'=>['Invoice has been discarded']],
        ]);
    }

    public function publishInvoice($id){

        $user = auth()->user();
        $invoice = Invoice::where('user_id', $user->id)->where('user_type', 'USER')->where('id', $id)->first();
        if (!$invoice) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Invoice not found']],
            ]);
        }

        $invoice->status = 1;
        $invoice->save();

        return response()->json([
            'remark'=>'publish_invoice',
            'status'=>'success',
            'message'=>['success'=>['Invoice published successfully']],
        ]);

    }

    public function sendInvoiceToMail($id){
     
        $user = auth()->user();
        $invoice = Invoice::where('user_id', $user->id)->where('user_type', 'USER')->where('id', $id)->first();
        if (!$invoice) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Invoice not found']],
            ]);
        }

        try {
            notify($user,'SEND_INVOICE_MAIL',
                [
                    'url' => route('invoice.payment', encrypt($invoice->invoice_num))
                ],
                ['email']
            );
        } catch (\Exception $ex) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Mail can not send right now. Try again later']],
            ]);
        }

        return response()->json([
            'remark'=>'invoice_email_sent',
            'status'=>'success',
            'message'=>['success'=>['Invoice sent to email successfully']],
        ]);
    }

    public function updateInvoice(Request $request){
        
        $validator = Validator::make($request->all(), [
            'invoice_to' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'item_name' => 'required',
            'item_name.*' => 'required',
            'amount' => 'required',
            'amount.*' => 'required|numeric|gt:0',
            'currency_id' => 'required|integer',
            'invoice_id' => 'required',
        ], 
        [
            'item_name.*.required' => 'Item name fields required',
            'amount.*.required' => 'Amount fields required',
            'amount.*.gt' => 'Amount fields must be greater than 0',
            'amount.*.numeric' => 'Amount fields value should be numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }
    
        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();
        if(!$invoiceCharge){
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry, Transaction charge not found']],
            ]); 
        }

        $currency = Currency::find($request->currency_id);
        if (!$currency) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Sorry! Currency not found']],
            ]);
        }

        $rate = $currency->rate;
        $initialAmount = array_sum($request->amount);
        $fixedCharge = currencyConverter($invoiceCharge->fixed_charge, $rate);

        if ($currency->currency_type == Status::FIAT_CURRENCY) {
            $cap = getAmount(currencyConverter($invoiceCharge->cap, $rate), 2);
            $presetion = 2;
        } else {
            $cap = getAmount(currencyConverter($invoiceCharge->cap, $rate), 8);
            $presetion = 8;
        }

        $totalCharge = getAmount(chargeCalculator($initialAmount, $invoiceCharge->percent_charge, $fixedCharge), $presetion);

        if ($totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $getAmount = $initialAmount - $totalCharge;

        $invoice = Invoice::findOrFail($request->invoice_id);
        if(!$invoice){
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>['Invalid requrest']],
            ]);
        }

        $invoice->user_id = auth()->id();
        $invoice->user_type = 'USER';
        $invoice->invoice_to = $request->invoice_to;
        $invoice->email = $request->email;
        $invoice->address = $request->address;
        $invoice->currency_id = $request->currency_id;
        $invoice->charge = $totalCharge;
        $invoice->total_amount = $initialAmount;
        $invoice->get_amount = $getAmount;
        $invoice->save();
        
        InvoiceItem::where('invoice_id', $invoice->id)->delete();
        $items = array_combine($request->item_name, $request->amount);

        foreach ($items as $itemName => $itemAmount) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_name     = $itemName;
            $invoiceItem->amount     = $itemAmount;
            $invoiceItem->save();
        }

        return response()->json([
            'remark'=>'update_invoice_done',
            'status'=>'success',
            'message'=>['success'=>['Invoice update successfully']],
        ]);
    }
}
