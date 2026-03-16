<h2 style="margin-top:0;">{{ __('Your Subscription Has Expired') }}</h2>

<p>
    {{ __('Hello, :name!', ['name' => $fullname ?? 'User']) }}
</p>

<p>
    {{ __('Your Pando subscription has expired. Your account is currently on limited access — your data is safe, but publishing, analytics, and team access are paused until you renew.') }}
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
        <td style="color:#888; padding:6px 0;">{{ __('Expired On') }}:</td>
        <td>{{ $order_date ?? now()->format('d M Y') }}</td>
    </tr>
</table>

<div style="margin:28px 0 14px;">
    <a href="{{ $login_url ?? config('app.url') }}" class="btn"
       style="background: #e63946; color: #fff; padding: 12px 32px; border-radius: 5px;
              text-decoration:none; font-size: 17px;">
        {{ __('Reactivate My Subscription') }}
    </a>
</div>

<p style="color:#888;">
    {{ __('Your content and settings are preserved for 30 days. For assistance, reach us at :email.', ['email' => $support_email ?? 'support@yourdomain.com']) }}
</p>
