@extends('admin.layouts.app')

@section('panel')
<div class="row g-4">
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-header bg--primary">
                <div class="d-flex justify-content-between flex-wrap">
                    <h5 class="text-white"><i class="las la-user-friends"></i> @lang('User Modules')</h5>
                </div>
            </div>
            <div class="card-body ">
                <ul class="list-group user-area">
                    @foreach ( $modules->where('user_type','USER') as $module)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <label class="fw-bold" for="{{$module->id}}">{{ucwords(str_replace('_',' ',$module->slug))}}</label>
                        <div class="form-group mb-0">
                            <label class="switch">
                                <input type="checkbox" class="update" data-id="{{$module->id}}"  id="{{$module->id}}" {{$module->status == 1 ? 'checked':''}}>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li> 
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-header bg--dark">
                <div class="d-flex justify-content-between flex-wrap">
                    <h5 class="text-white"><i class="las la-user-friends"></i> @lang('Agent Modules')</h5>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group agent-area">
                    @foreach ( $modules->where('user_type','AGENT') as $module)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <label class="fw-bold" for="{{$module->id}}">{{ucwords(str_replace('_',' ',$module->slug))}}</label>
                        <div class="form-group mb-0">
                            <label class="switch">
                                <input type="checkbox" class="update" data-id="{{$module->id}}"  id="{{$module->id}}" {{$module->status == 1 ? 'checked':''}}>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li> 
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12">
        <div class="card">
            <div class="card-header bg--secondary">
                <div class="d-flex justify-content-between flex-wrap">
                    <h5 class="text-white"><i class="las la-user-friends"></i> @lang('Merchant Modules')</h5>
                </div>                
            </div>
            <div class="card-body">
                <ul class="list-group merchant-area">
                    @foreach ( $modules->where('user_type','MERCHANT') as $module)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <label class="fw-bold" for="{{$module->id}}">{{ucwords(str_replace('_',' ',$module->slug))}}</label>
                        <div class="form-group mb-0">
                            <label class="switch">
                                <input type="checkbox" class="update" data-id="{{$module->id}}"  id="{{$module->id}}" {{$module->status == 1 ? 'checked':''}}>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li> 
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        'use strict';
        (function ($) {

            $('.update').on('change', function () {
                var url = "{{route('admin.module.update')}}"
                var id = $(this).data('id');
                var token = "{{csrf_token()}}";
                var data = {
                    id:id,
                    _token:token
                }
                $.post(url,data,function(response) {
                    notify('success',response.success)
                });
            });

        })(jQuery);
    </script>
@endpush