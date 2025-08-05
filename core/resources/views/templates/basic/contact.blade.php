@extends($activeTemplate .'layouts.frontend')
@php
    $content = getContent('contact_us.content', true)->data_values;
    $user = userGuard();
@endphp
@section('content')
    <section class="pt-100 pb-100 position-relative z-index section--bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <span class="subtitle fw-bold text--base font-size--18px border-left">{{ __(@$content->title) }}</span>
                    <h2 class="section-title">{{ __(@$content->heading) }}</h2>
                    <ul class="contact-info-list mt-5">
                        <li class="single-info d-flex flex-wrap align-items-center">
                            <div class="single-info__icon bg--base text-white d-flex justify-content-center align-items-center rounded-3">
                                <i class="las la-map-marked-alt"></i>
                            </div>
                            <div class="single-info__content">
                                <h4 class="title">@lang('Our Address')</h4>
                                <p class="mt-3">{{ __(@$content->address) }}</p>
                            </div>
                        </li><!-- single-info end -->
                        <li class="single-info d-flex flex-wrap align-items-center">
                            <div class="single-info__icon bg--base text-white d-flex justify-content-center align-items-center rounded-3">
                                <i class="las la-envelope"></i>
                            </div>
                            <div class="single-info__content">
                                <h4 class="title">@lang('Email Address')</h4>
                                <p class="mt-3">
                                    <a href="mailto:{{ @$content->email_address }}" class="text--secondary">
                                        {{ @$content->email_address }}
                                    </a>
                                </p>
                            </div>
                        </li><!-- single-info end -->
                        <li class="single-info d-flex flex-wrap align-items-center">
                            <div class="single-info__icon bg--base text-white d-flex justify-content-center align-items-center rounded-3">
                                <i class="las la-phone-volume"></i>
                            </div>
                            <div class="single-info__content">
                                <h4 class="title">@lang('Phone Number')</h4>
                                <p class="mt-3"><a href="tel:{{ @$content->contact_number }}" class="text--secondary">{{ @$content->contact_number }}</a></p>
                            </div>
                        </li><!-- single-info end -->
                    </ul>
                </div>
                <div class="col-lg-6 mt-lg-0 mt-5">
                    <form class="box-shadow p-sm-5 p-3 section--bg rounded-3 position-relative" method="post" >
                        @csrf
                        <div class="row"> 
                            <div class="form-group col-lg-12">
                                <label>@lang('Name') <sup class="text--danger">*</sup></label>
                                <input 
                                    name="name" 
                                    type="text" 
                                    placeholder="@lang('Enter your name')" class="form--control" 
                                    value="{{ $user['user'] ? $user['user']->fullname : old('name') }}" 
                                    {{ $user['user'] ? 'readonly' : 'required' }}
                                >
                            </div>
                            <div class="form-group col-lg-12">
                                <label>@lang('Email') <sup class="text--danger">*</sup></label>
                                <input 
                                    name="email" 
                                    type="text" 
                                    placeholder="@lang('Enter your email address')" 
                                    class="form--control" 
                                    value="{{ $user['user'] ? $user['user']->email : old('email') }}" 
                                    {{ $user['user'] ? 'readonly' : 'required' }}
                                >
                            </div>
                            <div class="form-group col-lg-12">
                                <label>@lang('Subject') <sup class="text--danger">*</sup></label>
                                <input name="subject" type="text" placeholder="@lang('Enter your subject')" class="form--control" value="{{ old('subject') }}" required>
                            </div>
                            <div class="form-group col-lg-12">
                                <label>@lang('Message') <sup class="text--danger">*</sup></label>
                                <textarea name="message" wrap="off" placeholder="@lang('Enter your message')" class="form--control">{{ old('message') }}</textarea>
                            </div>

                            <x-captcha />

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @if($sections->secs != null)
        @foreach(json_decode($sections->secs) as $sec)
            @include($activeTemplate.'sections.'.$sec)
        @endforeach
    @endif
@endsection







