<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkedIn Account Disconnected</title>
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #333;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #0077b5 0%, #005885 100%);
            padding: 32px 40px;
            text-align: center;
        }
        .header img {
            height: 36px;
            margin-bottom: 16px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .body {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 16px;
            color: #222;
            margin-bottom: 14px;
        }
        .intro {
            font-size: 15px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .accounts-list {
            background: #f8f9fc;
            border: 1px solid #e4e7ed;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 28px;
        }
        .accounts-list-header {
            background: #eef0f5;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .account-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-top: 1px solid #e4e7ed;
            gap: 12px;
        }
        .account-item:first-of-type {
            border-top: none;
        }
        .account-icon {
            width: 36px;
            height: 36px;
            background: #0077b5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .account-icon svg {
            width: 18px;
            height: 18px;
            fill: #fff;
        }
        .account-info .name {
            font-size: 14px;
            font-weight: 600;
            color: #222;
        }
        .account-info .type {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }
        .badge-expired {
            margin-left: auto;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            white-space: nowrap;
        }
        .cta-block {
            text-align: center;
            margin-bottom: 28px;
        }
        .cta-button {
            display: inline-block;
            background: #0077b5;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 13px 32px;
            border-radius: 8px;
            letter-spacing: -0.2px;
        }
        .note {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            padding: 12px 16px;
            font-size: 13px;
            color: #78350f;
            line-height: 1.5;
            margin-bottom: 28px;
        }
        .footer {
            border-top: 1px solid #e8eaed;
            padding: 24px 40px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            line-height: 1.6;
        }
        .footer a {
            color: #0077b5;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>
            {{ count($accounts) === 1
                ? __('LinkedIn Account Disconnected')
                : __(':count LinkedIn Accounts Disconnected', ['count' => count($accounts)]) }}
        </h1>
    </div>

    {{-- Body --}}
    <div class="body">

        <p class="greeting">
            {{ __('Hi :name,', ['name' => $user->fullname]) }}
        </p>

        <p class="intro">
            @if(count($accounts) === 1)
                {{ __('Your LinkedIn account listed below has been disconnected because the access token has expired. Please reconnect it to continue publishing and managing your content on Pando.') }}
            @else
                {{ __('The following LinkedIn accounts have been disconnected because their access tokens have expired. Please reconnect them to continue publishing and managing your content on Pando.') }}
            @endif
        </p>

        {{-- Account list --}}
        <div class="accounts-list">
            <div class="accounts-list-header">{{ __('Disconnected Accounts') }}</div>

            @foreach($accounts as $account)
            <div class="account-item">
                <div class="account-icon">
                    {{-- LinkedIn "in" logo --}}
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </div>
                <div class="account-info">
                    <div class="name">{{ $account->name }}</div>
                    <div class="type">LinkedIn {{ ucfirst($account->category) }}</div>
                </div>
                <span class="badge-expired">{{ __('Token Expired') }}</span>
            </div>
            @endforeach
        </div>

        {{-- Note --}}
        <div class="note">
            ⚠️ {{ __('Until reconnected, scheduled posts for these accounts will not be published.') }}
        </div>

        {{-- CTA --}}
        <div class="cta-block">
            <a href="{{ url_app('channels') }}" class="cta-button">
                {{ __('Go to Manage Accounts') }}
            </a>
        </div>

        <p style="font-size:14px; color:#888; line-height:1.6;">
            {{ __('If you did not expect this email or have already reconnected your account, you can ignore this message.') }}
        </p>

    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>
            {{ __('This is an automated notification from') }}
            <a href="{{ url('/') }}">{{ config('app.name') }}</a>.
        </p>
        <p style="margin:0;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
        </p>
    </div>

</div>
</body>
</html>
