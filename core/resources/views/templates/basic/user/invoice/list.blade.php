@extends($activeTemplate . 'layouts.user_master')

@section('content')
    <div class="custom--card p-0">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-6">
                    <h6>@lang($pageTitle)</h6>
                </div>
                <div class="col-6 text-end">
                    <a class="btn btn--base btn-sm" href="{{ route('user.invoice.create') }}"> <i class="las la-plus"></i> @lang('Create New')</a>
                </div>
            </div>
            <div class="table-responsive--md">
                <table class="table custom--table"> 
                    <thead>
                        <tr>
                            <th>@lang('Invoice To')</th>
                            <th>@lang('Email')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Payment Status')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Created at')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_to }}</td>
                                <td>{{ $invoice->email }}</td>
                                <td>
                                    {{ showAmount($invoice->total_amount, $invoice->currency, currencyFormat: false) }}
                                    {{ $invoice->currency->currency_code }}</td>
                                <td>
                                    @php echo $invoice->showPaymentStatusBadge; @endphp
                                </td>
                                <td>
                                    @php echo $invoice->showStatusBadge; @endphp
                                </td>
                                <td>
                                    {{ showDateTime($invoice->created_at, 'd M Y @ g:i a') }}
                                </td>
                                <td>
                                    <button 
                                        data-id="link{{ $invoice->id }}"
                                        class="btn btn--success btn-sm copyInvoiceLink" data-toggle="tooltip" title="@lang('Copy Link')"
                                    >
                                        <i class="la la-copy"></i>
                                    </button>
                                    <input type="text" value="{{ route('invoice.payment', encrypt($invoice->invoice_num)) }}" id="link{{ $invoice->id }}" class="d-none">
                                    @if($invoice->pay_status == 1 || $invoice->status == 1 || $invoice->status == 2)
                                        <a  target="_blank" 
                                            href="{{ route('invoice.payment', encrypt($invoice->invoice_num)) }}"
                                            class="btn btn--dark btn-sm" data-toggle="tooltip" title="@lang('See')"
                                        >
                                            <i class="la la-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('user.invoice.edit', $invoice->invoice_num) }}" class="btn btn--base btn-sm">
                                            <i class="la la-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center not-found" colspan="12">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="card-footer bg-transparent pt-4 pb-2">
                    {{ paginatelinks($invoices) }}
                </div>
            @endif 
        </div>
    </div><!-- custom--card end -->
@endsection

@push('script')
<script> 
    (function($){
        "use strict";

        $(document).on('click', '.copyInvoiceLink', function(){

            var element = document.getElementById($(this).data('id'));
            var $temp = $("<input>");

            $("body").append($temp);
            $temp.val($(element).val()).select();
            document.execCommand("copy");
            $temp.remove();

            notify('success', 'URL Copied');
        });
        
    })(jQuery)
</script>
@endpush 