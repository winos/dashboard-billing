@php
    $content = @getContent('feature.content', true)->data_values;
    $elements = @getContent('feature.element', orderById:true);
@endphp
<section class="pt-150 pb-150 border-top section-shape-two section--bg2 position-relative z-index-2">
    <div class="section-wave-img opacity50"><img src="{{ frontendImage('feature' ,@$content->background_image, '1920x1080') }}"
            alt="image"></div>
    <div class="container">
        <div class="row align-items-center mt-5 pb-100">
            <div class="col-lg-4 d-lg-block d-none wow fadeInLeft" data-wow-duration="0.3" data-wow-delay="0.3s">
                <div class="feature-thumb">
                    <img src="{{ frontendImage('feature' , @$content->image, '768x888') }}"
                        alt="@lang('image')"
                    >
                </div>
            </div>
            <div class="col-lg-5 wow fadeInRight" data-wow-duration="0.3" data-wow-delay="0.7s">
                <div class="feature-content ps-lg-5">
                    <h2 class="section-title text-white">{{ __(@$content->heading) }}</h2>
                    <p class="text-white mt-3">{{ __(@$content->subheading) }}</p>
                </div>
            </div>
        </div>
        <div class="row gy-4">
            @foreach ($elements as $element)
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.1s">
                    <div class="feaure-card">
                        <div class="feaure-card__icon">
                            <img src="{{ frontendImage('feature' ,@$element->data_values->icon, '65x65') }}"
                                alt="image">
                        </div>
                        <div class="feaure-card__content mt-4">
                            <h3 class="title text-white">{{ __(@$element->data_values->title) }}</h3>
                            <p class="mt-3 text-white">{{ @$element->data_values->short_details }}</p>
                        </div>
                    </div><!-- feaure-card end -->
                </div>
            @endforeach
        </div>
    </div>
</section>
