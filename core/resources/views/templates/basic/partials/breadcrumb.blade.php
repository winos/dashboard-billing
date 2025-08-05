@php
    $content = getContent('breadcrumb.content', true)->data_values;
@endphp
<section class="inner-hero overlay--one bg_img" style="background-image: url('{{ frontendImage('breadcrumb', @$content->background_image, '1920x768') }}');">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h2 class="page-title text-center text-white">{{ __($pageTitle) }}</h2>
                <ul class="page-breadcrumb justify-content-center">
                    <li><a href="{{ route('home') }}">@lang('Home')</a></li>
                    <li>{{ __($pageTitle) }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>
