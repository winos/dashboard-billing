@php
	$content = @getContent('brands.content', true)->data_values;
    $elements = @getContent('brands.element', orderById:true);
@endphp
<!-- brand section start -->
<div class="brand-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4">
                <h4>{{ __(@$content->heading) }}</h4>
            </div>
            <div class="col-lg-8">
                <div class="brand-slider">
                    @foreach($elements as $element)
                        <div class="single-slide">
                            <div class="brand-item">
                                <img src="{{ frontendImage('brands', @$element->data_values->image, '60x32') }}" 
									alt="@lang('image')"
								>
                            </div>
                        </div><!-- single-slide end -->
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- brand section end -->
