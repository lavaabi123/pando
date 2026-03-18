<section class="relative w-screen min-h-screen flex flex-column flex-sm-row items-stretch overflow-hidden bg-white overflow-x-hidden">
    @include("partials/login-screen", ["name" => __("A Social Media Management Platform")])
    <div class="flex content-right flex-col justify-center flex-1 px-8 py-16 bg-white z-10 ">
	
	<div x-data class="min-h-screen overflow-hidden">
    <div class="absolute inset-x-0 -top-3 -z-10 transform-gpu overflow-hidden px-36 blur-3xl" aria-hidden="true">
        <div class="min-h-[100vh] overflow-hidden pt-10 pb-10"
             style="background: 
                radial-gradient(circle at 20% 10%, var(--color-info) -200%, transparent 35%), 
                radial-gradient(circle at 70% 65%, var(--color-success) -200%, transparent 30%);">
        </div>
    </div>

    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="rounded-2xl shadow-xl p-8 text-center bg-base-100/90">

                <!-- Logo/Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 bg-success/20">
                    <i class="fas fa-check-circle text-success text-3xl"></i>
                </div>

                <!-- Title -->
                <h2 class="text-2xl font-bold text-base-content mb-2">
                    Signup successful!
                </h2>

                <!-- Message -->
                <p class="text-base-content mt-2">
                    Please check your email and click the activation link to activate your account.
                </p>

                <!-- Action Button -->
                <div class="mt-8">
                    <a href="{{ url('auth/login') }}"
                       class="w-full btn btn-primary submit-btn">
                        <i class="fa fa-arrow-left mr-2"></i>
                        {{ __("Back to Login") }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</section>