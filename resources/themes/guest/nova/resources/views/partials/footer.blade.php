<!--<section class="py-24 md:pb-32 bg-white overflow-hidden" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <img class="absolute top-0 left-1/2 transform -translate-x-1/2" src="{{ theme_public_asset('images/cta/gradient4.svg') }}" alt=""/>
    <div class="relative z-10 container px-4 mx-auto">
        <div class="flex flex-wrap -m-8">
            <div class="w-full md:w-auto p-8">

                    <img class="w-56 mx-auto transform hover:translate-y-4 transition ease-in-out duration-1000 rounded-lg hide-on-mobile" src="{{ theme_public_asset('images/cta/man-play.png') }}" alt=""/>
                
            </div>
            <div class="w-full md:flex-1 p-8">
                <div class="md:max-w-2xl mx-auto text-center">
                    <h2 class="mb-10 text-6xl md:text-7xl font-bold font-heading text-center tracking-px-n leading-tight">
                        {{ __("Experience every feature. No commitment, no credit card required.") }}
                    </h2>
                    <div class="mb-12 md:inline-block">
                        <a href="{{ route("login") }}" class="py-4 px-6 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200" type="button">
                            {{ __("Get Started Now") }}
                        </a>
                    </div>
                    <div class="md:max-w-sm mx-auto">
                        <div class="flex flex-wrap -m-2">
                            <div class="w-auto p-2">
                                <svg class="mt-1" width="26" height="20" viewbox="0 0 26 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 20V14.2777C0 12.6321 0.306867 10.921 0.920601 9.14446C1.55293 7.34923 2.40844 5.65685 3.48712 4.06732C4.58441 2.45909 5.81187 1.10332 7.16953 0L11.8562 3.0575C10.7589 4.72183 9.83834 6.46096 9.09442 8.2749C8.3691 10.0701 8.01574 12.0524 8.03433 14.2216V20H0ZM14.1438 20V14.2777C14.1438 12.6321 14.4506 10.921 15.0644 9.14446C15.6967 7.34923 16.5522 5.65685 17.6309 4.06732C18.7282 2.45909 19.9557 1.10332 21.3133 0L26 3.0575C24.9027 4.72183 23.9821 6.46096 23.2382 8.2749C22.5129 10.0701 22.1595 12.0524 22.1781 14.2216V20H14.1438Z" fill="#E0E7FF"></path>
                                </svg>
                            </div>
                            <div class="flex-1 p-2">
                                <p class="mb-4 text-lg font-medium leading-normal text-left">
                                    {{ __("The easiest way to manage all my social channels in one place. It saves me hours every week!") }}
                                </p>
                                <h3 class="font-bold text-left">- {{ __("Anna Brown") }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-auto self-end p-8">
                <img class="w-52 mx-auto transform hover:-translate-y-4 transition ease-in-out duration-1000 rounded-lg" src="{{ theme_public_asset('images/cta/woman-play2.png') }}" alt=""/>
            </div>
        </div>
    </div>
</section>

<section class="pt-15 overflow-hidden border-t border-gray-600" style="background-image: url({{ theme_public_asset('images/features/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto">
        <div class="pb-9 border-b border-gray-200">
            <div class="flex flex-wrap items-center justify-between -m-4">
                {{-- Logo --}}
                <div class="w-auto p-4">
                    <a href="{{ url('/') }}">
                        <img class="h-9" src="{{ url(get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png'))) }}" alt="">
                    </a>
                </div>
                {{-- Main Menu --}}
                <ul class="flex flex-wrap -m-4 md:-m-9 p-4">
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600 {{ request()->is('/') ? 'text-indigo-600' : '' }}"
                           href="{{ url('') }}">
                            {{ __("Home") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ (request()->is('/') && str_contains(request()->fullUrl(), '#features')) ? 'text-indigo-600' : '' }}"
                           href="{{ url('') }}#features">
                            {{ __("Features") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('pricing*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('pricing') }}">
                            {{ __("Pricing") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('faqs*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('faqs') }}">
                            {{ __("FAQs") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('blogs*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('blogs') }}">
                            {{ __("Blog") }}
                        </a>
                    </li>
                    <li class="p-4 md:p-9">
                        <a class="font-medium tracking-tight transition duration-200 text-gray-700 hover:text-gray-600
                            {{ request()->is('contact*') ? 'text-indigo-600' : '' }}"
                           href="{{ url('contact') }}">
                            {{ __("Contact") }}
                        </a>
                    </li>
                </ul>

                {{-- Social Icons --}}
                <div class="w-auto p-4">
                    <div class="flex flex-wrap items-center -m-4">
                        @if(get_option("social_page_facebook", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-blue-600 transition duration-200"
                                   href="{{ get_option('social_page_facebook') }}"
                                   title="Facebook" target="_blank" rel="noopener">
                                    <i class="fab fa-facebook fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_instagram", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-pink-500 transition duration-200"
                                   href="{{ get_option('social_page_instagram') }}"
                                   title="Instagram" target="_blank" rel="noopener">
                                    <i class="fab fa-instagram fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_tiktok", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-black transition duration-200"
                                   href="{{ get_option('social_page_tiktok') }}"
                                   title="TikTok" target="_blank" rel="noopener">
                                    <i class="fab fa-tiktok fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_youtube", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-red-600 transition duration-200"
                                   href="{{ get_option('social_page_youtube') }}"
                                   title="YouTube" target="_blank" rel="noopener">
                                    <i class="fab fa-youtube fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_x", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-gray-800 transition duration-200"
                                   href="{{ get_option('social_page_x') }}"
                                   title="X (Twitter)" target="_blank" rel="noopener">
                                    <i class="fab fa-x-twitter fa-lg"></i>
                                </a>
                            </div>
                        @endif

                        @if(get_option("social_page_pinterest", ""))
                            <div class="w-auto p-4">
                                <a class="text-gray-800 hover:text-red-600 transition duration-200"
                                   href="{{ get_option('social_page_pinterest') }}"
                                   title="Pinterest" target="_blank" rel="noopener">
                                    <i class="fab fa-pinterest fa-lg"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- Footer --}}
        <div class="pt-4 pb-6">
            <div class="flex flex-wrap justify-between items-center -m-4">
                <div class="w-auto p-4">
                    <p class="tracking-tight">© {{ date('Y') }}, {{ __("All Rights Reserved") }}</p>
                </div>
                <div class="w-auto p-4">
                    <div class="flex flex-wrap">
                        <div class="flex flex-wrap">
                            <div class="w-auto p-4">
                                <a class="tracking-tight" href="{{ url('privacy-policy') }}">{{ __("Privacy Policy") }}</a>
                            </div>
                            <div class="w-auto p-4">
                                <a class="tracking-tight" href="{{ url('terms-of-service') }}">{{ __("Terms & Conditions") }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>-->
<footer class="footer bg-white gap-0">
      <div class="container py-3">
			<div class="d-flex justify-content-center items-center w-100">
				<a class="btn btn-primary" href="{{ url('terms-of-service') }}">{{ __("Terms & Conditions") }}</a>
				<a class="btn btn-primary" href="{{ url('privacy-policy') }}">{{ __("Privacy Policy") }}</a>
			</div>
      </div>
      <div class="footer-bottom py-3 w-100">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-5 text-md-start text-center mt-0">
              <p class="mb-0">Copyright © {{ date('Y') }}, {{ __("All Rights Reserved") }}</p>
            </div>
			<div class="social col-md-2 d-flex justify-content-center gap-2">
			<a href="https://www.facebook.com/ItsPandoLLC/">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="48px" height="48px"><path d="M 25 3 C 12.861562 3 3 12.861562 3 25 C 3 36.019135 11.127533 45.138355 21.712891 46.728516 L 22.861328 46.902344 L 22.861328 29.566406 L 17.664062 29.566406 L 17.664062 26.046875 L 22.861328 26.046875 L 22.861328 21.373047 C 22.861328 18.494965 23.551973 16.599417 24.695312 15.410156 C 25.838652 14.220896 27.528004 13.621094 29.878906 13.621094 C 31.758714 13.621094 32.490022 13.734993 33.185547 13.820312 L 33.185547 16.701172 L 30.738281 16.701172 C 29.349697 16.701172 28.210449 17.475903 27.619141 18.507812 C 27.027832 19.539724 26.84375 20.771816 26.84375 22.027344 L 26.84375 26.044922 L 32.966797 26.044922 L 32.421875 29.564453 L 26.84375 29.564453 L 26.84375 46.929688 L 27.978516 46.775391 C 38.71434 45.319366 47 36.126845 47 25 C 47 12.861562 37.138438 3 25 3 z M 25 5 C 36.057562 5 45 13.942438 45 25 C 45 34.729791 38.035799 42.731796 28.84375 44.533203 L 28.84375 31.564453 L 34.136719 31.564453 L 35.298828 24.044922 L 28.84375 24.044922 L 28.84375 22.027344 C 28.84375 20.989871 29.033574 20.060293 29.353516 19.501953 C 29.673457 18.943614 29.981865 18.701172 30.738281 18.701172 L 35.185547 18.701172 L 35.185547 12.009766 L 34.318359 11.892578 C 33.718567 11.811418 32.349197 11.621094 29.878906 11.621094 C 27.175808 11.621094 24.855567 12.357448 23.253906 14.023438 C 21.652246 15.689426 20.861328 18.170128 20.861328 21.373047 L 20.861328 24.046875 L 15.664062 24.046875 L 15.664062 31.566406 L 20.861328 31.566406 L 20.861328 44.470703 C 11.816995 42.554813 5 34.624447 5 25 C 5 13.942438 13.942438 5 25 5 z"></path></svg>
			</a>
			<a href="https://www.instagram.com/pandosocial/">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="48px" height="48px"><path d="M 16 3 C 8.8324839 3 3 8.8324839 3 16 L 3 34 C 3 41.167516 8.8324839 47 16 47 L 34 47 C 41.167516 47 47 41.167516 47 34 L 47 16 C 47 8.8324839 41.167516 3 34 3 L 16 3 z M 16 5 L 34 5 C 40.086484 5 45 9.9135161 45 16 L 45 34 C 45 40.086484 40.086484 45 34 45 L 16 45 C 9.9135161 45 5 40.086484 5 34 L 5 16 C 5 9.9135161 9.9135161 5 16 5 z M 37 11 A 2 2 0 0 0 35 13 A 2 2 0 0 0 37 15 A 2 2 0 0 0 39 13 A 2 2 0 0 0 37 11 z M 25 14 C 18.936712 14 14 18.936712 14 25 C 14 31.063288 18.936712 36 25 36 C 31.063288 36 36 31.063288 36 25 C 36 18.936712 31.063288 14 25 14 z M 25 16 C 29.982407 16 34 20.017593 34 25 C 34 29.982407 29.982407 34 25 34 C 20.017593 34 16 29.982407 16 25 C 16 20.017593 20.017593 16 25 16 z"></path></svg>
			</a>
			<a href="https://x.com/itspandosocial">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="48px" height="48px"><path d="M 5.9199219 6 L 20.582031 27.375 L 6.2304688 44 L 9.4101562 44 L 21.986328 29.421875 L 31.986328 44 L 44 44 L 28.681641 21.669922 L 42.199219 6 L 39.029297 6 L 27.275391 19.617188 L 17.933594 6 L 5.9199219 6 z M 9.7167969 8 L 16.880859 8 L 40.203125 42 L 33.039062 42 L 9.7167969 8 z"></path></svg>
			</a>
			<a href="https://share.google/t4rL9tjMHbkXxgs8X">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="48px" height="48px"><path d="M 9.2832031 4 C 7.488935 4 5.9052102 5.2051958 5.4277344 6.9355469 L 2 19.365234 L 2 19.5 C 2 21.333655 2.770342 22.991752 4 24.175781 L 4 43 C 4 44.64497 5.3550302 46 7 46 L 43 46 C 44.64497 46 46 44.64497 46 43 L 46 24.175781 C 47.229658 22.991752 48 21.333655 48 19.5 L 48 19.365234 L 44.570312 6.9355469 C 44.092963 5.2056548 42.509782 4 40.714844 4 L 9.2832031 4 z M 9.2832031 6 L 14.853516 6 L 13.197266 18 L 4.4511719 18 L 7.3554688 7.46875 C 7.5959929 6.597101 8.3794712 6 9.2832031 6 z M 16.871094 6 L 24 6 L 24 18 L 15.216797 18 L 16.871094 6 z M 26 6 L 33.128906 6 L 34.783203 18 L 26 18 L 26 6 z M 35.146484 6 L 40.714844 6 C 41.619905 6 42.401927 6.596642 42.642578 7.46875 L 45.548828 18 L 36.802734 18 L 35.146484 6 z M 4.0644531 20 L 12.949219 20 C 12.699714 22.256206 10.826202 24 8.5 24 C 6.175282 24 4.3143567 22.254621 4.0644531 20 z M 15.099609 20 L 23.900391 20 C 23.642986 22.247621 21.820142 24 19.5 24 C 17.179858 24 15.357014 22.247621 15.099609 20 z M 26.099609 20 L 34.900391 20 C 34.642986 22.247621 32.820142 24 30.5 24 C 28.179858 24 26.357014 22.247621 26.099609 20 z M 37.050781 20 L 45.935547 20 C 45.685643 22.254621 43.824718 24 41.5 24 C 39.173798 24 37.300286 22.256206 37.050781 20 z M 25 22.748047 C 26.135537 24.654479 28.129125 26 30.5 26 C 32.82974 26 34.842617 24.748335 35.966797 22.888672 C 37.112706 24.749928 39.163177 26 41.5 26 C 42.385009 26 43.229585 25.821229 44 25.498047 L 44 43 C 44 43.56503 43.56503 44 43 44 L 7 44 C 6.4349698 44 6 43.56503 6 43 L 6 25.498047 C 6.7704149 25.821229 7.6149912 26 8.5 26 C 10.836823 26 12.887294 24.749928 14.033203 22.888672 C 15.157383 24.748335 17.17026 26 19.5 26 C 21.870875 26 23.864463 24.654479 25 22.748047 z M 35.5 29 C 31.916 29 29 31.916 29 35.5 C 29 39.084 31.916 42 35.5 42 C 39.084 42 42 39.084 42 35.5 C 42 35.331 41.986609 35.166 41.974609 35 L 36 35 L 36 37 L 39.724609 37 C 39.103609 38.742 37.453 40 35.5 40 C 33.019 40 31 37.981 31 35.5 C 31 33.019 33.019 31 35.5 31 C 36.996 31 38.313813 31.741187 39.132812 32.867188 L 40.564453 31.435547 C 39.371453 29.953547 37.546 29 35.5 29 z"></path></svg>
			</a>
			</div>
			<div class="col-md-5 text-md-end text-center mt-0">
              <p class="mb-0">Designed and Developed by: <a href="http://royalinkdesign.com/" target="_blank">Royal Ink</a></p>
            </div>
          </div>
        </div>
      </div>
    </footer>
