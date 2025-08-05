<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{

    public function allCurrency(Request $request)
    {
        $pageTitle  = 'Manage Currencies';
        $currencies = $this->getCurrency();
        return view('admin.currency.index', compact('pageTitle', 'currencies'));
    }

    public function add(Request $request)
    {

        $currency = new Currency();
        $this->currencySave($currency, $request);

        $notify[] = ['success', 'Currency added successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request)
    {

        $currency = Currency::findOrFail($request->currency_id);
        $this->currencySave($currency, $request);

        $notify[] = ['success', 'Currency updated successfully'];
        return back()->withNotify($notify);
    }

    protected function currencySave($currency, $request)
    {

        $request->validate([
            'currency_fullname' => 'required',
            'currency_code'     => 'required|unique:currencies,currency_code,' . $currency->id,
            'currency_symbol'   => 'required|unique:currencies,currency_symbol,' . $currency->id,
            'currency_type'     => 'required|in:1,2',
            'rate'              => 'required|numeric|gt:0',
        ]);

        $general = gs();

        $currency->currency_fullname = $request->currency_fullname;
        $currency->currency_code     = strtoupper($request->currency_code);
        $currency->currency_symbol   = $request->currency_symbol;
        $currency->currency_type     = $request->currency_type;
        $currency->is_default        = $request->is_default ? 1 : 0;
        $currency->status            = $request->status ? 1 : 0;
        $currency->rate              = $request->rate;

        if ($request->is_default) {
            Currency::default()->where('id', '!=', $currency->id)->update(['is_default' => 0]);

            $currency->status = 1;

            $general->cur_text = strtoupper($request->currency_code);
            $general->cur_sym  = $request->currency_symbol;
            $general->save();
        }

        //When trying to add new currency
        if (!$request->currency_id) {
            $currency->status = 1;
        }

        $currency->save();
    }

    public function updateApiKey(Request $request)
    {

        $request->validate([
            'fiat_api_key'   => 'required',
            'crypto_api_key' => 'required',
        ]);

        $gnl = gs();

        $gnl->fiat_currency_api   = $request->fiat_api_key;
        $gnl->crypto_currency_api = $request->crypto_api_key;
        $gnl->save();

        $notify[] = ['success', 'Api key updated successfully'];
        return back()->withNotify($notify);
    }

    protected function getCurrency()
    {
        return Currency::searchable(['currency_code', 'currency_fullname'])->orderBy('is_default', 'desc')->paginate(getPaginate());
    }

}
