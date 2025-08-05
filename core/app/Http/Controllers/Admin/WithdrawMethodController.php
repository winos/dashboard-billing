<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\Currency;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;

class WithdrawMethodController extends Controller
{
    public function methods()
    {
        $pageTitle = 'Withdrawal Methods';
        $methods   = WithdrawMethod::orderBy('name')->orderBy('id')->get();
        return view('admin.withdraw.index', compact('pageTitle', 'methods'));
    }

    public function create()
    {
        $pageTitle  = 'New Withdrawal Method';
        $currencies = Currency::enable()->get();
        return view('admin.withdraw.create', compact('pageTitle', 'currencies'));
    }

    public function store(Request $request)
    {
        $validation = [
            'name'           => 'required',
            'currencies*'    => 'required|integer',
            'user_guards'    => 'required',
            'user_guards.*'  => 'required|in:1,2,3',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'min_limit'      => 'required|numeric|gt:fixed_charge',
            'max_limit'      => 'required|numeric|gt:min_limit',
            'instruction'    => 'required',
        ];

        $formProcessor       = new FormProcessor();
        $generatorValidation = $formProcessor->generatorValidation();
        $validation          = array_merge($validation, $generatorValidation['rules']);
        $request->validate($validation, $generatorValidation['messages']);

        $generate = $formProcessor->generate('withdraw_method');

        $method       = new WithdrawMethod();
        $method->name = $request->name;

        $method->form_id        = @$generate->id ?? 0;
        $method->min_limit      = $request->min_limit;
        $method->max_limit      = $request->max_limit;
        $method->fixed_charge   = $request->fixed_charge;
        $method->percent_charge = $request->percent_charge;
        $method->currencies     = $request->currencies;
        $method->user_guards    = $request->user_guards;
        $method->description    = $request->instruction;
        $method->save();

        $notify[] = ['success', 'Withdraw method added successfully'];
        return to_route('admin.withdraw.method.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle  = 'Update Withdrawal Method';
        $method     = WithdrawMethod::with('form')->findOrFail($id);
        $currencies = Currency::enable()->get();
        $form       = $method->form;
        return view('admin.withdraw.edit', compact('pageTitle', 'method', 'form', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $validation = [
            'name'           => 'required',
            'fixed_charge'   => 'required|numeric|gte:0',
            'min_limit'      => 'required|numeric|gt:fixed_charge',
            'max_limit'      => 'required|numeric|gt:min_limit',
            'percent_charge' => 'required|numeric|between:0,100',
            'currencies*'    => 'required|integer',
            'instruction'    => 'required',
            'user_guards'    => 'required',
            'user_guards.*'  => 'required|in:1,2,3',
        ];

        $formProcessor       = new FormProcessor();
        $generatorValidation = $formProcessor->generatorValidation();
        $validation          = array_merge($validation, $generatorValidation['rules']);
        $request->validate($validation, $generatorValidation['messages']);

        $method = WithdrawMethod::findOrFail($id);

        $generate               = $formProcessor->generate('withdraw_method', true, 'id', $method->form_id);
        $method->form_id        = @$generate->id ?? 0;
        $method->name           = $request->name;
        $method->min_limit      = $request->min_limit;
        $method->max_limit      = $request->max_limit;
        $method->fixed_charge   = $request->fixed_charge;
        $method->percent_charge = $request->percent_charge;
        $method->description    = $request->instruction;
        $method->currencies     = $request->currencies;
        $method->user_guards    = $request->user_guards;
        $method->save();

        $notify[] = ['success', 'Withdraw method updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return WithdrawMethod::changeStatus($id);
    }

}
