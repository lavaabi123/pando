<section class="relative w-screen min-h-screen flex flex-column flex-sm-row items-stretch overflow-hidden bg-white overflow-x-hidden">

    @include("partials/login-screen", ["name" => __("A Social Media Management Platform")])

    <div class="flex flex-col justify-center flex-1 px-8 py-16 bg-white z-10">
        <form class="actionForm max-w-md mx-auto w-full space-y-5 px-xl-3" action="{{ module_url('do_forgot_password') }}" method="POST">
        	<div class="">
                <a class="inline-block" href="{{ url('') }}">
                    <img class="h-20" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                </a>
                <h2 class="fs-2 font-bold font-heading tracking-px-n leading-tight">
                    {{ __("Forgot password") }}
                </h2>
            </div>
			<div>
			    <!--<label for="email" class="block text-gray-700 font-semibold mb-2">{{ __("Email Address") }}</label>-->
			    <input type="email" id="email" name="email"
			        class="form-control"
			        placeholder="{{ __('Enter your email') }}" required autofocus>
			</div>

			<div class="mb-3">
                {!! Captcha::render(); !!}
            </div>

			<div class="msg-error mb-4"></div>

			<button type="submit"
			    class="w-full btn btn-primary">
			    {{ __("Submit") }}
			</button>

			<!--<p class="text-center text-base-content/80 pt-4">
			    <a href="{{ url('auth/login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
			        <i class="fa fa-arrow-left mr-1"></i>{{ __("Back to login") }}
			    </a>
			</p>-->
		</form>

    </div>
</section>
