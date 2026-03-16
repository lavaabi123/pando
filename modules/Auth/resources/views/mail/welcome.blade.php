<h2>{{ __('Welcome to :app', ['app' => get_option("website_title", config('site.title'))]) }}</h2>

<p>{{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}</p>

<p>{{ __('We’re excited to have you on board. You can now explore your dashboard and start using all features. use below login,') }}</p>
<p>{{ __('Username - :username', ['username' => $username ?? 'Username']) }}</p>
<p>{{ __('Password - :password', ['password' => $password ?? 'Password']) }}</p>

<div style="margin: 28px 0;">
    <a href="{{ $login_url ?? url('/') }}" class="btn"
       style="background:#fd8107; color:#fff; padding:10px 32px; border-radius:50px; font-weight: 600; text-decoration:none;">
        {{ __('Login') }}
    </a>
</div>

<p>{{ __('Need help? Just reply to this email or contact support.') }}</p>