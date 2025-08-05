@extends($activeTemplate.'layouts.checkout_master')
@php
     $policies = getContent('policies.element',false,'',1);
@endphp
@section('content')
<div class="checkout-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">

          <div class="checkout-wrapper rounded-3  {{@$apiPayment->checkout_theme   == 'dark' ? 'checkout-wrapper--dark':''}} shake-card">

              @if(@$apiPayment['error'])
                <h3 class="text-danger text-center">{{ __($apiPayment['message']) }}</h3>
              @else

                @if (session('data'))
                    <a class="p-close" href="{{route('test.cancel.payment')}}" class="text--base">@lang('Cancel')</a>
                  @else
                    <a class="p-close" href="{{route('cancel.payment')}}" class="text--base">@lang('Cancel')</a>
                  @endif
                <div class="shape">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#0099ff" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,218.7C384,224,480,224,576,234.7C672,245,768,267,864,256C960,245,1056,203,1152,170.7C1248,139,1344,117,1392,106.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>
                </div>

                <div class="shape-two">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#0099ff" fill-opacity="1" d="M0,320L48,288C96,256,192,192,288,165.3C384,139,480,149,576,154.7C672,160,768,160,864,170.7C960,181,1056,203,1152,181.3C1248,160,1344,96,1392,64L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>
                </div>

                <div class="checkout-wrapper__header text-center">
                  @if($apiPayment->site_logo) <img src="{{$apiPayment->site_logo}}" alt="image" class="form-logo mb-3"> @endif
                  <h3 class="product-price mt-2">{{@$apiPayment->currency->currency_symbol}} {{showAmount($apiPayment->amount,$apiPayment->currency, currencyFormat: false)}} {{@$apiPayment->currency->currency_code}}</h3>
                  <h6 class="mb-5 title fw-normal mt-2">@lang('for') {{__($apiPayment->details)}}</h6>
                </div>
                <form class="mt-5 check-mail">
                    @csrf
                  <div class="form-group">
                    <input type="email" name="email" class="form--control" required placeholder="@lang('Enter your '.gs('site_name').' email address')" autocomplete="off">
                    <small class="text-danger error-message"></small>
                  </div>
                  <button type="submit" class="btn btn-md btn--base w-100 next">@lang('Next')</button>
                </form>
                <div class="or-divider my-3"><span>@lang('or')</span></div>
                <a href="{{route('user.register')}}" class="btn btn-md bg--light w-100">@lang('Create an Account')</a>
                <div class="row mt-4">
                  <div class="col-6">
                    <p class="font-size--14px">@lang('Powered by') <a href="{{route('home')}}" class="text--secondary font-size--16px"><strong>{{gs('site_name')}}</strong></a></p>
                  </div>
                  <div class="col-6">
                    <ul class="checkout-footer-menu d-flex flex-wrap justify-content-end">
                        @foreach ($policies as $policy)
                        <li><a class="text--base" href="{{route('links',[slug(@$policy->data_values->title),$policy->id])}}">{{@$policy->data_values->title}}</a></li>
                        @endforeach

                        @if (session('data'))
                          <li><a href="{{route('test.cancel.payment')}}" class="text--base">@lang('Cancel')</a></li>
                        @else
                          <li><a href="{{route('cancel.payment')}}" class="text--base">@lang('Cancel')</a></li>
                        @endif
                    </ul>
                  </div>
                </div>

              @endif
          </div>
        </div>
      </div><!-- row end -->

    </div>
  </div>
@endsection

@push('script')
     <script>
            'use strict';
            (function ($) {

              $('.check-mail').on('submit',function(e){
                e.preventDefault();
                var email = $('input[name=email]').val();
                var csrf = $('input[name=_token]').val();
                $.post('{{ $mailCheckRoute }}',{email:email,_token:csrf}, function(response){

                  if(response.error == 'yes'){
                    const shakeCard = document.querySelectorAll('.shake-card')
                      shakeCard.forEach(function(card){
                      card.classList.add('shake', 'wrong-info');
                      setTimeout(function() {
                        card.classList.remove('shake');
                      }, 600);
                    });
                    $('.error-message').html(`<i class="las la-ban"></i> ${response.message}`)
                  }else{
                    window.location = response.redirect_url
                  }

                });

              });
            })(jQuery);
     </script>
@endpush
