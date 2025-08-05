@extends($activeTemplate.'layouts.merchant_master')
@section('content')

<div class="row justify-content-center mt-5">
    <div class="col-md-10">
        <div class="custom--card">
            <div class="card-header">
                <h4>@lang('Fill Up The Informations')</h4>
            </div>
            <form  method="post" enctype="multipart/form-data">
                <div class="card-body">
                    @csrf
                    @if($userKyc->form_data)
                    @foreach($userKyc->form_data as $k => $v)
                    @if($v->type == "text")
                    <div class="form-group">
                        <label><strong>{{__($v->field_level)}} @if($v->validation == 'required') <span class="text-danger">*</span> @endif</strong></label>
                        <input type="text" name="{{$k}}" class="form--control" value="{{old($k)}}"
                            placeholder="{{__($v->field_level)}}" @if($v->validation == "required") required @endif>
                        @if ($errors->has($k))
                        <span class="text-danger">{{ __($errors->first($k)) }}</span>
                        @endif
                    </div>
                    @elseif($v->type == "textarea")
                    <div class="form-group">
                        <label><strong>{{__($v->field_level)}} @if($v->validation == 'required') <span
                                    class="text-danger">*</span> @endif</strong></label>
                        <textarea name="{{$k}}" class="form--control" placeholder="{{__($v->field_level)}}" rows="3"
                            @if($v->validation == "required") required @endif>{{old($k)}}</textarea>
                        @if ($errors->has($k))
                        <span class="text-danger">{{ __($errors->first($k)) }}</span>
                        @endif
                    </div>
                    @elseif($v->type == "file") 
                        <div class="form-froup">
                            <label><strong>{{__($v->field_level)}} @if($v->validation == 'required') <span class="text-danger">*</span>
                                @endif</strong></label>
                            <input type="file" class="form--control" name="{{$k}}" id="profilePicUpload1"
                                accept=".png, .jpg, .jpeg" @if($v->validation == "required") required @endif>
                            
                        </div>
                            
                      @if ($errors->has($k))
                        <br>
                        <span class="text-danger">{{ __($errors->first($k)) }}</span>
                      @endif
                     @endif
                    @endforeach
                   @endif
        
        
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn--base btn-md text-center text-white w-100">@lang('Submit')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection