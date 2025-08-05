@php
    $content = @getContent('service.content', true)->data_values;
    $elements = @getContent('service.element', orderById:true);
@endphp

<section class="pt-100 pb-150 position-relative z-index-2 section--bg2 section-shape">
    <div class="section-wave-img opacity50">
        <img src="{{ frontendImage('service', $content->background_image, '1920x1080') }}" alt="@lang('image')">
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-header text-center wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                    <h2 class="section-title text-white">{{ __(@$content->heading) }}</h2>
                    <p class="mt-3 text-white">{{ __(@$content->subheading) }}</p>
                </div>
            </div>
        </div><!-- row end -->
        <div class="row gy-4">
            @foreach($elements as $element)
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.1s">
                    <div class="service-card text-center">
                        <div class="dotted-wrapper"></div>
                        <div class="service-card__icon">
                            @php
                                echo @$element->data_values->service_icon;
                            @endphp
                        </div>
                        <div class="service-card__content mt-4">
                            <h3 class="title">{{ @$element->data_values->title }}</h3>
                            <p class="mt-3">{{ __(@$element->data_values->description) }}</p>
                        </div>
                    </div><!-- service-card end -->
                </div>
            @endforeach
        </div>
    </div>
</section>
