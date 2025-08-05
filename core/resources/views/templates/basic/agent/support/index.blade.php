@extends($activeTemplate.'layouts.agent_master')

@section('content')
<div class="custom--card mt-5">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-lg-6 col-lg-6 col-md-6 col-sm-6">
                <h6>@lang($pageTitle)</h6>
            </div>
            <div class="col-lg-6 col-md-6 text-md-end text-sm-end col-sm-6 mt-2">
                <a href="{{ route('ticket.open') }}" class="btn btn--base btn-sm">
                    <i class="las la-plus"></i>
                    @lang('Add New')
                </a>
            </div>
        </div>
        <div class="table-responsive--sm">
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Subject')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Priority')</th>
                        <th>@lang('Last Reply')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supports as $key => $support)
                        <tr>
                            <td> 
                                <a href="{{ route('ticket.view', $support->ticket) }}" class="fw-bold">
                                    [@lang('Ticket')#{{ $support->ticket }}] {{ __($support->subject) }}
                                </a>
                            </td>
                            <td>
                                @php echo $support->statusBadge; @endphp
                            </td>
                            <td>
                                @if ($support->priority == Status::PRIORITY_LOW)
                                    <span class="badge badge--dark">@lang('Low')</span>
                                @elseif($support->priority == Status::PRIORITY_MEDIUM)
                                    <span class="badge badge--success">@lang('Medium')</span>
                                @elseif($support->priority == Status::PRIORITY_HIGH)
                                    <span class="badge badge--primary">@lang('High')</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($support->last_reply)->diffForHumans() }} </td>

                            <td>
                                <a href="{{ route('ticket.view', $support->ticket) }}" class="btn btn--dark btn-sm">
                                    <i class="fa fa-desktop"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center not-found">{{ __($emptyMessage) }}</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        @if($supports->hasPages())
            <div class="pt-4 pb-2">
                {{ paginatelinks($supports) }}
            </div>
        @endif
    </div>
</div><!-- custom--card end -->
@endsection
