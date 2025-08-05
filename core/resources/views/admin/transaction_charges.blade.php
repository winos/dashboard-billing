@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="payment-method-item">
                        <div class="payment-method-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border--primary mb-3">
                                        <h5 class="card-header bg--primary">@lang('Money Transfer/Request Charge')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$moneyTransfer->id}}">
                                                <div class="input-group has_append mb-3">
                                                <label class="w-100">@lang('Percentage Charge') <span class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($moneyTransfer->percent_charge,2) }}"/>
                                                    <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Fixed Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($moneyTransfer->fixed_charge,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Minimum Amount') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="min_limit" placeholder="0" value="{{ getAmount($moneyTransfer->min_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Amount') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="max_limit" placeholder="0" value="{{  getAmount($moneyTransfer->max_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('Daily Transfer Limit') <span class="text-danger">*</span><code class="text--primary">@lang('(Put -1 if you don\'t want limit)')</code></label>
                                                            <input type="number" step="any" class="form-control" name="daily_limit" placeholder="0" value="{{  getAmount($moneyTransfer->daily_limit,2) }}"/>
                                                                <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('Daily Request Accept Limit') <span class="text-danger">*</span><code class="text--primary">@lang('(Put -1 for unlimited)')</code></label>
                                                            <input type="number" step="any" class="form-control" name="daily_request_accept_limit" placeholder="0" value="{{  getAmount($moneyTransfer->daily_request_accept_limit,2) }}"/>
                                                                <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Charge Cap') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want charge cap)')</code></label>
                                                    <input type="number" step="any" class="form-control" name="cap" placeholder="0" value="{{  getAmount($moneyTransfer->cap) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary mb-3">
                                        <h5 class="card-header bg--primary">@lang('Voucher Create Charge')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$voucherCharge->id}}">
                                                <div class="input-group has_append mb-3">
                                                <label class="w-100">@lang('Percentage Charge') <span class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($voucherCharge->percent_charge,2) }}"/>
                                                    <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Fixed Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($voucherCharge->fixed_charge,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="row">
                                                    <div class="input-group has_append mb-3 col-md-12">
                                                        <label class="w-100">@lang('Minimum Amount') <span class="text-danger">*</span></label>
                                                        <input type="number" step="any" class="form-control" name="min_limit" placeholder="0" value="{{ getAmount($voucherCharge->min_limit,2) }}"/>
                                                            <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                    </div>
                                                    <div class="input-group has_append mb-3 col-md-12">
                                                        <label class="w-100">@lang('Maximum Amount') <span class="text-danger">*</span></label>
                                                        <input type="number" step="any" class="form-control" name="max_limit" placeholder="0" value="{{  getAmount($voucherCharge->max_limit,2) }}"/>
                                                            <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                    </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Daily Voucher Create Limit') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want limit)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="voucher_limit" placeholder="0" value="{{  getAmount($voucherCharge->voucher_limit) }}"/>
                                                    
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Charge Cap') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want charge cap)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="cap" placeholder="0" value="{{  getAmount($voucherCharge->cap,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary mb-3">
                                        <h5 class="card-header bg--primary">@lang('Invoice Charge')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$invoiceCharge->id}}">
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Percentage Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($invoiceCharge->percent_charge,2) }}"/>
                                                        <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Fixed Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($invoiceCharge->fixed_charge,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Charge Cap') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want charge cap)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="cap" placeholder="0" value="{{  getAmount($invoiceCharge->cap,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary mb-3">
                                        <h5 class="card-header bg--primary">@lang('Money Exchange Charge')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$exchangeCharge->id}}">
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Percentage Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($exchangeCharge->percent_charge,2) }}"/>
                                                        <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Fixed Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($exchangeCharge->fixed_charge,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Charge Cap') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want charge cap)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="cap" placeholder="0" value="{{  getAmount($exchangeCharge->cap,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary mb-3">
                                        <h5 class="card-header bg--primary">@lang('Money Out Charges')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$moneyOutCharge->id}}">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('Percentage Charge') <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($moneyOutCharge->percent_charge,2) }}"/>
                                                                <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('Fixed Charge') <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($moneyOutCharge->fixed_charge,2) }}"/>
                                                                <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('Minimum Amount') <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" name="min_limit" placeholder="0" value="{{ getAmount($moneyOutCharge->min_limit,2) }}"/>
                                                                <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('Maximum Amount') <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" name="max_limit" placeholder="0" value="{{  getAmount($moneyOutCharge->max_limit,2) }}"/>
                                                                <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Agent Commission (fixed)') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="agent_com_fixed" placeholder="0" value="{{  getAmount($moneyOutCharge->agent_commission_fixed,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Agent Commission (%)') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="agent_com_percent" placeholder="0" value="{{  getAmount($moneyOutCharge->agent_commission_percent,2) }}"/>
                                                        <div class="input-group-text">%</div>
                                                </div>

                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Daily Money Out Limit') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want limit)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="daily_limit" placeholder="0" value="{{  getAmount($moneyOutCharge->daily_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Monthly Money Out Limit') <span class="text-danger">*</span><code class="text--primary">@lang('(Put -1 if you don\'t want limit)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="monthly_limit" placeholder="0" value="{{  getAmount($moneyOutCharge->monthly_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary mb-3">
                                        <h5 class="card-header bg--primary">@lang('Money In Charges')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$moneyInCharge->id}}">
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Minimum Amount') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="min_limit" placeholder="0" value="{{ getAmount($moneyInCharge->min_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Amount') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="max_limit" placeholder="0" value="{{  getAmount($moneyInCharge->max_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Agent Commission (fixed)') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="agent_com_fixed" placeholder="0" value="{{  getAmount($moneyInCharge->agent_commission_fixed,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Agent Commission (%)') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="agent_com_percent" placeholder="0" value="{{  getAmount($moneyInCharge->agent_commission_percent,2) }}"/>
                                                        <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Daily Money In Limit') <span class="text-danger">*</span><code class="text--primary">@lang('(Put -1 if you don\'t want limit)')</code></label>
                                                    <input type="number" step="any" class="form-control" name="daily_limit" placeholder="0" value="{{  getAmount($moneyInCharge->daily_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Monthly Money In Limit') <span class="text-danger">*</span><code class="text--primary">@lang('(Put -1 if you don\'t want limit)')</code></label>
                                                    <input type="number" step="any" class="form-control" name="monthly_limit" placeholder="0" value="{{  getAmount($moneyInCharge->monthly_limit,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary">
                                        <h5 class="card-header bg--primary">@lang('Api Payment Charge')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$apiCharge->id}}">
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Percentage Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($apiCharge->percent_charge,2) }}"/>
                                                        <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Fixed Charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($apiCharge->fixed_charge,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Maximum Charge Cap') <span class="text-danger">*</span> <code class="text--primary">@lang('(Put -1 if you don\'t want charge cap)')</code> </label>
                                                    <input type="number" step="any" class="form-control" name="cap" placeholder="0" value="{{  getAmount($apiCharge->cap,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border--primary">
                                        <h5 class="card-header bg--primary">@lang('Make Payment Charges')</h5>
                                        <div class="card-body">
                                            <form action="{{route('admin.transaction.charges.update')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$paymentCharge->id}}">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('User Percentage Charge') <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" name="percentage_charge" placeholder="0" value="{{ getAmount($paymentCharge->percent_charge,2) }}"/>
                                                                <div class="input-group-text">%</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group has_append mb-3">
                                                            <label class="w-100">@lang('User Fixed Charge') <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" name="fixed_charge" placeholder="0" value="{{ getAmount($paymentCharge->fixed_charge,2) }}"/>
                                                                <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Merchant percent charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="merchant_percent_charge" placeholder="0" value="{{  getAmount($paymentCharge->merchant_percent_charge,2) }}"/>
                                                        <div class="input-group-text">%</div>
                                                </div>
                                                <div class="input-group has_append mb-3">
                                                    <label class="w-100">@lang('Merchant fixed charge') <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control" name="merchant_fixed_charge" placeholder="0" value="{{  getAmount($paymentCharge->merchant_fixed_charge,2) }}"/>
                                                        <div class="input-group-text"> {{gs('cur_text')}} </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn--primary w-100 h-45 mt-2">@lang('Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('style')
    <style>
        .card[class*="border"]{
            border: 1px solid;
        }
    </style>
@endpush

