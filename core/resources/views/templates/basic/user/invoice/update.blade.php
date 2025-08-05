@extends($activeTemplate . 'layouts.user_master')

@section('content')
    <div class="col-xl-12">
        <div class="card style--two">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div class="bank-icon  me-2 mb-2">
                    <h4 class="fw-normal"> @lang('Update Invoice')</h4>
                </div>
                <div class="action--buttons d-flex flex-wrap align-items-center gap-sm-2 gap-1">
                    <div class="form-group">
                        <a href="{{ route('user.invoice.all') }}" class="btn btn--dark btn-sm me-2"> <i class="las la-backward"></i> @lang('Back') </a>
                    </div>
                    <div class="form-group">
                        <a href="{{ route('user.invoice.send.mail', encrypt($invoice->invoice_num)) }}"
                            class="btn btn--base btn-sm me-2"> <i class="lab la-telegram"></i> @lang('Send To Email')
                        </a>
                    </div>
                    @if ($invoice->status != 2) 
                        <div class="form-group ">
                            @if ($invoice->status == 1)
                                <a href="javascript:void(0)" class="btn btn--secondary btn-sm me-2"> <i class="las la-clipboard-check"></i> @lang('Published') </a>
                            @else
                                <a href="{{ route('user.invoice.publish', encrypt($invoice->invoice_num)) }}"
                                    class="btn btn--primary btn-sm me-2">
                                    <i class="las la-clipboard-check"></i>
                                    @lang('Publish Invoice') </a>
                            @endif
                        </div>
                    @endif
                    <div class="form-group">
                        @if ($invoice->status == 2)
                            <a href="javascript:void(0)" class="btn btn--secondary btn-sm"> <i class="las la-times-circle"></i> @lang('Discarded')</a>
                        @elseif($invoice->status == 0)
                            <a data-route="{{ route('user.invoice.discard', encrypt($invoice->invoice_num)) }}"
                                href="javascript:void(0)" class="btn btn--danger btn-sm delete"> <i class="las la-times-circle"></i> @lang('Discard Invoice')
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('user.invoice.update') }}" method="POST" id="form">
                    <div class="p-4 border mb-4">
                        <div class="row">
                            <label class="">@lang('Invoice Payment Url')</label>
                            <div class="col-lg-12 input-group">
                                <input type="text" 
                                    class="form--control text-secondary" 
                                    id="url" 
                                    readonly  
                                    required 
                                    value="{{ route('invoice.payment', encrypt($invoice->invoice_num)) }}"
                                >
                                <button type="button" class="input-group-text copytext"
                                    id="basic-addon2">@lang('Copy')
                                </button>
                            </div>
                        </div><!-- row end -->
                    </div>
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            @csrf
                            <div class="d-widget">
                                <div class="d-widget__header d-flex justify-content-between flex-wrap gap-2">
                                    <h4>@lang('Invoice Details #'){{ $invoice->invoice_num }}</h4>
                                    @php echo $invoice->showPaymentStatusBadge; @endphp
                                </div>
                                <div class="d-widget__content px-5">
                                    <div class="p-4 border mb-4">
                                        <div class="row">
                                            <div class="col-lg-6 form-group">
                                                <label class="mb-0">@lang('Invoice To')<span class="text--danger">*</span>
                                                </label>
                                                <input type="text" class="form--control style--two invoice_to"
                                                    name="invoice_to" placeholder="@lang('Invoice To')" required
                                                    value="{{ $invoice->invoice_to }}">
                                            </div>
                                            <div class="col-lg-6 form-group">
                                                <label class="mb-0">@lang('E-mail')<span
                                                        class="text--danger">*</span></label>
                                                <input type="email" class="form--control style--two email" name="email"
                                                    placeholder="@lang(' E-mail')" required value="{{ $invoice->email }}">
                                                <label class="exist text-end"></label>
                                            </div>
                                        </div><!-- row end -->
                                    </div>
                                    <div class="p-4 border mb-4">
                                        <div class="row">
                                            <div class="col-lg-12 form-group">
                                                <label class="mb-0">@lang('Address')<span class="text--danger">*</span>
                                                </label>
                                                <input type="text" class="form--control style--two address"
                                                    name="address" placeholder="@lang('Address')" required
                                                    value="{{ $invoice->address }}">
                                            </div>
                                        </div><!-- row end -->
                                    </div>

                                    <div class="p-4 border mb-4">
                                        <div class="row">
                                            <div class="col-lg-12 form-group">
                                                <label class="mb-0">@lang('Your Wallet')</label>
                                                <select class="select style--two currency select2" name="currency_id" required>
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{ $currency->id }}"
                                                            data-code="{{ $currency->currency_code }}"
                                                            data-rate="{{ $currency->rate }}"
                                                            {{ $currency->id == $invoice->currency_id ? 'selected' : '' }}
                                                        >
                                                            {{ $currency->currency_code }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input type="hidden" class="invoiceCharge"
                                                data-fixcharge="{{ $invoiceCharge->fixed_charge }}"
                                                data-percentage="{{ $invoiceCharge->percent_charge }}"
                                                data-cap="{{ $invoiceCharge->cap }}">
                                        </div><!-- row end -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="d-widget">
                                <div class="d-widget__header">
                                    <h6>@lang('Invoice items')</h4>
                                </div>
                                <div class="d-widget__content px-5">
                                    <div class="p-4 border mb-4">
                                        @foreach ($invoiceItems as $item)
                                            <div class="row">
                                                <div class="col-lg-7 form-group">
                                                    <label class="mb-0">@lang('Item Name')<span
                                                            class="text-danger">*</span></label>
                                                    <input class="form--control " type="text" name="item_name[]"
                                                        value="{{ $item->item_name }}" placeholder="@lang('Item Name')"
                                                        required>
                                                </div>
                                                <div class="col-lg-4 form-group">
                                                    <label class="mb-0">@lang('Amount')<span
                                                            class="text-danger">*</span></label>
                                                    <input class="form--control amount" oninput="amountSum()"
                                                        type="number" name="amount[]" placeholder="@lang('Amount')"
                                                        value="{{ getAmount($item->amount) }}" required>
                                                </div>
                                                <div class="col-lg-1 form-group">
                                                    <label for="">&nbsp;</label>
                                                    <button type="button"
                                                        class="btn icon-btn btn-sm {{ $loop->first ? 'item' : 'remove btn--danger' }}">{{ $loop->first ? '+' : '-' }}</button>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="append"></div>
                                    </div>
                                    <div class="text-end">
                                        <label class="mb-0 total">@lang('Total : ')<b
                                                class="amount_total">{{ getAmount($invoice->total_amount, 2) }}
                                                {{ $invoice->currency->currency_code }}</b></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="text-center">
                            <button type="button"
                                class="btn btn-md btn--base mt-4 create w-100">@lang('Update Invoice')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form  method="POST">
                    @csrf
                    <div class="modal-body text-center">
                        <i class="las la-exclamation-circle text-danger display-2 mb-15"></i>
                        <h4 class="text--secondary mb-15">@lang('Are you sure want to discard this?')</h4>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn--dark btn-sm"
                            data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--base btn-sm del">@lang('Discard')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">@lang('Invoice Calculation Preview')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <div class="d-widget border-start-0 shadow-sm">
                        <div class="d-widget__content">
                            <ul class="cmn-list-two text-center mt-4">
                                <li class="list-group-item">
                                    @lang('Total Amount'): <strong class="total_amount"></strong>
                                </li>
                                <li class="list-group-item">
                                    @lang('Total Charge'): <strong class="charge"></strong>
                                </li>
                                <li class="list-group-item">
                                    @lang('You will get'): <strong class="will_get"></strong>
                                </li>
                            </ul>
                        </div>
                        <div class="d-widget__footer text-center border-0 pb-3">
                            <button type="submit" class="btn btn-md w-100 d-block btn--base" form="form">
                                @lang('Confirm') <i class="las la-long-arrow-alt-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    .margin--top--45{
        margin-top: -45px;
    }
    @media screen and (max-width: 991px) {
        .margin--top--45{
            margin-top: -5px;
        }
    }
</style>
@endpush

@push('script')
    <script>
        'use strict';
        
        var amount = parseFloat($('.amount_total').text());

        function amountSum() {
            var totalAmount = 0;
            $('.amount').each(function(e) {
                if ($(this).val() != '') {
                    totalAmount += parseFloat($(this).val());
                }
                $('.amount_total').text(totalAmount.toFixed(2) + ' ' + $('.currency option:selected').data('code'))
                amount = totalAmount
            })
        }

        $('.currency').on('change',function () { 
            amountSum();
        })

        function partial(){
            if ($('.invoice_to').val() == '' || $('.email').val() == '' || $('.address').val() == '' || $(
                    '.currency').val() == '' || $('.amount').val() == '') {
                notify('error', 'Each fields are required to create invoice')
                return false
            }

            var selected = $('.currency option:selected')
            var code = selected.data('code')
            var rate = selected.data('rate')
            var amount = parseFloat($('.amount_total').text())
            var cap = parseFloat($('.invoiceCharge').data('cap')/parseFloat(rate))
            var fixCharge = parseFloat($('.invoiceCharge').data('fixcharge')) / parseFloat(rate)
            var percentage = (amount * parseFloat($('.invoiceCharge').data('percentage'))) / 100
            var totalCharge = fixCharge + percentage;

            if (totalCharge > cap) {
                totalCharge = cap
            }

            $('#confirm').find('.total_amount').text(amount + ' ' + code)
            $('#confirm').find('.charge').text(totalCharge.toFixed(2) + ' ' + code)
            $('#confirm').find('.will_get').text((amount - totalCharge).toFixed(2) + ' ' + code)
            $('#confirm').modal('show')
        }

        (function($) {
            $('.delete').on('click', function() {
                var route = $(this).data('route')
                var modal = $('#deleteModal');
                modal.find('form').attr('action', route)
                modal.modal('show');
            })

            $('.item').on('click',function(){
                var append = ` <div class="row">
                                    <div class="col-lg-7 form-group">
                                        <input class="form--control " type="text"
                                        name="item_name[]" value="" placeholder="@lang('Item Name')">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <input class="form--control amount" type="number" step="any" name="amount[]" oninput="amountSum()" min="0" placeholder="@lang('Amount')">
                                    </div>
                                    <div class="col-lg-1 form-group">
                                        <label for="">&nbsp;</label>
                                        <button type="button" class="btn icon-btn btn--danger btn-sm item remove margin--top--45">-</button>
                                    </div>
                                </div>    
                
                `
                $('.append').append(append);
            })

            $(document).on('click', '.remove', function() {
                var val = $(this).parent().parent().find('.amount').val()
                if (val != '') {
                    amount -= val;
                    $('.amount_total').text(amount.toFixed(2) + ' ' + $('.currency option:selected').data(
                        'code'))
                }
                $(this).parent().parent().remove()
            })

            $('#form').on('submit', function(){

                partial();
                var confirmMdoal = $('#confirm');

                if(!confirmMdoal.is(':visible')){
                    confirmMdoal.modal('show');
                    return false;
                }

            });

            $('.create').on('click', function() {
               return partial();
            })

            $('.copytext').on('click', function() {
                var copyText = document.getElementById("url");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                iziToast.success({
                    message: "URL Copied",
                    position: "topRight"
                });
            });

        })(jQuery);
    </script>
@endpush
