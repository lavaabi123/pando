<section class="relative w-screen min-h-screen flex flex-column flex-sm-row items-stretch overflow-hidden bg-white overflow-x-hidden">

    @include("partials/login-screen", ["name" => __("A Social Media Management Platform")])

    <div class="flex content-right flex-col justify-center flex-1 px-8 py-16 bg-white z-10 ">
        <form class="actionForm max-w-md mx-auto w-full" action="{{ module_url('do_login') }}" method="POST" data-loading="1">
            <!--<div class="show-on-mobile">
                <a class="mb-4 inline-block" href="{{ url('') }}">
                    <img class="h-10" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logos.png')) ) }}" alt="">
                </a>
                <h2 class="mb-16 text-4xl md:text-4xl font-bold font-heading tracking-px-n leading-tight">
                    {{ $name ?? __("Welcome Back") }}
                </h2>
            </div>-->
			<h3 class="fw-bold fs-4 lh-sm mb-2">Sign in,<br/>
we are building something special!</h3>
<p>If you are seeing this,<br/>
then you are a part of the journey!</p>
<h4 class="fw-bold fs-5 mt-4 mb-3">Sign in to your Account</h4>
            <label class="block mb-3">
                <!--<p class="mb-2 text-gray-700 font-semibold leading-normal">{{ __("Email or Username") }}</p>-->
                <input type="text" id="username" name="username" class="form-control" placeholder="{{ __('Enter username or email address') }}">
            </label>
            <label class="block mb-3">
                <!--<p class="mb-2 text-gray-700 font-semibold leading-normal">{{ __("Password") }}</p>-->
                <input id="password" type="password" name="password" class="form-control" placeholder="{{ __('Enter your Password') }}">
            </label>
            <div class="mb-3">
                {!! Captcha::render(); !!}
            </div>

            <div class="flex flex-wrap justify-between mb-3">
                <div class="flex justify-between w-full items-center">
                    <div class="flex items-center">
                        <input class="w-4 h-4" id="remember" type="checkbox" name="remember" value="1">
                        <label class="ml-2 text-gray-700 font-medium" for="remember">
                            <span>{{ __("Remember Me") }}</span>
                        </label>
                    </div>

                    <a href="{{ url('auth/forgot-password') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ __("Forgot Password?") }}</a>
                </div>
            </div>

            <div class="msg-error mb-2"></div>
            <div class="msg-success mb-2"></div>

            <button type="submit" class="w-full btn btn-primary">
                {{ __("Sign In") }}
            </button>
                @php
                    $socials = [
                        'google' => [
                            'status' => get_option('auth_google_login_status', 0),
                            'url'    => url('auth/login/google'),
                            'icon'   => '<img src="'.theme_public_asset('images/google.png').'" class="size-6">',
                            'label'  => __("Continue with Google"),
                        ],
                        'facebook' => [
                            'status' => get_option('auth_facebook_login_status', 0),
                            'url'    => url('auth/login/facebook'),
                            'icon'   => '<i class="fa-brands fa-square-facebook text-2xl text-[#1877F2]"></i>',
                            'label'  => __("Continue with Facebook"),
                        ],
                        'x' => [
                            'status' => get_option('auth_x_login_status', 0),
                            'url'    => url('auth/login/x'),
                            'icon'   => '<i class="fab fa-x-twitter mr-2 text-2xl text-[#000]"></i>',
                            'label'  => __("Continue with X"),
                        ],
                    ];
                @endphp
            @if(collect($socials)->where('status', 1)->count())
                <p class="mb-5 text-sm text-gray-500 font-medium text-center">
                    {{ __("Or continue with") }}
                </p>

                <div class="flex flex-wrap justify-center -m-2">
                    @foreach($socials as $s)
                        @if($s['status'])
                            <a href="{{ $s['url'] }}" class="flex items-center justify-center p-4 bg-white hover:bg-gray-50 border rounded-lg transition ease-in-out duration-200 gap-2 w-full mb-3">
                                {!! $s['icon'] !!}
                                <span class="font-semibold leading-normal">{{ $s['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </form>

        <!-- Switch to signup -->
        <!--@if(get_option("auth_signup_page_status", 1))
            <p class="text-center pt-4">
                {{ __("Don't have an account?") }}
                <a href="{{ url('auth/signup') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ __("Sign up") }}</a>
            </p>
        @endif-->
    </div>
</section>