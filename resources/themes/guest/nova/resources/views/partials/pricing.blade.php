@php
    $pricing = \Pricing::plansWithFeatures();
    $planTypes = \Modules\AdminPlans\Facades\Plan::getTypes();
@endphp

<section x-data="{ type: {{ array_key_first($planTypes) }} }" class="py-20 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
    <div class="container px-4 mx-auto max-w-7xl">
        
        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl fw-bolder text-gray-900 mb-4">
                {{ __("Pricing") }}
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-10">
                {{ __("Choose an affordable plan packed with top features to engage your audience, create loyalty, and boost sales.") }}
            </p>
            
            {{-- Toggle Buttons - Only show if multiple plan types exist --}}
            @php
                // Count how many plan types actually have plans
                $activePlanTypes = collect($pricing)->filter(fn($plans) => !empty($plans))->count();
            @endphp
            
            @if($activePlanTypes > 1)
                <div class="inline-flex items-center bg-gray-800 rounded-full p-1.5 shadow-lg mb-8">
                    @foreach($planTypes as $typeKey => $typeLabel)
                        @php
                            $savings = strtolower($typeLabel) === 'yearly' ? '(Save 15%)' : '';
                            // Skip if this plan type has no plans
                            if(empty($pricing[$typeKey])) continue;
                        @endphp
                        <button 
                            type="button"
                            class="px-8 py-3 rounded-full font-medium text-base transition-all duration-300 whitespace-nowrap"
                            :class="type == {{ $typeKey }} ? 'bg-indigo-600 text-white shadow-md transform scale-105' : 'text-gray-300 hover:text-white'"
                            x-on:click="type={{ $typeKey }}"
                        >
                            {{ __($typeLabel) }} 
                            @if($savings)
                                <span class="text-sm" :class="type == {{ $typeKey }} ? 'text-indigo-200' : 'text-gray-400'">{{ $savings }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pricing Grid --}}
        @foreach($planTypes as $typeKey => $typeLabel)
            @php
                $plans = $pricing[$typeKey] ?? [];
                if(empty($plans)) continue; // Skip empty plan types
                $isYearly = strtolower($typeLabel) === 'yearly';
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"
                 @if($activePlanTypes > 1)
                    x-show="type == {{ $typeKey }}"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    style="display: none;"
                 @endif
            >

                @foreach($plans as $plan)
                    @php
                        $isFreePlan = $plan['free_plan'];
                        $isFeatured = !empty($plan['featured']);
                        $originalPrice = $isYearly && !$isFreePlan ? round($plan['price'] / 0.85) : 0;
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
                    @endphp

                    <div class="relative">
                        {{-- Card --}}
                        <div class="h-full bg-white rounded-5 transition-all duration-300 hover:shadow-2xl {{ $isFeatured ? 'border-1 border-indigo-500 shadow-xl scale-105 lg:scale-110' : 'border border-gray-200 shadow-lg hover:border-indigo-300' }}">
                            
                            {{-- Featured Badge --}}
                            @if($isFeatured)
                                <div class="absolute left-0 top-4  right-0 d-flex justify-content-center -translate-y-1/2 z-20">
                                    <div class="bg-indigo-600 text-white px-6 py-2 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                                        {{ __('Most Popular') }}
                                    </div>
                                </div>
                            @endif

                            <div class="p-8 lg:p-10">
                                {{-- Plan Name --}}
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold uppercase tracking-wider mb-1 {{ $isFeatured ? 'text-indigo-600' : 'text-green-600' }}">
                                        {{ __($plan['name'] ?? '-') }}
                                    </h3>
                                    <p class="text-gray-600 text-base min-h-[48px]">
                                        {{ __($plan['desc'] ?? '') }}
                                    </p>
                                </div>

                                {{-- Price --}}
                                <div class="mb-8">
                                    @if($isYearly && !$isFreePlan && $originalPrice > 0)
                                        <div class="mb-2">
                                            <span class="text-2xl text-gray-400 line-through font-medium">
                                                {{ price($originalPrice) }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-baseline mb-2">
                                        @if($isFreePlan)
                                            <span class="text-6xl font-bold text-gray-900">{{ price(0) }}</span>
                                        @else
                                            <span class="text-6xl font-bold text-gray-900">{{ price($plan['price'] ?? 0) }}</span>
                                        @endif
                                        <span class="text-xl text-gray-500 ml-2">/mo</span>
                                    </div>
                                    
                                    <p class="text-gray-600 text-sm">
                                        {{ __("Billed") }} {{ __($typeLabel) }}
                                        @if($isYearly && !$isFreePlan)
                                            <span class="text-green-600 font-semibold ml-1">({{ __("Save 15%") }})</span>
                                        @endif
                                    </p>
                                </div>

                                {{-- CTA Button --}}
                                <div class="mb-8">
                                    <a href="{{ url('auth/signup', $plan['id_secure']) }}" 
                                       class="btn btn-primary w-100 border-0 {{ $isFeatured ? 'bg-primary hover:bg-black' : 'bg-black text-white border-2 border-indigo-600 hover:bg-primary hover:text-white shadow-md' }}">
									   <!-- {{ $isFeatured ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg hover:shadow-xl' : 'bg-white text-indigo-600 border-2 border-indigo-600 hover:bg-indigo-600 hover:text-white shadow-md' }} -->
                                        <span class="flex items-center justify-center gap-2">
                                            {{ __("TRY FOR FREE") }}
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </span>
                                    </a>
									<!--<a href="{{ route('payment.index', $plan['id_secure']) }}" 
                                       class="block w-full py-4 px-6 text-center font-bold rounded-xl transition-all duration-300 transform hover:scale-105 {{ $isFeatured ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg hover:shadow-xl' : 'bg-white text-indigo-600 border-2 border-indigo-600 hover:bg-indigo-600 hover:text-white shadow-md' }}">
                                        <span class="flex items-center justify-center gap-2">
                                            {{ __("TRY FOR FREE") }}
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </span>
                                    </a>-->
                                    @if($isFreePlan || (!empty($plan['trial_day']) && $plan['trial_day'] > 0))
                                        <p class="text-center text-sm text-gray-500 mt-3">
                                            {{ __("No credit card required") }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Features List --}}
                                <div class="space-y-3 pt-6">
                                    {{-- Show dynamic numeric values first --}}
                                    @if($maxChannels)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                @if($maxChannels == -1 || $maxChannels > 10000)
                                                    {{ __("Unlimited Social Media Accounts") }}
                                                @else
                                                    {{ number_format($maxChannels) }} {{ __("Social Media Accounts") }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if($maxPosts)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                @if($maxPosts == -1 || $maxPosts > 100000)
                                                    {{ __("Unlimited Posts per Month") }}
                                                @else
                                                    {{ number_format($maxPosts) }} {{ __("Posts per Month") }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if($aiWordCredits)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                @if($aiWordCredits == -1 || $aiWordCredits > 1000000)
                                                    {{ __("Unlimited AI Word Credits") }}
                                                @else
                                                    {{ number_format($aiWordCredits) }} {{ __("AI Word Credits") }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if($maxStorage)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                @if($maxStorage == -1 || $maxStorage > 100000)
                                                    {{ __("Unlimited Storage") }}
                                                @else
                                                    {{ number_format($maxStorage) }} {{ __("MB Storage") }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Add Analytics --}}
                                    @php
                                        $hasAnalytics = $getPermValue('appanalytics');
                                    @endphp
                                    @if($hasAnalytics)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                {{ __("Analytics") }}
                                            </span>
                                        </div>
									@else
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
												<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
											</svg>
                                            <span class="text-base text-gray-900">
                                                {{ __("Analytics") }}
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Add AI Publishing --}}
                                    @php
                                        $hasAIPublishing = $getPermValue('appaipublishing');
                                    @endphp
                                    @if($hasAIPublishing)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                {{ __("AI Publishing") }}
                                            </span>
                                        </div>
									@else
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
												<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
											</svg>
                                            <span class="text-base text-gray-900">
                                                {{ __("AI Publishing") }}
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Show boolean features from features array --}}
                                    @foreach($plan['features'] ?? [] as $feature)
                                        @if($feature['key'] !== 'access_feature')
                                            <div class="flex items-center gap-3">                                                
												@if($feature['check'])
													<svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
													</svg>
												@else
													<svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
													</svg>
												@endif
                                                <span class="text-base text-gray-900">
                                                    {{ __($feature['label']) }}
                                                </span>
                                                
                                                {{-- Info Icon for Subfeatures --}}
                                                @if(!empty($feature['subfeature']))
                                                    <div x-data="{ open: false, timer: null }" class="relative ml-auto flex-shrink-0">
                                                        <button
                                                            @mouseenter="clearTimeout(timer); open = true"
                                                            @mouseleave="timer = setTimeout(() => open = false, 150)"
                                                            type="button"
                                                            class="w-5 h-5 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-indigo-100 hover:text-indigo-600 transition-colors"
                                                        >
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </button>
                                                        
                                                        {{-- Tooltip Popup --}}
                                                        <div
                                                            x-show="open"
                                                            @mouseenter="clearTimeout(timer); open = true"
                                                            @mouseleave="timer = setTimeout(() => open = false, 150)"
                                                            class="absolute left-full top-0 ml-4 z-50 w-80 max-h-96 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-2xl"
                                                            x-transition:enter="transition ease-out duration-200"
                                                            x-transition:enter-start="opacity-0 scale-95"
                                                            x-transition:enter-end="opacity-100 scale-100"
                                                            style="display: none;"
                                                        >
                                                            <div class="p-6">
                                                                @foreach($feature['subfeature'] as $tabGroup)
                                                                    <div class="mb-6 last:mb-0">
                                                                        <h4 class="font-bold text-sm text-gray-900 mb-3">
                                                                            {{ __($tabGroup['tab_name']) }}
                                                                        </h4>
                                                                        <ul class="space-y-2.5">
                                                                            @foreach($tabGroup['items'] as $sub)
                                                                                <li class="flex items-center gap-2.5 text-sm">
                                                                                    @if($sub['check'])
                                                                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                                        </svg>
                                                                                    @else
                                                                                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                                        </svg>
                                                                                    @endif
                                                                                    <span class="text-gray-700">{{ __($sub['label']) }}</span>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        {{-- Payment Providers --}}
        <!--<div class="mt-24 text-center">
            <p class="text-base text-gray-600 font-medium mb-8">
                {{ __("Trusted by secure payment service") }}
            </p>
            <div class="flex flex-wrap items-center justify-center gap-10 lg:gap-12">
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="{{ theme_public_asset('logos/brands/stripe.svg') }}" alt="Stripe">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="{{ theme_public_asset('logos/brands/visa.svg') }}" alt="Visa">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="{{ theme_public_asset('logos/brands/mastercard.svg') }}" alt="Mastercard">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="{{ theme_public_asset('logos/brands/amex.svg') }}" alt="Amex">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="{{ theme_public_asset('logos/brands/paypal.svg') }}" alt="Paypal">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="{{ theme_public_asset('logos/brands/apple-pay.svg') }}" alt="Apple Pay">
                </div>
            </div>
        </div>-->
    </div>
</section>

{{-- Additional Custom Styles --}}
<style>
    /* Prevent layout shift during transitions */
    [x-cloak] { display: none !important; }
    
    /* Custom scrollbar for tooltips */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #c4c4c4;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #a0a0a0;
    }
</style>