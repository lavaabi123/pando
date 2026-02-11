<?php
    $pricing = \Pricing::plansWithFeatures();
    $planTypes = \Modules\AdminPlans\Facades\Plan::getTypes();
?>

<section x-data="{ type: <?php echo e(array_key_first($planTypes)); ?> }" class="py-20 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
    <div class="container px-4 mx-auto max-w-7xl">
        
        
        <div class="text-center mb-12">
            <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-4">
                <?php echo e(__("Pricing")); ?>

            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-10">
                <?php echo e(__("Choose an affordable plan packed with top features to engage your audience, create loyalty, and boost sales.")); ?>

            </p>
            
            
            <?php
                // Count how many plan types actually have plans
                $activePlanTypes = collect($pricing)->filter(fn($plans) => !empty($plans))->count();
            ?>
            
            <?php if($activePlanTypes > 1): ?>
                <div class="inline-flex items-center bg-gray-800 rounded-full p-1.5 shadow-lg mb-8">
                    <?php $__currentLoopData = $planTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $typeLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $savings = strtolower($typeLabel) === 'yearly' ? '(Save 15%)' : '';
                            // Skip if this plan type has no plans
                            if(empty($pricing[$typeKey])) continue;
                        ?>
                        <button 
                            type="button"
                            class="px-8 py-3 rounded-full font-medium text-base transition-all duration-300 whitespace-nowrap"
                            :class="type == <?php echo e($typeKey); ?> ? 'bg-indigo-600 text-white shadow-md transform scale-105' : 'text-gray-300 hover:text-white'"
                            x-on:click="type=<?php echo e($typeKey); ?>"
                        >
                            <?php echo e(__($typeLabel)); ?> 
                            <?php if($savings): ?>
                                <span class="text-sm" :class="type == <?php echo e($typeKey); ?> ? 'text-indigo-200' : 'text-gray-400'"><?php echo e($savings); ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        
        <?php $__currentLoopData = $planTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $typeLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $plans = $pricing[$typeKey] ?? [];
                if(empty($plans)) continue; // Skip empty plan types
                $isYearly = strtolower($typeLabel) === 'yearly';
            ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"
                 <?php if($activePlanTypes > 1): ?>
                    x-show="type == <?php echo e($typeKey); ?>"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    style="display: none;"
                 <?php endif; ?>
            >

                <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
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
                    ?>

                    <div class="relative">
                        
                        <div class="h-full bg-white rounded-2xl transition-all duration-300 hover:shadow-2xl <?php echo e($isFeatured ? 'border-2 border-indigo-500 shadow-xl scale-105 lg:scale-110' : 'border border-gray-200 shadow-lg hover:border-indigo-300'); ?>">
                            
                            
                            <?php if($isFeatured): ?>
                                <div class="absolute -top-5 left-1/2 -translate-x-1/2 z-20">
                                    <div class="bg-indigo-600 text-white px-6 py-2 rounded-full text-sm font-bold uppercase tracking-wider shadow-lg">
                                        <?php echo e(__('Most Popular')); ?>

                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="p-8 lg:p-10">
                                
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold uppercase tracking-wider mb-3 <?php echo e($isFeatured ? 'text-indigo-600' : 'text-green-600'); ?>">
                                        <?php echo e(__($plan['name'] ?? '-')); ?>

                                    </h3>
                                    <p class="text-gray-600 text-base min-h-[48px]">
                                        <?php echo e(__($plan['desc'] ?? '')); ?>

                                    </p>
                                </div>

                                
                                <div class="mb-8">
                                    <?php if($isYearly && !$isFreePlan && $originalPrice > 0): ?>
                                        <div class="mb-2">
                                            <span class="text-2xl text-gray-400 line-through font-medium">
                                                <?php echo e(price($originalPrice)); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-baseline mb-2">
                                        <?php if($isFreePlan): ?>
                                            <span class="text-6xl font-bold text-gray-900"><?php echo e(price(0)); ?></span>
                                        <?php else: ?>
                                            <span class="text-6xl font-bold text-gray-900"><?php echo e(price($plan['price'] ?? 0)); ?></span>
                                        <?php endif; ?>
                                        <span class="text-xl text-gray-500 ml-2">/mo</span>
                                    </div>
                                    
                                    <p class="text-gray-600 text-sm">
                                        <?php echo e(__("Billed")); ?> <?php echo e(__($typeLabel)); ?>

                                        <?php if($isYearly && !$isFreePlan): ?>
                                            <span class="text-green-600 font-semibold ml-1">(<?php echo e(__("Save 15%")); ?>)</span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                
                                <div class="mb-8">
                                    <a href="<?php echo e(url('auth/signup', $plan['id_secure'])); ?>" 
                                       class="block w-full py-4 px-6 text-center font-bold rounded-xl transition-all duration-300 transform hover:scale-105 <?php echo e($isFeatured ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg hover:shadow-xl' : 'bg-white text-indigo-600 border-2 border-indigo-600 hover:bg-indigo-600 hover:text-white shadow-md'); ?>">
                                        <span class="flex items-center justify-center gap-2">
                                            <?php echo e(__("TRY FOR FREE")); ?>

                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </span>
                                    </a>
									<!--<a href="<?php echo e(route('payment.index', $plan['id_secure'])); ?>" 
                                       class="block w-full py-4 px-6 text-center font-bold rounded-xl transition-all duration-300 transform hover:scale-105 <?php echo e($isFeatured ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg hover:shadow-xl' : 'bg-white text-indigo-600 border-2 border-indigo-600 hover:bg-indigo-600 hover:text-white shadow-md'); ?>">
                                        <span class="flex items-center justify-center gap-2">
                                            <?php echo e(__("TRY FOR FREE")); ?>

                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </span>
                                    </a>-->
                                    <?php if($isFreePlan || (!empty($plan['trial_day']) && $plan['trial_day'] > 0)): ?>
                                        <p class="text-center text-sm text-gray-500 mt-3">
                                            <?php echo e(__("No credit card required")); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="space-y-3 pt-6">
                                    
                                    <?php if($maxChannels): ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                <?php if($maxChannels == -1 || $maxChannels > 10000): ?>
                                                    <?php echo e(__("Unlimited Social Media Accounts")); ?>

                                                <?php else: ?>
                                                    <?php echo e(number_format($maxChannels)); ?> <?php echo e(__("Social Media Accounts")); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($maxPosts): ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                <?php if($maxPosts == -1 || $maxPosts > 100000): ?>
                                                    <?php echo e(__("Unlimited Posts per Month")); ?>

                                                <?php else: ?>
                                                    <?php echo e(number_format($maxPosts)); ?> <?php echo e(__("Posts per Month")); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($aiWordCredits): ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                <?php if($aiWordCredits == -1 || $aiWordCredits > 1000000): ?>
                                                    <?php echo e(__("Unlimited AI Word Credits")); ?>

                                                <?php else: ?>
                                                    <?php echo e(number_format($aiWordCredits)); ?> <?php echo e(__("AI Word Credits")); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($maxStorage): ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                <?php if($maxStorage == -1 || $maxStorage > 100000): ?>
                                                    <?php echo e(__("Unlimited Storage")); ?>

                                                <?php else: ?>
                                                    <?php echo e(number_format($maxStorage)); ?> <?php echo e(__("MB Storage")); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    
                                    <?php
                                        $hasAnalytics = $getPermValue('appanalytics');
                                    ?>
                                    <?php if($hasAnalytics): ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                <?php echo e(__("Analytics")); ?>

                                            </span>
                                        </div>
									<?php else: ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
												<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
											</svg>
                                            <span class="text-base text-gray-900">
                                                <?php echo e(__("Analytics")); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    
                                    <?php
                                        $hasAIPublishing = $getPermValue('appaipublishing');
                                    ?>
                                    <?php if($hasAIPublishing): ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-base text-gray-900">
                                                <?php echo e(__("AI Publishing")); ?>

                                            </span>
                                        </div>
									<?php else: ?>
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
												<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
											</svg>
                                            <span class="text-base text-gray-900">
                                                <?php echo e(__("AI Publishing")); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    
                                    <?php $__currentLoopData = $plan['features'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($feature['key'] !== 'access_feature'): ?>
                                            <div class="flex items-center gap-3">                                                
												<?php if($feature['check']): ?>
													<svg class="w-5 h-5 text-gray-900 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
													</svg>
												<?php else: ?>
													<svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
													</svg>
												<?php endif; ?>
                                                <span class="text-base text-gray-900">
                                                    <?php echo e(__($feature['label'])); ?>

                                                </span>
                                                
                                                
                                                <?php if(!empty($feature['subfeature'])): ?>
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
                                                                <?php $__currentLoopData = $feature['subfeature']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <div class="mb-6 last:mb-0">
                                                                        <h4 class="font-bold text-sm text-gray-900 mb-3">
                                                                            <?php echo e(__($tabGroup['tab_name'])); ?>

                                                                        </h4>
                                                                        <ul class="space-y-2.5">
                                                                            <?php $__currentLoopData = $tabGroup['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <li class="flex items-center gap-2.5 text-sm">
                                                                                    <?php if($sub['check']): ?>
                                                                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                                        </svg>
                                                                                    <?php else: ?>
                                                                                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                                        </svg>
                                                                                    <?php endif; ?>
                                                                                    <span class="text-gray-700"><?php echo e(__($sub['label'])); ?></span>
                                                                                </li>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </ul>
                                                                    </div>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <!--<div class="mt-24 text-center">
            <p class="text-base text-gray-600 font-medium mb-8">
                <?php echo e(__("Trusted by secure payment service")); ?>

            </p>
            <div class="flex flex-wrap items-center justify-center gap-10 lg:gap-12">
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="<?php echo e(theme_public_asset('logos/brands/stripe.svg')); ?>" alt="Stripe">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="<?php echo e(theme_public_asset('logos/brands/visa.svg')); ?>" alt="Visa">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="<?php echo e(theme_public_asset('logos/brands/mastercard.svg')); ?>" alt="Mastercard">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="<?php echo e(theme_public_asset('logos/brands/amex.svg')); ?>" alt="Amex">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="<?php echo e(theme_public_asset('logos/brands/paypal.svg')); ?>" alt="Paypal">
                </div>
                <div class="transition-all duration-300 hover:scale-110">
                    <img class="h-14 opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all" src="<?php echo e(theme_public_asset('logos/brands/apple-pay.svg')); ?>" alt="Apple Pay">
                </div>
            </div>
        </div>-->
    </div>
</section>


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
</style><?php /**PATH C:\xampp82\htdocs\pando-laravel\resources\themes\guest\nova\resources\views/partials/pricing.blade.php ENDPATH**/ ?>