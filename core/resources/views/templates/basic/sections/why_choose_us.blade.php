@php
    $content = @getContent('why_choose_us.content', true)->data_values;
    $elements = @getContent('why_choose_us.element', orderById:true);
@endphp

<section class="pt-100 pb-100 position-relative z-index-2 bg_img">
    <div class="section-img bg_img opacity20"
        style="background-image: url('{{ frontendImage('why_choose_us', @$content->background_image, '1920x1080') }}');">
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-header text-center wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                    <h2 class="section-title">{{ __(@$content->heading) }}</h2>
                    <p class="mt-3">{{ __(@$content->subheading) }}</p>
                </div>
            </div>
        </div><!-- row end -->
        <div class="row gy-4">
            @foreach($elements as $element)
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.1s">
                    <div class="choose-card rounded-3">
                        <div class="choose-card__icon">
                            <img src="{{ frontendImage('why_choose_us', @$element->data_values->icon, '65x65') }}"
                                alt="@lang('image')">
                        </div>
                        <div class="choose-card__content">
                            <h3 class="title">{{ __(@$element->data_values->title) }}</h3>
                            <p class="mt-3">{{ @$element->data_values->short_details }}</p>
                        </div>
                    </div><!-- choose-card end -->
                </div>
            @endforeach
        </div>
    </div>
</section>
