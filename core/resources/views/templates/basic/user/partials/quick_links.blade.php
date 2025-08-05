@if (module('add_money', $module)->status)
    <div class="col-lg-2 col-sm-3 col-6 text-center">
        <a href="{{ route('user.deposit.index') }}" class="quick-link-card">
            <span class="icon"><i class="las la-credit-card"></i></span>
            <p class="caption">@lang('Add Money')</p>
        </a><!-- quick-link-card end -->
    </div>
@endif

@if (module('money_out', $module)->status)
    <div class="col-lg-2 col-sm-3 col-6 text-center">
        <a href="{{ route('user.money.out') }}" class="quick-link-card">
            <span class="icon"><i class="las la-hand-holding-usd"></i></span>
            <p class="caption">@lang('Money Out')</p>
        </a><!-- quick-link-card end -->
    </div>
@endif

@if (module('make_payment', $module)->status)
    <div class="col-lg-2 col-sm-3 col-6 text-center">
        <a href="{{ route('user.payment') }}" class="quick-link-card">
            <span class="icon"><i class="las la-shopping-bag"></i></span>
            <p class="caption">@lang('Make Payment')</p> 
        </a><!-- quick-link-card end -->
    </div>
@endif

@if (module('money_exchange', $module)->status)
    <div class="col-lg-2 col-sm-3 col-6 text-center">
        <a href="{{ route('user.exchange.money') }}" class="quick-link-card">
            <span class="icon"><i class="las la-exchange-alt"></i></span>
            <p class="caption">@lang('Exchange')</p>
        </a><!-- quick-link-card end -->
    </div>
@endif

@if (module('create_voucher', $module)->status)
    <div class="col-lg-2 col-sm-3 col-6 text-center">
        <a href="{{ route('user.voucher.create') }}" class="quick-link-card">
            <span class="icon"><i class="las la-share-square"></i></span>
            <p class="caption">@lang('Create Voucher')</p>
        </a><!-- quick-link-card end -->
    </div>
@endif

@if (module('create_invoice', $module)->status)
    <div class="col-lg-2 col-sm-3 col-6 text-center">
        <a href="{{ route('user.invoice.all') }}" class="quick-link-card">
            <span class="icon"><i class="las la-receipt"></i></span>
            <p class="caption">@lang('Invoice')</p>
        </a><!-- quick-link-card end -->
    </div>
@endif
