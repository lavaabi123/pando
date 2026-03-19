<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('LinkedIn Account Disconnected') }}</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; color:#333; }
        .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.08); }
        .header { text-align:center; }
        .header h1 { font-size:20px; font-weight:600; margin:0; }
        .body { padding:36px 40px; }
        .greeting { font-size:16px; color:#222; margin-bottom:14px; }
        .intro { font-size:15px; color:#555; line-height:1.6; margin-bottom:24px; }
        .accounts-box { background:#f8f9fc; border:1px solid #e4e7ed; border-radius:8px; padding:16px; margin-bottom:24px; font-size:14px; color:#333; line-height:1.8; white-space:pre-line; }
        .note { background:#fffbeb; border-left:4px solid #f59e0b; border-radius:4px; padding:12px 16px; font-size:13px; color:#78350f; line-height:1.5; margin-bottom:28px; }
        .cta { text-align:center; margin-bottom:28px; }
        .cta a { display:inline-block; background:#7ec476; color:#fff !important; text-decoration:none; font-size:15px; font-weight:600; padding:13px 32px; border-radius:8px; }
        .footer { border-top:1px solid #e8eaed; padding:24px 40px; text-align:center; font-size:12px; color:#aaa; line-height:1.6; }
        .footer a { color:#7ec476; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>{{ __('LinkedIn Account Disconnected') }}</h1>
    </div>

    <div class="body">

        <p class="greeting">{{ __('Hi') }} {{ $fullname }},</p>

        <p class="intro">
            {{ __('The following LinkedIn account(s) have been disconnected because the access token has expired or been revoked. Please reconnect to continue publishing.') }}
        </p>

        <div class="accounts-box">{{ $accounts_list }}</div>

        <div class="note">
            ⚠️ {{ __('Until reconnected, scheduled posts for these accounts will not be published.') }}
        </div>

        <div class="cta">
            <a href="{{ $reconnect_url }}">{{ __('Go to Manage Accounts') }}</a>
        </div>

        <p style="font-size:14px;color:#888;line-height:1.6;">
            {{ __('If you have already reconnected your account, you can ignore this email.') }}
        </p>

    </div>

</div>
</body>
</html>
