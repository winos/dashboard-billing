@php
    $content = @getContent('about.content', true)->data_values;
    $elements = @getContent('about.element', orderById:true);
@endphp

<!-- about section start -->
<section class="pt-100 pb-100 bg-white">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-xl-6 order-xl-1 order-2 mt-xl-0 mt-5">
                <span class="section-subtitle border-left">{{ __(@$content->title) }}</span>
                <h2 class="section-title">{{ @$content->heading }}</h2>
                <p class="mt-3 font-size--18px">{{ @$content->short_details }}</p>
                <ul class="award-list d-flex flex-wrap align-items-center mt-3">
                    @foreach ($elements as $element)
                        <li>
                            <img src="{{ frontendImage('about',@$element->data_values->award_logo, '100x100') }}"
                                alt="image"
                            >
                        </li>
                    @endforeach
                </ul>
                <a href="{{ @$content->button_link }}" class="btn btn--base btn--custom mt-5">{{ __(@$content->button_name) }}</a>
            </div>
            <div class="col-xl-5 order-xl-2 order-1">
                <div class="about-thumb">
                    <img src="{{ frontendImage('about',@$content->background_image, '992x692') }}"
                        alt="@lang('image')" class="wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                    <div class="about-img-content wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.7s">
                        <h4 class="years">{{ @$content->experience_year }}</h4>
                        <span class="caption">{{ @$content->experience_text }}</span>
                    </div>
                </div>
            </div>
        </div><!-- row end -->
    </div>
</section>
<!-- about section end -->
