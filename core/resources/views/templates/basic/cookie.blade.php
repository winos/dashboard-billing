@extends($activeTemplate .'layouts.frontend')
@section('content')
    <section class="pt-100 pb-100">
        <div class="container">
            <div class="row gy-4 justify-content-center">
                <div class="col-xl-12">
                    <div class="contact-form-wrapper">
                        @php echo $cookie->data_values->description; @endphp
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
