<h2 style="margin-top:0;">{{ __('Your Subscription Expires Tomorrow') }}</h2>

<p>
    {{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}
</p>

<p>
    {{ __('This is a friendly reminder that your Pando subscription is expiring tomorrow. After it expires, your scheduled posts, analytics, and connected social accounts will be paused.') }}
</p>

<table style="margin:24px 0 18px 0; width:100%; max-width:400px;">
    <tr>
        <td style="color:#888; padding:6px 0;">{{ __('Plan') }}:</td>
        <td>{{ $plan_name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="color:#888; padding:6px 0;">{{ __('Amount') }}:</td>
        <td>{{ $order_amount ?? '-' }} {{ $order_currency ?? '' }}</td>
    </tr>
    <tr>
        <td style="color:#888; padding:6px 0;">{{ __('Expiring On') }}:</td>
        <td>{{ $order_date ?? now()->format('d M Y') }}</td>
    </tr>
</table>

<div style="margin:28px 0 14px;">
    <a href="{{ $login_url ?? config('app.url') }}" class="btn"
       style="background: #f59e0b; color: #fff; padding: 12px 32px; border-radius: 5px;
              text-decoration:none; font-size: 17px;">
        {{ __('Renew My Subscription') }}
    </a>
</div>

<p style="color:#888;">
    {{ __('If you have already renewed, please disregard this email. For assistance, contact us at :email.', ['email' => $support_email ?? 'support@yourdomain.com']) }}
</p>
