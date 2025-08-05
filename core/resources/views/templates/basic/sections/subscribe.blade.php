@php
    $subscribe = getContent('subscribe.content', true);
@endphp

<section class="subscribe-section position-relative z-index-2 section--bg2 overflow-hidden">
    <div class="section-wave-img opacity50">
        <img src="{{ frontendImage('subscribe', @$subscribe->data_values->image, '1900x450') }}" alt="@lang('image')">
    </div>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 col-md-10">
                <div class="subscribe-content text-start wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                    <h2 class="section-title text-white">{{ __(@$subscribe->data_values->heading) }}</h2>
                    <p class="mt-3 text-white">{{ __(@$subscribe->data_values->subheading) }}</p>
                </div>
            </div>
            <div class="col-lg-6 col-md-10">
                <form  method="POST" class="subscription-form">
                    @csrf
                    <form class="search-form">
                        <div class="input--group">  
                            <input type="email" name="email" class="form--control" placeholder="@lang('Enter Email')">
                            <button type="submit" class="btn btn--base rounded-0"> <span class="btn-text">@lang('Subscribe')</span> <span class="btn-icon"><i class="fas fa-paper-plane"></i></span> </button>
                        </div>
                    </form>
                </form>
            </div>
        </div>
    </div>
</section>

@push('script')
    <script>
        (function($){

            "use strict";

            var formEl = $(".subscription-form");

            formEl.on('submit', function(e){
                e.preventDefault();
                var data = formEl.serialize();

                if(!formEl.find('input[name=email]').val()){
                    return notify('error', 'Email field is required');
                }

                $.ajax({
                url:"{{ route('subscribe') }}",
                method:'post',
                data:data,

                success:function(response){
                    if(response.success){
                        formEl.find('input[name=email]').val('')
                        notify('success', response.message);
                    }else{
                        $.each(response.error, function( key, value ) {
                            notify('error', value);
                        });
                    }
                },
                error:function(error){
                        console.log(error)
                    }

                });
            });

        })(jQuery);
    </script>
@endpush




