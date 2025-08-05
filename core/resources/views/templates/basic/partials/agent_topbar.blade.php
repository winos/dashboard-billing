@php
    $agent = agent();
@endphp
<div class="dashboard-top-nav">
    <div class="row align-items-center">
        <div class="col-2">
            <button class="sidebar-open-btn"><i class="las la-bars"></i></button>
        </div>
        <div class="col-10">
          <div class="d-flex flex-wrap justify-content-end align-items-center">
            <ul class="header-top-menu">
              <li><a href="{{route('ticket.index')}}">@lang('Support Ticket')</a></li>
            </ul> 
            <div class="header-user">
              <span class="thumb"><img src="{{ getImage(getFilePath('agentProfile') . '/' . @$agent->image, getFileSize('agentProfile')) }}" alt="image"></span>
              <span class="name">{{$agent->username}}</span> 
              <ul class="header-user-menu">
                <li><a href="{{route('agent.profile.setting')}}"><i class="las la-user-circle"></i>@lang('Profile Setting')</a></li>
                <li><a href="{{route('agent.change.password')}}"><i class="las la-cogs"></i> @lang('Change Password')</a></li>
                <li><a href="{{route('agent.twofactor')}}"><i class="las la-bell"></i>@lang('2FA Security')</a></li>
                <li><a href="{{route('agent.logout')}}"><i class="las la-sign-out-alt"></i> @lang('Logout')</a></li>
              </ul>
            </div>
          </div>
        </div>
    </div>
</div>
