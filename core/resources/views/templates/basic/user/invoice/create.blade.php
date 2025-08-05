@extends($activeTemplate.'layouts.user_master')

@section('content')
<div class="col-xl-12">
    <div class="card style--two">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h4 class="fw-normal fs-sm-18">@lang('Create Invoice')</h4>
            <a href="{{route('user.invoice.all')}}" class="btn btn--base btn-sm"> <i class="las la-list"></i> @lang('Invoice List')</a>
        </div>
        <div class="card-body p-4">
            <form  method="POST" id="form">
                <div class="row justify-content-center">
                    <div class="col-lg-6"> 
                        @csrf
                        <div class="d-widget">
                            <div class="d-widget__header">
                                <h6>@lang('Invoice Details')</h4>
                            </div>
                            <div class="d-widget__content px-5">
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-6 form-group">
                                            <label class="mb-0">@lang('Invoice To')<span class="text--danger">*</span> </label>
                                            <input type="text" class="form--control style--two invoice_to" name="invoice_to" placeholder="@lang('Invoice To')" required value="{{old('invoice_to')}}">
                                        </div>
                                        <div class="col-lg-6 form-group">
                                            <label class="mb-0">@lang('E-mail')<span class="text--danger">*</span></label>
                                            <input type="email" class="form--control style--two email" name="email" placeholder="@lang(' E-mail')" required>
                                            <label class="exist text-end"></label>
                                        </div>
                                    </div><!-- row end -->
                                </div>
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Address')<span class="text--danger">*</span> </label>
                                            <input type="text" class="form--control style--two address" name="address" placeholder="@lang('Address')" required value="{{old('address')}}">
                                        </div>
                                    </div><!-- row end -->
                                </div>
                                <div class="p-4 border mb-4">
                                    <div class="row">
                                        <div class="col-lg-12 form-group">
                                            <label class="mb-0">@lang('Your Wallet')</label>
                                            <select class="select style--two currency select2" name="currency_id"  required>
                                                <option value="" selected>@lang('Select Wallet')</option>
                                                @foreach ($currencies as $currency)
                                                <option value="{{$currency->id}}" data-code="{{$currency->currency_code}}" data-rate="{{$currency->rate}}">{{$currency->currency_code}}</option>
                                                @endforeach
                                            </select>
                                        </div> 
                                    <input type="hidden" class="invoiceCharge" data-fixcharge="{{$invoiceCharge->fixed_charge}}" data-percentage="{{$invoiceCharge->percent_charge}}" data-cap="{{$invoiceCharge->cap}}">
                                    </div><!-- row end -->
                                </div>
                            </div>
                        </div>  
                    </div>
                    <div class="col-lg-6 mt-4 mt-lg-0">
                        <div class="d-widget">
                            <div class="d-widget__header">
                                <h6>@lang('Invoice items')</h4>
                            </div>
                            <div class="d-widget__content px-5">
                                <div class="p-4 border mb-4">
                                    <div class="row appendArea">
                                        <div class="col-xl-6 form-group">
                                            <label class="mb-0">@lang('Item Name')<span
                                                class="text-danger">*</span></label>
                                            <input class="form--control itemName" type="text"
                                            name="item_name[]" value="" placeholder="@lang('Item Name')" required disabled>
                                        </div>
                                        <div class="col-xl-4 form-group">
                                            <label class="mb-0">@lang('Amount')<span class="text-danger">*</span></label>
                                            <input class="form--control amount" oninput="amountSum()" type="number" step="any" name="amount[]"  placeholder="@lang('Amount')" min="0" required disabled>
                                        </div>
                                        <div class="col-xl-2 form-group align-self-end">
                                            <button type="button" class="btn icon-btn btn-sm item btn--base w-100" disabled><i class="las la-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="append"></div>
                                </div>
                                <div class="text-end">
                                    <label class="mb-0 total">@lang('Total : ')<b class="amount_total text--base">0.00</b></label>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <div class="text-center">
                        <button type="submit" class="btn btn-md btn--base mt-4 create w-100">@lang('Create Invoice')</button>
                    </div>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <div class="d-widget border-start-0 shadow-sm">
                    <div class="d-widget__content">
                        <ul class="cmn-list-two text-center mt-4">
                            <li class="list-group-item">@lang('Total Amount'): <strong class="total_amount"></strong></li>
                            <li class="list-group-item">@lang('Total Charge'): <strong class="charge"></strong></li> 
                            <li class="list-group-item">@lang('You will get'): <strong class="will_get"></strong></li> 
                        </ul>
                    </div>
                    <div class="d-widget__footer text-center border-0 pb-3">
                        <button type="submit" class="btn btn-md w-100 d-block btn--base" form="form">
                            @lang('Confirm')<i class="las la-long-arrow-alt-right"></i>
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
    .icon-btn {
        width: 50px;
        height: 50px;
        font-size: 32px;
    }
    .total {
        font-size: 17px;
    }
    .d-widget__content .icon-btn i {
        font-size: 22px;
        color: #fff;
        margin-bottom: 0;
    }
</style>
@endpush

@push('script')
  <script>
    'use strict';  
    var amount;

    function amountSum() { 
        var totalAmount = 0;
        $('.amount').each(function(e){
           
            if($(this).val()!=''){
                totalAmount = totalAmount + parseFloat($(this).val());
            }
            
            $('.amount_total').text(totalAmount.toFixed(2)+' '+$('.currency option:selected').data('code'))
            amount = totalAmount
        })
    }

    function partial(){

        if($('.invoice_to').val() == '' || $('.email').val() == '' || $('.address').val() == '' || $('.currency').val() == '' || $('.amount').val() == ''){
            notify('error','Each fields are required create invoice')
            return false
        }

        var selected = $('.currency option:selected')
        var code = selected.data('code')
        var rate = selected.data('rate')
        var amount = parseFloat($('.amount_total').text())
        var cap = parseFloat($('.invoiceCharge').data('cap')/parseFloat(rate))
        
        var fixCharge = parseFloat($('.invoiceCharge').data('fixcharge'))/parseFloat(rate)
        var percentage = (amount*parseFloat($('.invoiceCharge').data('percentage')))/100
        var totalCharge = fixCharge+percentage;

        if(totalCharge > cap){
            totalCharge = cap
        }

        $('#confirm').find('.total_amount').text(amount+' '+code)
        $('#confirm').find('.charge').text(totalCharge.toFixed(2)+' '+code)
        $('#confirm').find('.will_get').text((amount - totalCharge).toFixed(2)+' '+code)
        $('#confirm').modal('show')
    }
    
    (function ($) {

        $('.item').on('click',function(){
            var append = ` <div class="row">
                                <div class="col-xl-6 form-group">
                                    <input class="form--control " type="text"
                                    name="item_name[]" value="" required placeholder="@lang('Item Name')">
                                </div>
                                <div class="col-xl-4 form-group">
                                    <input class="form--control amount" required type="number" step="any" name="amount[]" oninput="amountSum()" min="0" placeholder="@lang('Amount')">
                                </div>
                                <div class="col-xl-2 form-group align-self-end">
                                    <button type="button" class="btn w-100 icon-btn btn--danger btn-sm item remove item"><i class="las la-times"></i> </button>
                                </div>
                            </div>    
            
            `
            $('.append').append(append);
        })

        $(document).on('click','.remove',function(){
            var val = $(this).parent().parent().find('.amount').val()
            if(val != ''){
                amount -= val;
                $('.amount_total').text(amount.toFixed(2)+' '+$('.currency option:selected').data('code'))
            }
            $(this).parent().parent().remove()
        })  
        
        $('#form').on('submit', function(){

            var confirmMdoal = $('#confirm');

            if(!confirmMdoal.is(':visible')){
                partial();
                confirmMdoal.modal('show');
                return false;
            }

        });

        $('.currency').on('change',function () { 
           if( $('.currency option:selected').val() != ''){
               $('.itemName').attr('disabled',false)
               $('.amount').attr('disabled',false)
               $('.item').attr('disabled',false)
               amountSum();
           }
           else{
               $('.itemName').attr('disabled',true)
               $('.amount').attr('disabled',true)
               $('.item').attr('disabled',true)
           }
        })

    })(jQuery);

    var old = @json(session()->getOldInput());
    if(old.length != 0){
        $('input[name=invoice_to]').val(old.invoice_to);
        $('input[name=email]').val(old.email);
        $('input[name=address]').val(old.address);
        $('select[name=currency_id]').val(old.currency_id).change();

        $(old.item_name).each(function(index, data) {

            if(index == 0){
                $('.appendArea').find("input[name='item_name[]']").eq(0).val(data);
                $('.appendArea').find("input[name='amount[]']").eq(0).val(old.amount[index]);
            }
            else{
                var html = ` <div class="row">
                    <div class="col-xl-6 form-group">
                        <input class="form--control " type="text"
                        name="item_name[]" value="${data}" placeholder="@lang('Item Name')">
                    </div>
                    <div class="col-xl-4 form-group">
                        <input class="form--control amount" value="${old.amount[index]}" type="number" step="any" name="amount[]" oninput="amountSum()" min="0" placeholder="@lang('Amount')">
                    </div>
                    <div class="col-xl-2 form-group align-self-end">
                        <button type="button" class="btn w-100 icon-btn btn--danger btn-sm item remove item"><i class="las la-times"></i></button>
                    </div>
                </div>`;
                $('.append').append(html);
            }

            amountSum();
        });

    }

  </script>
@endpush
