@extends('admin.layouts.app')
@section('panel')

<div class="row">
    <div class="col-md-12">
        <div class="card b-radius--10 ">
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two custom-data-table">
                        <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Plugin For')</th>
                                <th>@lang('Version')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($plugins as $plugin)
                            <tr>
                                <td>{{ __($plugin->name) }}</td>
                                <td>{{ __($plugin->plugin_for) }}</td>
                                <td>v{{ __($plugin->version) }}</td>
                                <td>
                                    @php
                                        echo $plugin->statusBadge;
                                    @endphp
                                </td>
                                <td>
                                    <div class="button--group">
                                        <button type="button" class="btn btn-sm btn-outline--primary ms-1 mb-2 updateBtn"
                                                    data-action="{{ route('admin.plugins.update', $plugin->id) }}"
                                                    data-name="{{ $plugin->name }}"
                                                    data-company_name="{{ $plugin->meta_data->author }}"
                                                    data-description="{{ $plugin->meta_data->description }}"
                                                    >
                                                <i class="la la-pen"></i> @lang('Update')
                                        </button>
                                        @if($plugin->status == 0)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline--success ms-1 mb-2 confirmationBtn"
                                                    data-action="{{ route('admin.plugins.status', $plugin->id) }}"
                                                    data-question="@lang('Are you sure to enable this extension?')">
                                                <i class="la la-eye"></i> @lang('Enable')
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline--danger mb-2 confirmationBtn"
                                            data-action="{{ route('admin.plugins.status', $plugin->id) }}"
                                            data-question="@lang('Are you sure to disable this extension?')">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">@lang('Add Plugin')</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <i class="las la-times"></i>
          </button>
        </div>
        <form action="{{ route('admin.plugins.upload') }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row">
                <div class="form-group col-lg-6">
                    <label>@lang('Envato Username')</label>
                    <input type="text" name="envato_username" class="form-control" required>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Purchase Code')</label>
                    <input type="text" name="purchase_code" class="form-control" required>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Name')</label>
                    <input type="text" name="name" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('The plugin will setup according to this name.')</small>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Company Name')</label>
                    <input type="text" name="company" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('This name will add as plugin author.')</small>
                </div>
                <div class="form-group col-lg-12">
                    <label>@lang('Description')</label>
                    <input type="text" name="description" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('Write a short description here.')</small>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Logo')</label>
                    <input type="file" name="logo" class="form-control" accept=".png, .jpg, .jpeg" required>
                    <small><i class="la la-info-circle"></i> @lang('This logo will appear on the CMS where required.')</small>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Upload Plugin')</label>
                    <input type="file" name="plugin" accept=".zip" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('Upload the plugin\'s file that you\'ve downloaded from envato.')</small>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
          </div>
        </form>
      </div>
    </div>
</div>

<div class="modal fade" id="updateModal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">@lang('Add Plugin')</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <i class="las la-times"></i>
          </button>
        </div>
        <form  method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">

            <div class="alert alert-info d-block" role="alert"> <i class="las la-exclamation-triangle"></i> @lang('You\'ve to upload the plugin zip file also to change below info to your plugin.')</div>

            <div class="row">
                <div class="form-group col-lg-6">
                    <label>@lang('Name')</label>
                    <input type="text" name="name" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('The plugin will setup according to this name.')</small>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Company Name')</label>
                    <input type="text" name="company" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('This name will add as plugin author.')</small>
                </div>
                <div class="form-group col-lg-12">
                    <label>@lang('Description')</label>
                    <input type="text" name="description" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('Write a short description here.')</small>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Logo')</label>
                    <input type="file" name="logo" class="form-control" accept=".png, .jpg, .jpeg" required>
                    <small><i class="la la-info-circle"></i> @lang('This logo will appear on the CMS where required.')</small>
                </div>
                <div class="form-group col-lg-6">
                    <label>@lang('Upload Plugin')</label>
                    <input type="file" name="plugin" accept=".zip" class="form-control" required>
                    <small><i class="la la-info-circle"></i> @lang('Upload the plugin\'s file that you\'ve downloaded from envato.')</small>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
          </div>
        </form>
      </div>
    </div>
</div>

  <x-confirmation-modal />
@endsection
@push('breadcrumb-plugins')
@if(!extension_loaded('zip'))
    <span class="text--danger mx-3"><span class="fw-bold text--danger">PHP-zip</span> Extension is required</span>
    <button class="btn btn-sm btn-outline--primary" disabled data-bs-toggle="modal" data-bs-target="#addModal"><i class="las la-plus"></i> @lang('Add Plugin')</button>
@else
  <button class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="las la-plus"></i> @lang('Add Plugin')</button>
@endif
@endpush

@push('style')
    <style>
        .alert{
            padding: 1rem 1rem !important;
        }
    </style>
@endpush
@push('script')
    <script>
        $('.updateBtn').on('click ',function () {
            let modal = $('#updateModal');
            modal.find('[name=name]').val($(this).data('name'));
            modal.find('[name=company]').val($(this).data('company_name'));
            modal.find('[name=description]').val($(this).data('description'));
            modal.find('form').attr('action',$(this).data('action'));
            modal.modal('show');
        });
    </script>
@endpush
