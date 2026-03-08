@php
    $plan = $user->plan;
    if($plan){
        $plan_detail = \Pricing::plansWithFeatures($plan->id);
    }

    $pricing = \Pricing::plansWithFeatures();
    $planTypes = \Modules\AdminPlans\Facades\Plan::getTypes();

    $expired = false;
    if ($user->expiration_date && $user->expiration_date > 0) {
        $expired = $user->expiration_date < time();
    }

    $credit_summary = Credit::getCreditUsageSummary();
@endphp

@extends('layouts.app')

@section('content')
    @include("appprofile::partials.profile-header")
    
    <div class="container py-4 pricing">

        @include("components.main-message")

        <div class="mb-2">
            <x-sub-header
                title="{{ __('Subscription Plan') }}"
                description="{{ __('Manage your plan. Upgrade for more features!') }}"
            />
        </div>

        <div class="card shadow-sm rounded-4 mb-5 mx-auto border-2 bg-gray-100">
            <div class="card-body p-0">
                <div class="d-flex flex-column flex-md-row">
                    <!-- Left: Plan Info -->
                    <div class="flex-fill border-end-md mb-4 mb-md-0">
                        <div class="border-bottom fw-6 fs-16 p-3">
                            {{ __("Your Plan") }}
                        </div>
                        <div class="p-4 fs-14">
							<div class="d-flex gap-10">
								<div class="size-50 d-flex align-items-center bg-gray-100 border border-gray-200 fs-25 justify-content-center b-r-15">
									<i class="fa-light fa-user-crown text-warning"></i>
								</div>
								<div class="d-flex flex-column gap-1">
									<div class="fs-20"><b>{{ $plan->name ?? __("No Plan") }}</b>
										@if($plan && $plan->free_plan)
											<span class="badge badge-outline badge-light badge-pill badge-sm position-relative t--5">
												{{ __("Free Plan") }}
											</span>
										@endif
									</div>
									<div class="mb-2">
										{{ __('Expiration date :') }}
										<b class="{{ $expired ? 'text-danger' : 'text-success' }}">
											@if($user->expiration_date == -1)
												{{ __('Unlimited') }}
											@elseif($user->expiration_date)
												{{ date_show($user->expiration_date) }}
											@else
												{{ __('N/A') }}
											@endif
										</b>
									</div>
								</div>
							</div>
                            <div class="mt-4 mb-1 d-flex justify-content-between align-items-center">
                                <span class="fs-12">{{ __('Credits used') }}</span>
                                <span class="small fw-bold text-primary">{{ $credit_summary['progress_label'] }}</span>
                            </div>
                            <div class="progress wp-100 h-10" style="background: #eee">
                                <div class="progress-bar {{ $credit_summary['is_unlimited'] ? 'bg-success' : 'bg-dark' }}"
                                     style="width: {{ $credit_summary['progress_value'] }}%;">
                                </div>
                            </div>
                            <div class="small mt-1">
                                {{ __('Used:') }} <b>{{ number_format($credit_summary['used']) }}</b>
                                {{ $credit_summary['is_unlimited'] ? '' : '/ ' . number_format($credit_summary['limit']) }}
                                ({{ __('Left:') }} <b>{{ $credit_summary['is_unlimited'] ? __('Unlimited') : number_format($credit_summary['remaining']) }}</b>)
                            </div>
                            @if($credit_summary['quota_reached'])
                                <div class="text-danger small mt-1">{{ $credit_summary['message'] }}</div>
                            @endif

                            
                            <div class="d-flex gap-10 mt-4 flex-wrap">
                                @if($plan && Plan::hasSubscription())
                                <a href="{{ route("payment.cancel_subscription") }}" class="btn btn-outline btn-danger btn-md actionItem b-r-20" data-confirm="{{ __("Are you sure you want to cancel your subscription?") }}" data-redirect="">{{ __("Cancel Subscription") }}</a>
                                @else
                                    @if($plan && !$plan->free_plan)
                                    <a href="#pricingTabContent" class="btn btn-warning btn-md">{{ __("Upgrade Plan") }}</a>
                                    <a href="{{ route('payment.index', $plan->id_secure) }}" class="btn btn-dark btn-md">{{ __("Renew Plan") }}</a>
                                    @endif
                                @endif
                            </div>
                            
                        </div>
                    </div>
                   <div class="flex-grow-0">
    <div class="border-bottom fw-6 fs-16 p-3">
        {{ __("Plan Permissions") }}
    </div>
    <div class="p-4">
        @if(!empty($plan_detail))
            @php
		
                $permissions = $plan_detail['permissions'] ?? [];
                
                // Helper function to get permission value
                $getPermValue = function($key) use ($permissions) {
                    $perm = collect($permissions)->firstWhere('key', $key);
                    return $perm['value'] ?? null;
                };
                
                // Get key values
                $maxChannels = $getPermValue('max_channels');
                $maxPosts = $getPermValue('apppublishing.max_post');
                $aiWordCredits = $getPermValue('ai_word_credits');
                $maxStorage = $getPermValue('appfiles.max_storage');
                $hasAnalytics = $getPermValue('appanalytics');
                $hasAIPublishing = $getPermValue('appaipublishing');
                $team_members_limit = $getPermValue('team_members') ?? 0;
            @endphp

            <ul class="list-unstyled with-mark fw-6">
                {{-- Social Media Accounts --}}
                @if($maxChannels)
                    <li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        @if($maxChannels == -1 || $maxChannels > 10000)
                            {{ __("Unlimited Social Media Accounts") }}
                        @else
                            {{ number_format($maxChannels) }} {{ __("Social Media Accounts") }}
                        @endif
                    </li>
                @endif

                {{-- Posts per Month --}}
                @if($maxPosts)
                    <li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        @if($maxPosts == -1 || $maxPosts > 100000)
                            {{ __("Unlimited Posts per Month") }}
                        @else
                            {{ number_format($maxPosts) }} {{ __("Posts per Month") }}
                        @endif
                    </li>
                @endif

                {{-- AI Word Credits --}}
                @if($aiWordCredits)
                    <li class="mb-2 d-flex align-items-center gap-1 d-none">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        @if($aiWordCredits == -1 || $aiWordCredits > 1000000)
                            {{ __("Unlimited AI Word Credits") }}
                        @else
                            {{ number_format($aiWordCredits) }} {{ __("AI Word Credits") }}
                        @endif
                    </li>
                @endif

                {{-- Storage --}}
                @if($maxStorage)
                    <li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        @if($maxStorage == -1 || $maxStorage > 100000)
                            {{ __("Unlimited Storage") }}
                        @else
                            {{ number_format($maxStorage) }} {{ __("MB Storage") }}
                        @endif
                    </li>
                @endif

                {{-- Analytics --}}
                @if($hasAnalytics)
                    <li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        {{ __("Analytics") }}
                    </li>
				@else
					<li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-danger">
                            <i class="fa-regular fa-xmark"></i>
                        </span>
                        {{ __("Analytics") }}
                    </li>
                @endif

                {{-- AI Publishing --}}
                @if($hasAIPublishing)
                    <li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                            <i class="fa-regular fa-check"></i>
                        </span>
                        {{ __("AI Publishing") }}
                    </li>
				@else
					<li class="mb-2 d-flex align-items-center gap-1">
                        <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-danger">
                            <i class="fa-regular fa-xmark"></i>
                        </span>
                        {{ __("AI Publishing") }}
                    </li>
                @endif

                {{-- Show other boolean features --}}
               @php
                    /*echo $team_members_limit;
                    echo "<pre>";
                    print_r($plan_detail['features']);
                    echo "</pre>";*/
                @endphp

                @foreach($plan_detail['features']??[] as $feature)                
                    @if($feature['key'] !== 'access_feature')
                        <li class="mb-2 d-flex align-items-center gap-1">
                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 {{ $feature['check'] ? 'text-success' : 'text-danger' }}">
                                <i class="fa-regular fa-{{ $feature['check'] ? 'check' : 'xmark' }}"></i>
                            </span>                            
                            {{ __($feature['label']) }}@if($feature['key'] === 'appteams'): ({{ $team_members_limit === -1 ? __('Unlimited') : $team_members_limit }})@endif
                            {{-- Popup info icon for subfeatures --}}
                            @if(!empty($feature['subfeature']))
                                <div class="feature-popup-wrapper position-relative ms-1 d-inline-block">
                                    <span class="info-hover-icon" tabindex="0">
                                        <i class="fa fa-info-circle text-primary" style="cursor:pointer;"></i>
                                    </span>
                                    <div class="features-popup shadow-lg">
                                        @foreach($feature['subfeature'] as $group)
                                            <div class="fw-bold mb-1 small px-3 pt-2">
                                               test1 {{ __($group['tab_name'] ?? '') }}
                                            </div>
                                            <ul class="list-unstyled with-mark mb-2 px-3">
                                                @foreach($group['items'] as $item)
                                                    <li class="d-flex align-items-center">
                                                       test2 <span class="d-flex align-items-center justify-content-center fs-13 me-2 {{ $feature['check'] ? 'text-success' : 'text-gray-600' }}">
                                                            <i class="fa-regular fa-{{$feature['check'] ? 'check' : 'xmark' }}"></i>
                                                        </span>
                                                        {{ __($item['label']) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        @else
            <div class="text-center py-5">
                <span class="d-inline-block mb-2 fs-70">
                    <i class="fa fa-box-open text-primary"></i>
                </span>
                <div class="fw-semibold text-gray-700" style="opacity:.85">
                    {{ __("No features available for this plan.") }}
                </div>
            </div>
        @endif
    </div>
</div>
			   </div>
            </div>
        </div>


        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2 fs-35">{{ __('Choose a plan that suits you. Grow business fast.') }}</h2>
            <p class="mx-auto text-lg text-muted max-w-500">
                {{ __('Choose an affordable plan packed with top features to engage your audience, create loyalty, and boost sales.') }}
            </p>
        </div>

        <div class="d-flex mx-auto justify-content-center mb-0">
            <!--<ul class="nav nav-tabs justify-content-center mb-4 gap-0 b-r-20 border border-gray-300 overflow-hidden" id="pricingTab" role="tablist">
                @foreach($planTypes as $typeKey => $typeLabel)
                    <li class="nav-item {{ $typeKey==2?"border-start border-end border-gray-300":"" }}" role="presentation">
                        <button
                            class="nav-link px-4 border-bottom-0 py-2 fw-bold bg-active-gray-200 text-active-gray-800 @if($loop->first) active @endif"
                            id="tab-{{ $typeKey }}"
                            data-bs-toggle="tab"
                            data-bs-target="#content-{{ $typeKey }}"
                            type="button"
                            role="tab"
                            aria-controls="content-{{ $typeKey }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        >
                            {{ __($typeLabel) }}
                        </button>
                    </li>
                @endforeach
            </ul>-->
        </div>

       {{-- Plans content --}}
<div class="tab-content" id="pricingTabContent">
    @foreach($planTypes as $typeKey => $typeLabel)
        <div class="tab-pane fade @if($loop->first) show active @endif" 
            id="content-{{ $typeKey }}" 
            role="tabpanel"
            aria-labelledby="tab-{{ $typeKey }}">
            <div class="row justify-content-center gy-4">
                @forelse($pricing[$typeKey] ?? [] as $plan)
                    @php
                        $isCurrentPlan = isset($user) && $user->plan_id == ($plan['id'] ?? null);
                        $isFreePlan = $plan['free_plan'];
                        $isFeatured = !empty($plan['featured']);
                        $permissions = $plan['permissions'] ?? [];
                        
                        // Helper function to get permission value
                        $getPermValue = function($key) use ($permissions) {
                            $perm = collect($permissions)->firstWhere('key', $key);
                            return $perm['value'] ?? null;
                        };
                        
                        // Get key values
                        $maxChannels = $getPermValue('max_channels');
                        $maxPosts = $getPermValue('apppublishing.max_post');
                        $aiWordCredits = $getPermValue('ai_word_credits');
                        $maxStorage = $getPermValue('appfiles.max_storage');
                        $hasAnalytics = $getPermValue('appanalytics');
                        $hasAIPublishing = $getPermValue('appaipublishing');
                        $team_members_limit = $getPermValue('team_members') ?? 0;
                    @endphp

                    <div class="col-md-3">
                        <div class="card pricing-card hp-100 text-center border-2 bg-gray-100 shadow-sm {{ $isFeatured ? 'border-2 border-primary' : '' }}">
                            <div class="card-body py-4 position-relative">
                                @if($isFeatured)
									<div class="position-absolute top-0 end-0 start-0 translate-middle-y">
										<span class="bg-primary text-uppercase text-white fw-6 px-3 py-1 b-r-20 fs-12">
											{{ __('Featured') }}
										</span>
									</div>
                                @endif
                                
                                <span class="text-uppercase fw-bold text-primary mb-2 d-block" style="letter-spacing:1px;">
                                    {{ __($plan['name'] ?? '-') }}
                                </span>

                                <h2 class="fw-bold mb-0 mt-2 fs-35">
                                    @if($isFreePlan)
                                        {{ price(0) }}
                                        <small class="fs-14 text-muted">/{{ strtolower(__($typeLabel)) }}</small>
                                    @else
                                        {{ price($plan['price'] ?? 0) }}
                                        <small class="fs-14 text-muted">/{{ strtolower(__($typeLabel)) }}</small>
                                    @endif
                                </h2>
                                <div class="mb-2 text-muted mb-4">{{ $plan['desc'] ?? '' }}</div>
                                
                                <ul class="list-unstyled with-mark mb-4 max-w-240">
                                    {{-- Social Media Accounts --}}
                                    @if($maxChannels)
                                        <li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-check"></i>
                                            </span>
                                            <span class="fs-14">
                                                @if($maxChannels == -1 || $maxChannels > 10000)
                                                    {{ __("Unlimited Social Media Accounts") }}
                                                @else
                                                    {{ number_format($maxChannels) }} {{ __("Social Media Accounts") }}
                                                @endif
                                            </span>
                                        </li>
                                    @endif

                                    {{-- Posts per Month --}}
                                    @if($maxPosts)
                                        <li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-check"></i>
                                            </span>
                                            <span class="fs-14">
                                                @if($maxPosts == -1 || $maxPosts > 100000)
                                                    {{ __("Unlimited Posts per Month") }}
                                                @else
                                                    {{ number_format($maxPosts) }} {{ __("Posts per Month") }}
                                                @endif
                                            </span>
                                        </li>
                                    @endif

                                    {{-- AI Word Credits --}}
                                    @if($aiWordCredits)
                                        <li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-check"></i>
                                            </span>
                                            <span class="fs-14">
                                                @if($aiWordCredits == -1 || $aiWordCredits > 1000000)
                                                    {{ __("Unlimited AI Word Credits") }}
                                                @else
                                                    {{ number_format($aiWordCredits) }} {{ __("AI Word Credits") }}
                                                @endif
                                            </span>
                                        </li>
                                    @endif

                                    {{-- Storage --}}
                                    @if($maxStorage)
                                        <li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-check"></i>
                                            </span>
                                            <span class="fs-14">
                                                @if($maxStorage == -1 || $maxStorage > 100000)
                                                    {{ __("Unlimited Storage") }}
                                                @else
                                                    {{ number_format($maxStorage) }} {{ __("MB Storage") }}
                                                @endif
                                            </span>
                                        </li>
                                    @endif

                                    {{-- Analytics --}}
                                    @if($hasAnalytics)
                                        <li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-check"></i>
                                            </span>
                                            <span class="fs-14">{{ __("Analytics") }}</span>
                                        </li>
									@else
										<li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-xmark"></i>
                                            </span>
                                            <span class="fs-14">{{ __("Analytics") }}</span>
                                        </li>
                                    @endif

                                    {{-- AI Publishing --}}
                                    @if($hasAIPublishing)
                                        <li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-check"></i>
                                            </span>
                                            <span class="fs-14">{{ __("AI Publishing") }}</span>
                                        </li>
									@else
										<li class="mb-2 d-flex align-items-center gap-1">
                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 text-success">
                                                <i class="fa-regular fa-xmark"></i>
                                            </span>
                                            <span class="fs-14">{{ __("AI Publishing") }}</span>
                                        </li>
                                    @endif

                                    {{-- Show other boolean features --}}
                                    @foreach($plan['features'] as $feature)
                                        @if($feature['key'] !== 'access_feature')
                                            <li class="mb-2 d-flex align-items-center gap-1">
                                                <span class="d-flex align-items-center justify-content-center fs-13 me-2 {{ $feature['check'] ? 'text-success' : 'text-danger' }}">
                                                    <i class="fa-regular fa-{{ $feature['check'] ? 'check' : 'xmark' }}"></i>
                                                </span>

                                                <span class="fs-14">                                                
                                                {{ __($feature['label']) }}@if($feature['key'] === 'appteams'): ({{ $team_members_limit === -1 ? __('Unlimited') : $team_members_limit }})@endif
                                                </span>
                                                
                                                {{-- Popup info icon --}}
                                                @if(!empty($feature['subfeature']))
                                                    <div class="feature-popup-wrapper position-relative ms-1 d-inline-block">
                                                        <span class="info-hover-icon" tabindex="0">
                                                            <i class="fa fa-info-circle text-primary" style="cursor:pointer;"></i>
                                                        </span>
                                                        <div class="features-popup shadow-lg">
                                                            @foreach($feature['subfeature'] as $group)
                                                                <div class="fw-bold mb-1 fs-12 px-3 pt-2">
                                                                    {{ __($group['tab_name'] ?? '') }}
                                                                </div>
                                                                <ul class="list-unstyled with-mark mb-2 px-3">
                                                                    @foreach($group['items'] as $item)
                                                                        <li class="d-flex align-items-center">
                                                                            <span class="d-flex align-items-center justify-content-center fs-13 me-2 {{ $feature['check'] ? 'text-success' : 'text-gray-600' }}">
                                                                                <i class="fa-regular fa-{{$feature['check'] ? 'check' : 'xmark' }}"></i>
                                                                            </span>
                                                                            <span class="text-gray-700 fs-14">{{ __($item['label']) }}</span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>

                                @if($isCurrentPlan)
                                    <button class="btn btn-outline-secondary text-gray-700 border-gray-300 btn-lg w-100 rounded-5" disabled>
                                        {{ __("Current Plan") }}
                                    </button>
                                @elseif($isFreePlan)
                                    <a href="{{ route('plan.activate', $plan['id_secure']) }}" data-confirm="{{ __("Are you sure you want to switch to this plan?") }}" class="btn btn-light btn-lg w-100 rounded-5 actionItem" data-redirect="">
                                        {{ __("Start for Free") }}
                                    </a>
                                @else
                                    <a href="{{ route('payment.index', $plan['id_secure']) }}" class="btn btn-dark btn-lg w-100 rounded-5">
                                        {{ __("Choose Plan") }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">{{ __('No plans available.') }}</div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

 </div>
@endsection
