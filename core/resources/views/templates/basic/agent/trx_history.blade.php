@extends($activeTemplate.'layouts.agent_master')

@section('content')
<div class="row justify-content-center mt-5">
  <div class="col-12">
    <div class="card custom--card border-0">
        <div class="card-header border">
            <div class="row align-items-center">
                <div class="col-8">
                    <h6>{{__($pageTitle)}}</h6>
                </div>
                <div class="col-4 text-end">
                    <button class="trans-serach-open-btn"><i class="las la-search"></i></button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <form class="transaction-top-form my-3 px-3"  method="GET">
                <div class="custom-select-search-box">
                    <input type="text" name="search" value="{{ request()->search }}" class="form--control" placeholder="@lang('Search by transaction ID')">
                    <button type="submit" class="search-box-btn">
                        <i class="las la-search"></i>
                    </button>
                </div>
            </form><!-- transaction-top-form end -->
            <div class="row gy-3 gx-3 p-3">
                <div class="col-xl-2 col-lg-3 col-md-4">
                    <div class="custom-select-box-two">
                        <label>@lang('Transaction type')</label>
                        <select onChange="window.location.href=this.value">
                            <option value="{{queryBuild('type','')}}" {{request('type') == '' ? 'selected':''}}>@lang('All Type')</option>
                            <option value="{{queryBuild('type','plus_trx')}}" {{request('type') == 'plus_trx' ? 'selected':''}}>@lang('Plus Transactions')</option>
                            <option value="{{queryBuild('type','minus_trx')}}" {{request('type') == 'minus_trx' ? 'selected':''}}>@lang('Minus Transactions')</option>
                        </select>
                    </div><!-- custom-select-box-two end -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4">
                    <div class="custom-select-box-two">
                        <label>@lang('Operation Type')</label>
                        <select onChange="window.location.href=this.value">
                            <option value="{{queryBuild('operation','')}}" {{request('operation') == '' ? 'selected':''}}>
                                @lang('All Operation')
                            </option>
                            <option value="{{queryBuild('operation','add_money')}}" {{request('operation') == 'add_money' ? 'selected':''}}>
                                @lang('Add Money')
                            </option>
                            <option value="{{queryBuild('operation','money_in')}}" {{request('operation') == 'money_in' ? 'selected':''}}>
                                @lang('Money In')
                            </option>
                            <option value="{{queryBuild('operation','money_out')}}" {{request('operation') == 'money_out' ? 'selected':''}}>
                                @lang('Money Out')
                            </option>
                            <option value="{{queryBuild('operation','withdraw_money')}}" {{request('operation') == 'withdraw_money' ? 'selected':''}}>
                                @lang('Withdraw Money')
                            </option>
                            <option value="{{queryBuild('operation','add_balance')}}" {{request('operation') == 'add_balance' ? 'selected':''}}>
                                @lang('Add By System')
                            </option>
                            <option value="{{queryBuild('operation','sub_balance')}}" {{request('operation') == 'sub_balance' ? 'selected':''}}>
                                @lang('Subtract By System')
                            </option>
                        </select>
                    </div><!-- custom-select-box-two end -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4">
                    <div class="custom-select-box-two">
                        <label>@lang('History From')</label>
                        <select onChange="window.location.href=this.value">
                            <option value="{{queryBuild('time','')}}" {{request('time') == '' ? 'selected':''}}>@lang('All Time')</option>
                            <option value="{{queryBuild('time','7days')}}" {{request('time') == '7days' ? 'selected':''}}>@lang('Last 7 days')</option>
                            <option value="{{queryBuild('time','15days')}}" {{request('time') == '15days' ? 'selected':''}}>@lang('Last 15 days')</option>
                            <option value="{{queryBuild('time','1month')}}" {{request('time') == '1month' ? 'selected':''}}>@lang('Last month')</option>
                            <option value="{{queryBuild('time','1year')}}" {{request('time') == '1year' ? 'selected':''}}>@lang('Last Year')</option>
                        </select>
                    </div><!-- custom-select-box-two end -->
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4">
                    <div class="custom-select-box-two">
                        <label>@lang('Wallet Currency')</label>
                        <select onChange="window.location.href=this.value">
                            <option value="{{queryBuild('currency','')}}" {{request('currency') == '' ? 'selected':''}}>
                                @lang('All Currency')
                            </option>
                            @foreach (userGuard()['user']->wallets as $item)
                                <option 
                                    value="{{queryBuild('currency',strtolower($item->currency->currency_code))}}" 
                                    {{request('currency') == strtolower($item->currency->currency_code) ? 'selected':''}}
                                >
                                    {{$item->currency->currency_code}}
                                </option>
                            @endforeach
                        </select>
                    </div><!-- custom-select-box-two end -->
                </div>
            </div>
            <div class="accordion table--acordion" id="transactionAccordion">
                @forelse ($histories as $history)
                    <div class="accordion-item transaction-item {{$history->trx_type == '-' ? 'sent-item':'rcv-item'}}">
                        <h2 class="accordion-header" id="h-{{$loop->iteration}}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-{{$loop->iteration}}" aria-expanded="false" aria-controls="c-1">
                            <div class="col-lg-3 col-sm-4 col-6 order-1 icon-wrapper">
                                <div class="left">
                                    <div class="icon">
                                        <i class="las la-long-arrow-alt-right"></i>
                                    </div>
                                    <div class="content">
                                        <h6 class="trans-title">{{__(ucwords(str_replace('_',' ',$history->remark)))}}</h6>
                                        <span class="text-muted font-size--14px mt-2">{{showDateTime($history->created_at,'M d Y @g:i:a')}}</span>
                                    </div>
                                </div>
                            </div>
                           <div class="col-lg-6 col-sm-5 col-12 order-sm-2 order-3 content-wrapper mt-sm-0 mt-3">
                                <p class="text-muted font-size--14px"><b>{{__($history->details)}} {{$history->receiver ? @$history->receiver->username : ''  }}</b></p>
                            </div>
                            <div class="col-lg-3 col-sm-3 col-6 order-sm-3 order-2 text-end amount-wrapper">
                                <p><b>{{showAmount($history->amount,$history->currency, currencyFormat: false)}} {{$history->currency->currency_code}}</b></p>
                            </div>
                        </button>
                        </h2>
                        <div id="c-{{$loop->iteration}}" class="accordion-collapse collapse" aria-labelledby="h-1" data-bs-parent="#transactionAccordion">
                            <div class="accordion-body">
                                <ul class="caption-list">
                                <li>
                                    <span class="caption">@lang('Transaction ID')</span>
                                    <span class="value">{{$history->trx}}</span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Wallet')</span>
                                    <span class="value">{{$history->currency->currency_code}}</span>
                                </li>
                                @if($history->charge > 0)
                                    <li>
                                        <span class="caption">@lang('Before Charge')</span>
                                        <span class="value">{{showAmount($history->before_charge,$history->currency, currencyFormat: false)}} {{$history->currency->currency_code}}</span>
                                    </li>
                                
                                    <li>
                                        <span class="caption">@lang('Charge')</span>
                                        <span class="value">{{ $history->charge_type }}{{showAmount($history->charge,$history->currency, currencyFormat: false)}} {{$history->currency->currency_code}}</span>
                                    </li>
                                @endif
                                <li>
                                    <span class="caption">@lang('Transacted Amount')</span>
                                    <span class="value">{{showAmount($history->amount,$history->currency, currencyFormat: false)}} {{$history->currency->currency_code}}</span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Remaining Balance')</span>
                                    <span class="value">{{showAmount($history->post_balance,$history->currency, currencyFormat: false)}} {{$history->currency->currency_code}}</span>
                                </li>
                                </ul>
                            </div>
                        </div>
                    </div><!-- transaction-item end -->
                    @empty
                    <div class="accordion-body text-center">
                       <h4 class="text--muted">@lang('No transaction found')</h4>
                    </div>
                @endforelse
            </div>
        </div>
        @if($histories->hasPages())
            <div class="card-footer bg-transparent pt-4 pb-3">
                {{paginatelinks($histories)}}
            </div>
        @endif
    </div>
  </div>
</div>
@endsection