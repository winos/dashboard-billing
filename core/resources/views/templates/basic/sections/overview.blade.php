@php
    $content = @getContent('overview.content', true)->data_values;
    $elements = @getContent('overview.element', orderById:true);
@endphp
<section class="overview-section bg_img dark-overlay"
    style="background-image: url('{{ frontendImage('overview' ,@$content->background_image, '1920x1080') }}');">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-xl-6 col-lg-7 order-lg-1 order-2 mt-lg-0 mt-5">
                <h2 class="section-title text-white wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                    {{ __(@$content->heading) }}
                </h2>
                <p class="text-white mt-3 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.5s">
                    {{ __(@$content->subheading) }}
                </p>
                <div class="row mt-5 wow fadeInUp gy-4" data-wow-duration="0.3" data-wow-delay="0.7s">
                    @foreach ($elements as $element)
                        <div class="col-4">
                            <div class="overview-single">
                                <h3 class="overview-number text-white">{{ __(@$element->data_values->counter_digit) }}</h3>
                                <p class="caption text-white">{{ __(@$element->data_values->title) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4 order-lg-2 order-1">
                <div class="overview-video-wrapper text-center">
                    <a href="{{ @$content->video_link }}" data-rel="lightcase:myCollection"
                        class="video-btn video-btn--lg">
                        <span class="icon"><i class="las la-play"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
