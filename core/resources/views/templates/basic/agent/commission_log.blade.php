@extends($activeTemplate.'layouts.agent_master')

@section('content')
<div class="custom--card mt-5">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-6">
                <h6>@lang($pageTitle)</h6>
            </div>
        </div>
        <div class="table-responsive--sm">
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Transaction ID')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Post Balance')</th>
                        <th>@lang('Operation Type')</th>
                        <th>@lang('Time')</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $k=>$data)
                    <tr>
                        <td>{{$data->trx}}</td>
                        <td>
                            <strong class="text--success">
                                {{$data->currency->currency_symbol}}{{showAmount($data->amount,$data->currency, currencyFormat:false)}} {{$data->currency->currency_code}}
                            </strong>
                        </td>
                        <td>
                            {{$data->currency->currency_symbol}}{{showAmount($data->post_balance,$data->currency,currencyFormat:false)}} {{$data->currency->currency_code}}
                        </td>
                        <td>
                            {{ucwords(str_replace('_',' ', $data->remark))}} 
                        </td>
                        <td>
                            {{showDateTime($data->created_at,'d M Y @ g:i a')}}
                        </td>
                    </tr>
                @empty 
                    <tr>
                        <td colspan="100%" class="text-center not-found">{{ __($emptyMessage) }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="pt-4 pb-2">
                {{paginateLinks($logs)}}
            </div>
        @endif
    </div>
</div><!-- custom--card end -->
@endsection
