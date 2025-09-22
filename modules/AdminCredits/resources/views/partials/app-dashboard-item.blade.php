@php
    $creditUsageSummary = Credit::getcreditUsageSummary();
    $planName = $user->plan->name ?? __("No Plan");
    $isFreePlan = $planName === __("No Plan");
    $isUnlimited = ($user->expiration_date == -1);

    // Xử lý hiển thị ngày hết hạn
    if ($isUnlimited) {
        $expiresAt = __('Unlimited');
        $expireClass = 'text-success';
        $expireIcon = '<i class="fa-light fa-infinity me-1"></i>';
    } elseif ($user->expiration_date && $user->expiration_date < time()) {
        $expiresAt = __('Expired');
        $expireClass = 'text-danger fw-bold';
        $expireIcon = '<i class="fa-light fa-calendar-xmark me-1"></i>';
    } elseif ($user->expiration_date) {
        $expiresAt = date('j M Y', $user->expiration_date);
        $expireClass = 'text-muted';
        $expireIcon = '<i class="fa-light fa-calendar-clock me-1"></i>';
    } else {
        $expiresAt = __('N/A');
        $expireClass = 'text-muted';
        $expireIcon = '<i class="fa-light fa-calendar me-1"></i>';
    }

    function display_limit($value) {
        return $value == -1 ? __('Unlimited') : number_format($value);
    }
    // Theme + colors (session first, then user, then defaults)
    $theme      = session('theme', optional(auth()->user())->theme ?? 'light');          // 'light' | 'dark'
    $primaryHex = session('primary_color', optional(auth()->user())->primary_color ?? '#7ec476');
    $secHex     = session('sec_color',     optional(auth()->user())->secondary_color ?? '#fd8107');

    $pClass = 'primary-'.ltrim($primaryHex, '#');
    $sClass = 'secondary-'.ltrim($secHex, '#');
@endphp

<div class=" fw-6 text-black fs-22">
				<i class="fa-solid fa-face-smile text-primary"></i> Hi Royalink!
			</div>
<div class="colors d-flex align-items-center gap-1">
    <div class="form-check form-check-inline ml-10">
      <input class="form-check-input" type="checkbox" name="theme_color" id="theme_color_dark" value="dark" {{ $theme === 'dark' ? 'checked' : '' }}>
      <label class="form-check-label" for="theme_color_dark">Dark Theme</label>
    </div>

    <input type="color" id="colorPicker" value="{{ $primaryHex }}" class="colorPicker w-25 h-20" role="button">
    <input type="color" id="colorPicker_sec" value="{{ $secHex }}" class="colorPicker w-25 h-20" role="button">

    <i class="fa-solid fa-undo colorPicker_reset fs-20" data-bs-toggle="tooltip" data-bs-placement="bottom"
       title="Reset" role="button" aria-label="Reset"></i>
  </div>
	<div class="gradient-bg d-flex gap-12 main-services text-center p-5 justify-content-xl-evenly">
		<a class="icons" href="https://itspando.com/post">
			<div class="mb-3">
				{!! file_get_contents(public_path('img/post.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Create Post
			</div>
		</a>
		<a class="icons" href="https://itspando.com/account_manager">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 75%,var(--d-secondary) 25%)">
				{!! file_get_contents(public_path('img/account.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Manage Accounts
			</div>
		</a>
		<a class="icons" href="https://itspando.com/holidays">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 60%,var(--d-secondary) 40%)">
				{!! file_get_contents(public_path('img/calender.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Calender
			</div>
		</a>
		<a class="icons" href="https://itspando.com/reports">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 50%,var(--d-secondary) 50%)">
				{!! file_get_contents(public_path('img/Reports.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Reports
			</div>
		</a>
		<a class="icons" href="https://itspando.com/inbox">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 20%,var(--d-secondary) 80%)">
				{!! file_get_contents(public_path('img/inbox.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Inbox
			</div>
		</a>
		<a class="icons" href="https://itspando.com">
			<div class="mb-3">
				{!! file_get_contents(public_path('img/note.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Notes
			</div>
		</a>
	</div>
	
<div class="row">

    <div class="col-md-8 mb-4">
        <div class="card shadow-sm position-relative overflow-hidden hp-100">
            <div class="card-body py-4 px-5">
                <div class="d-flex flex-wrap align-items-center gap-4 justify-content-between hp-100">
                    <div class="d-flex flex-column flex-grow-1 hp-100 justify-content-between">
                        <div>
                            <div class="d-flex align-items-center mb-2 text-primary-700 fs-13">
                                <i class="fa-light fa-clock me-2"></i>
                                <span>{{ now()->format('j M Y') }}</span>
                            </div>
                            <div class="fw-bold fs-3 mb-1 text-primary-700">
                                {{ __('Welcome, :name', ['name' => $user->fullname ?? __('No User')]) }}
                            </div>
                            <div class="fw-5 text-gray-700 fs-15 mb-3">
                                {{ __("Here's an overview of your recent activity and content.") }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-8 mt-auto flex-wrap">
                            <span class="badge rounded-pill bg-white border border-primary-200 text-primary-700 px-3 py-1 fw-6 fs-13 shadow-sm">
                                <i class="fa-light fa-star me-1"></i>
                                {{ $planName }}
                            </span>
                            <span class="badge rounded-pill bg-white border px-3 py-1 fw-5 fs-13 shadow-sm {{ $expireClass }}">
                                {!! $expireIcon !!} 
                                {{ __('Expires:') }} {{ $expiresAt }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ms-md-4 d-flex align-items-end hp-100">
                        {{-- Add any extra content here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
	    $credit = $creditUsageSummary ?? [];
	    $limit = $credit['limit'] ?? 0;
	    $used = $credit['used'] ?? 0;
	    $remaining = $credit['remaining'] ?? 0;
	    $isUnlimitedCredit = isset($limit) && $limit == -1;
	    $quotaReached = !empty($credit['quota_reached']);
	    $percent = !$isUnlimitedCredit && $limit > 0
	        ? min(100, round($used / $limit * 100, 1))
	        : 100;

	    $percentLabel = $isUnlimitedCredit
	        ? __('Unlimited')
	        : ($quotaReached ? '100%' : ($percent . '%'));

	    if ($isUnlimitedCredit) {
	        $progressBarClass = 'bg-success';
	    } elseif ($quotaReached) {
	        $progressBarClass = 'bg-danger';
	    } elseif ($percent < 60) {
	        $progressBarClass = 'bg-success';
	    } elseif ($percent < 85) {
	        $progressBarClass = 'bg-warning';
	    } else {
	        $progressBarClass = 'bg-danger';
	    }
	@endphp

	<div class="col-md-4 mb-4">
	    <div class="card shadow-sm p-4 hp-100 d-flex flex-column">
	        <div class="d-flex align-items-center mb-3 gap-12">
	            <span class="d-inline-flex align-items-center justify-content-center fs-28 b-r-12 size-50 bg-warning-100 border border-warning-200 text-warning">
	                <i class="fa-light fa-gem"></i>
	            </span>
	            <div>
	                <div class="text-muted fs-14">{{ __('Your Credits') }}</div>
	                <div class="fs-24 fw-bold text-black">
	                    {{ $isUnlimitedCredit ? __('Unlimited') : number_format($limit) }}
	                </div>
	            </div>
	        </div>
	        <div class="flex-fill d-flex flex-column gap-3">
	            <div class="d-flex justify-content-between align-items-center">
	                <span class="text-muted fs-14">{{ __('Used') }}</span>
	                <span class="fw-bold text-danger fs-14">{{ number_format($used) }}</span>
	            </div>
	            <div class="d-flex justify-content-between align-items-center">
	                <span class="text-muted fs-14">{{ __('Remaining') }}</span>
	                <span class="fw-bold text-success fs-14">
	                    {{ $isUnlimitedCredit ? __('Unlimited') : number_format($remaining) }}
	                </span>
	            </div>
	            <div class="my-2">
	                <div class="progress h-14 b-r-10 bg-gray-200">
	                    <div class="progress-bar {{ $progressBarClass }}"
	                         role="progressbar min-w-8 b-r-10"
	                         style="width: {{ $percent }}%;"
	                         aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
	                    </div>
	                </div>
	                <div class="d-flex justify-content-between small mt-1 px-1 fs-12">
	                    <span class="text-muted">{{ $percentLabel }}</span>
	                    <span class="text-muted">
	                        {{ $isUnlimitedCredit ? __('No limit') : __(':left left', ['left' => number_format($remaining)]) }}
	                    </span>
	                </div>
	            </div>
	            @if($quotaReached)
	                <div class="alert alert-danger py-1 px-2 mt-2 small mb-0 d-flex align-items-center">
	                    <i class="fa fa-triangle-exclamation me-1"></i>
	                    <span>{{ $credit['message'] }}</span>
	                </div>
	            @endif
	        </div>
	    </div>
	</div>

</div>

  <script>
  window.routes = {
    setColor: @json(route('profile.set_color')),
    saveTheme: @json(route('settings.save_theme')),
  };
  window.csrfToken = "{{ csrf_token() }}";
  window.themeCssUrl = "{{ theme_public_asset('css/theme.css') }}";
</script>
    <script type="text/javascript" src="{{ theme_public_asset('js/custom.js') }}"></script>
