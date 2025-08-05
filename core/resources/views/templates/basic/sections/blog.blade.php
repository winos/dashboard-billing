@php
    $content = @getContent('blog.content', true)->data_values;
    $elements = @getContent('blog.element', false, 3);
@endphp
<section class="pt-100 pb-100 section--bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-header text-center wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.3s">
                    <span class="section-subtitle border-left">{{ __(@$content->title) }}</span>
                    <h2 class="section-title">{{ __(@$content->heading) }}</h2>
                    <p class="mt-3">{{ __(@$content->subheading) }}</p>
                </div>
            </div>
        </div><!-- row end -->
        <div class="row gy-4 justify-content-center">
            @foreach ($elements as $element)
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="0.3" data-wow-delay="0.1s">
                    <div class="blog-card">
                        <div class="blog-card__thumb rounded-3">
                            <img src="{{ frontendImage('blog', 'thumb_'.@$element->data_values->image) }}" alt="@lang('image')">
                        </div>
                        <div class="blog-card__meta">
                            <div class="post-time">
                                <span class="post-date">{{ showDateTime(@$element->created_at, 'd') }}</span>
                                <span class="post-month">{{ showDateTime(@$element->created_at, 'M') }}</span>
                            </div>
                        </div>
                        <div class="blog-card__content">
                            <h4 class="blog-title">
                                <a href="{{ route('blog.details',  $element->slug) }}">
                                    {{ __(@$element->data_values->title) }}
                                </a>
                            </h4>
                            <p class="mt-3">
                                {{ strLimit(strip_tags(@$element->data_values->description), 200) }}
                            </p>
                            <a href="{{ route('blog.details', $element->slug) }}" class="font-size--14px fw-bold text--base mt-2">
                                @lang('Read More')
                            </a>
                        </div>
                    </div><!-- blog-card end -->
                </div>
            @endforeach
        </div>
    </div>
</section>
