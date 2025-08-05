<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\UserActionProcess;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\UserAction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

    public function invoices()
    {
        $pageTitle = "Invoices";
        $invoices  = Invoice::where('user_id', auth()->id())->where('user_type', 'USER')->orderBy('pay_status', 'DESC')
            ->with(['items', 'currency'])->whereHas('currency')->latest()
            ->paginate(getPaginate());

        return view('Template::user.invoice.list', compact('pageTitle', 'invoices'));
    }

    public function createInvoice()
    {
        $pageTitle     = "Create Invoice";
        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();
        $currencies    = Currency::enable()->get();
        return view('Template::user.invoice.create', compact('pageTitle', 'invoiceCharge', 'currencies'));
    }

    public function createInvoiceConfirm(Request $request)
    {

        $request->validate(
            [
                'invoice_to'  => 'required',
                'email'       => 'required|email',
                'address'     => 'required',
                'item_name'   => 'required',
                'item_name.*' => 'required',
                'amount'      => 'required',
                'amount.*'    => 'required|numeric|gt:0',
                'currency_id' => 'required|integer',
            ],
            [
                'item_name.*.required' => 'Item name fields required',
                'amount.*.required'    => 'Amount fields required',
                'amount.*.gt'          => 'Amount fields must be greater than 0',
                'amount.*.numeric'     => 'Amount fields value should be numeric',
            ]
        );

        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();
        $currency      = Currency::enable()->find($request->currency_id);

        if (!$currency) {
            $notify[] = ['error', 'Sorry! Currency not found'];
            return back()->withNotify($notify);
        }

        $rate          = $currency->rate;
        $initialAmount = array_sum($request->amount);

        if ($currency->currency_type == 1) {
            $cap         = getAmount(currencyConverter($invoiceCharge->cap, $rate), 2);
            $fixedCharge = getAmount(currencyConverter($invoiceCharge->fixed_charge, $rate), 2);
        } else {
            $cap         = getAmount($invoiceCharge->cap / $rate, 8);
            $fixedCharge = getAmount(currencyConverter($invoiceCharge->fixed_charge, $rate), 8);
        }

        $totalCharge = chargeCalculator($initialAmount, $invoiceCharge->percent_charge, $fixedCharge);

        if ($invoiceCharge->cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $getAmount = $initialAmount - $totalCharge;

        $invoice               = new Invoice();
        $invoice->user_id      = auth()->id();
        $invoice->user_type    = 'USER';
        $invoice->invoice_num  = getTrx(12);
        $invoice->invoice_to   = $request->invoice_to;
        $invoice->email        = $request->email;
        $invoice->address      = $request->address;
        $invoice->currency_id  = $request->currency_id;
        $invoice->charge       = $totalCharge;
        $invoice->total_amount = $initialAmount;
        $invoice->get_amount   = $getAmount;
        $invoice->pay_status   = 0;
        $invoice->status       = 0;
        $invoice->save();

        $items = array_combine($request->item_name, $request->amount);

        foreach ($items as $itemName => $itemAmount) {
            $invoiceItem             = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_name  = $itemName;
            $invoiceItem->amount     = $itemAmount;
            $invoiceItem->save();
        }

        $notify[] = ['success', 'Invoice created successfully'];
        return to_route('user.invoice.all')->withNotify($notify);
    }

    public function editInvoice($invoiceNum)
    {

        $pageTitle = "Update Invoice";
        $invoice   = Invoice::where('invoice_num', $invoiceNum)->where('user_id', auth()->id())->where('user_type', 'USER')->first();

        if (!$invoice) {
            $notify[] = ['error', 'Sorry! invoice not found'];

            return back()->withNotify($notify);
        }

        $currencies    = Currency::get();
        $invoiceItems  = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();

        return view('Template::user.invoice.update', compact('pageTitle', 'invoice', 'invoiceItems', 'invoiceCharge', 'currencies'));
    }

    public function discardInvoice($invoiceNum)
    {

        $invNum  = decrypt($invoiceNum);
        $user    = auth()->user();
        $invoice = Invoice::where('user_id', $user->id)->where('user_type', 'USER')->where('invoice_num', $invNum)->first();

        if (!$invoice) {
            $notify[] = ['error', 'Invoice not found'];
            return back()->withNotify($notify);
        }

        $invoice->status = 2;
        $invoice->save();

        $notify[] = ['success', 'Invoice has been discarded'];
        return to_route('user.invoice.all')->withNotify($notify);
    }

    public function publishInvoice($invoiceNum)
    {

        $invNum  = decrypt($invoiceNum);
        $user    = auth()->user();
        $invoice = Invoice::where('user_id', $user->id)->where('user_type', 'USER')->where('invoice_num', $invNum)->first();

        if (!$invoice) {
            $notify[] = ['error', 'Invoice not found'];
            return back()->withNotify($notify);
        }

        $invoice->status = 1;
        $invoice->save();

        $notify[] = ['success', 'Invoice published successfully'];
        return to_route('user.invoice.all')->withNotify($notify);
    }

    public function invoicePaymentConfirm(Request $request, $invoiceNum)
    {

        $request->validate(
            [
                'otp_type' => otpType(validation: true),
            ],
        );

        $invNum  = decrypt($invoiceNum);
        $invoice = Invoice::where('invoice_num', $invNum)->where('pay_status', 0)->first();

        if (!$invoice) {
            $notify[] = ['error', 'Invoice not found'];
            return to_route('home')->withNotify($notify);
        }

        $user       = userGuard()['user'];
        $currency   = Currency::where('status', 1)->findOrFail($invoice->currency_id);
        $userWallet = Wallet::checkWallet(['user' => $user, 'type' => userGuard()['type']])->where('currency_id', $currency->id)->firstOrFail();

        if ($invoice->total_amount > $userWallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance'];
            return to_route('user.home')->withNotify($notify);
        }

        $userAction            = new UserActionProcess();
        $userAction->user_id   = auth()->user()->id;
        $userAction->user_type = userGuard()['type'];
        $userAction->act       = 'payment_invoice';

        $userAction->details = [
            'invoice_id'  => $invNum,
            'wallet_id'   => $userWallet->id,
            'currency_id' => $currency->id,
            'amount'      => $invoice->total_amount,
            'done_route'  => route('user.invoice.payment.confirm.done'),
        ];

        if (count(otpType())) {
            $userAction->type = $request->otp_type;
        }
        $userAction->submit();

        return redirect($userAction->next_route);
    }

    public function invoicePaymentConfirmDone()
    {

        $userAction = UserAction::where('user_id', auth()->user()->id)->where('user_type', 'USER')->where('id', session('action_id'))->first();
        if (!$userAction) {
            $notify[] = ['error', 'Sorry! Unable to process'];
            return to_route('user.home')->withNotify($notify)->withInput();
        }

        $user    = auth()->user();
        $details = $userAction->details;

        $invoice = Invoice::where('invoice_num', $details->invoice_id)->where('pay_status', 0)->first();
        if (!$invoice) {
            $notify[] = ['error', 'Invoice not found'];
            return to_route('home')->withNotify($notify);
        }

        $currency   = Currency::where('status', 1)->findOrFail($invoice->currency_id);
        $userWallet = Wallet::checkWallet(['user' => $user, 'type' => userGuard()['type']])->where('currency_id', $currency->id)->firstOrFail();

        if ($invoice->total_amount > $userWallet->balance) {
            $notify[] = ['error', 'Sorry! Insufficient balance'];
            return to_route('user.home')->withNotify($notify);
        }

        $userWallet->balance -= $invoice->total_amount;
        $userWallet->save();

        $trx                    = getTrx();
        $userTrx                = new Transaction();
        $userTrx->user_id       = $user->id;
        $userTrx->user_type     = $invoice->user_type;
        $userTrx->wallet_id     = $userWallet->id;
        $userTrx->currency_id   = $invoice->currency_id;
        $userTrx->before_charge = $invoice->total_amount;
        $userTrx->amount        = $invoice->total_amount;
        $userTrx->post_balance  = $userWallet->balance;
        $userTrx->charge        = 0;
        $userTrx->charge_type   = '+';
        $userTrx->trx_type      = '-';
        $userTrx->remark        = 'invoice_payment';
        $userTrx->details       = "Invoice payment successful to";
        $userTrx->receiver_id   = $invoice->user_id;
        $userTrx->receiver_type = "USER";
        $userTrx->trx           = $trx;
        $userTrx->save();

        $rcvWallet = Wallet::where('user_type', $invoice->user_type)->where('currency_id', $invoice->currency_id)->where('user_id', $invoice->user_id)->first();
        $rcvWallet->balance += $invoice->get_amount;
        $rcvWallet->save();

        $rcvTrx                = new Transaction();
        $rcvTrx->user_id       = $invoice->user_id;
        $rcvTrx->user_type     = $invoice->user_type;
        $rcvTrx->wallet_id     = $rcvWallet->id;
        $rcvTrx->currency_id   = $invoice->currency_id;
        $rcvTrx->before_charge = $invoice->total_amount;
        $rcvTrx->amount        = $invoice->get_amount;
        $rcvTrx->charge        = $invoice->charge;
        $rcvTrx->post_balance  = $rcvWallet->balance;
        $rcvTrx->remark        = 'invoice_payment';
        $rcvTrx->charge_type   = '-';
        $rcvTrx->trx_type      = '+';
        $rcvTrx->details       = 'Got payment of invoice from';
        $rcvTrx->receiver_id   = $user->id;
        $rcvTrx->receiver_type = "USER";
        $rcvTrx->trx           = $userTrx->trx;
        $rcvTrx->save();

        $invoice->pay_status = 1;
        $invoice->save();

        notify($rcvWallet->user, 'GET_INVOICE_PAYMENT', [
            'total_amount'  => showAmount($invoice->total_amount, $invoice->currency, currencyFormat: false),
            'get_amount'    => showAmount($invoice->get_amount, $invoice->currency, currencyFormat: false),
            'charge'        => showAmount($invoice->charge, $invoice->currency, currencyFormat: false),
            'currency_code' => $invoice->currency->currency_code,
            'invoice_id'    => $invoice->invoice_num,
            'from_user'     => $user->username,
            'trx'           => $userTrx->trx,
            'post_balance'  => showAmount($rcvWallet->balance, $invoice->currency, currencyFormat: false),
            'time'          => showDateTime($invoice->created_at, 'd/M/Y @h:i a'),
        ]);

        notify($user, 'PAY_INVOICE_PAYMENT', [
            'total_amount'  => showAmount($invoice->total_amount, $invoice->currency, currencyFormat: false),
            'currency_code' => $invoice->currency->currency_code,
            'invoice_id'    => $invoice->invoice_num,
            'time'          => showDateTime($invoice->created_at, 'd/M/Y @h:i a'),
            'to_user'       => $rcvWallet->user->username,
            'trx'           => $userTrx->trx,
            'post_balance'  => showAmount($userWallet->balance, $invoice->currency, currencyFormat: false),
        ]);

        $notify[] = ['success', 'Invoice payment successfully'];
        return to_route('user.home')->withNotify($notify);
    }

    public function sendInvoiceToMail($invoiceNum)
    {

        $invNum  = decrypt($invoiceNum);
        $user    = auth()->user();
        $invoice = Invoice::where('user_id', $user->id)->where('user_type', 'USER')->where('invoice_num', $invNum)->first();

        if (!$invoice) {
            $notify[] = ['error', 'Invoice not found'];
            return back()->withNotify($notify);
        }

        $invoice->username = $invoice->invoice_to;
        try {
            notify($invoice, 'SEND_INVOICE_MAIL',
                [
                    'url' => route('invoice.payment', encrypt($invoice->invoice_num)),
                ],
                ['email']
            );
        } catch (\Exception $ex) {
            $notify[] = ['error', 'Sorry! Mail can not send right now. Try again later'];
            return back()->withNotify($notify);
        }

        $notify[] = ['success', 'Invoice sent to email successfully'];
        return to_route('user.invoice.edit', $invoice->invoice_num)->withNotify($notify);
    }

    public function updateInvoice(Request $request)
    {

        $request->validate(
            [
                'invoice_to'  => 'required',
                'email'       => 'required|email',
                'address'     => 'required',
                'item_name'   => 'required',
                'item_name.*' => 'required',
                'amount'      => 'required',
                'amount.*'    => 'required|numeric|gt:0',
                'currency_id' => 'required|integer',
            ],
            [
                'item_name.*.required' => 'Item name fields required',
                'amount.*.required'    => 'Amount fields required',
                'amount.*.gt'          => 'Amount fields must be greater than 0',
                'amount.*.numeric'     => 'Amount fields value should be numeric',
            ]
        );

        $invoiceCharge = TransactionCharge::where('slug', 'invoice_charge')->first();
        $currency      = Currency::find($request->currency_id);

        if (!$currency) {
            $notify[] = ['error', 'Sorry! Currency not found'];
            return back()->withNotify($notify);
        }

        $rate          = $currency->rate;
        $initialAmount = array_sum($request->amount);
        $fixedCharge   = currencyConverter($invoiceCharge->fixed_charge, $rate);

        if ($currency->currency_type == 1) {
            $cap       = getAmount(currencyConverter($invoiceCharge->cap, $rate), 2);
            $presetion = 2;
        } else {
            $cap       = getAmount(currencyConverter($invoiceCharge->cap, $rate), 8);
            $presetion = 8;
        }

        $totalCharge = getAmount(chargeCalculator($initialAmount, $invoiceCharge->percent_charge, $fixedCharge), $presetion);

        if ($totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $getAmount             = $initialAmount - $totalCharge;
        $invoice               = Invoice::findOrFail($request->invoice_id);
        $invoice->user_id      = auth()->id();
        $invoice->user_type    = 'USER';
        $invoice->invoice_to   = $request->invoice_to;
        $invoice->email        = $request->email;
        $invoice->address      = $request->address;
        $invoice->currency_id  = $request->currency_id;
        $invoice->charge       = $totalCharge;
        $invoice->total_amount = $initialAmount;
        $invoice->get_amount   = $getAmount;
        $invoice->save();

        InvoiceItem::where('invoice_id', $invoice->id)->delete();
        $items = array_combine($request->item_name, $request->amount);

        foreach ($items as $itemName => $itemAmount) {
            $invoiceItem             = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_name  = $itemName;
            $invoiceItem->amount     = $itemAmount;
            $invoiceItem->save();
        }

        $notify[] = ['success', 'Invoice update successfully'];
        return to_route('user.invoice.edit', $invoice->invoice_num)->withNotify($notify);
    }
}
