<ul class="nav nav-tabs mb-4 topTap breadcrumb-nav" role="tablist">
    <button class="breadcrumb-nav-close"><i class="las la-times"></i></button>
    <li class="nav-item {{ menuActive('admin.kyc.setting', param:'user_kyc') }}" role="presentation">
        <a href="{{ route('admin.kyc.setting', 'user_kyc') }}" class="nav-link text-dark" type="button">
            <i class="las la-user"></i> @lang('User KYC')
        </a>
    </li>
    <li class="nav-item {{ menuActive('admin.kyc.setting', param:'agent_kyc') }}" role="presentation">
        <a href="{{ route('admin.kyc.setting', 'agent_kyc') }}" class="nav-link text-dark" type="button">
            <i class="las la-user-secret"></i> @lang('Agent KYC')
        </a>
    </li>
    <li class="nav-item {{ menuActive('admin.kyc.setting', param:'merchant_kyc') }}" role="presentation">
        <a href="{{ route('admin.kyc.setting', 'merchant_kyc') }}" class="nav-link text-dark" type="button">
            <i class="las la-user-tie"></i> @lang('Merchant KYC')
        </a>
    </li>
</ul>
