<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  @if(isset($subject))
  <title>{{ $subject ?? __('Email Notification') }}</title>
  @else
    <title>@yield('subject', __('Email Notification'))</title>
  @endif
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin:0; padding:0; background: #f9f9f9; font-family: 'Segoe UI', Arial, sans-serif; }
    .main-container { background: #f4f4f4; max-width: 100%; padding:20px; margin: 0 auto 0 auto; border-radius: 0px; box-shadow:0 4px 16px #0001; overflow:hidden; }
    .content { padding:32px 24px 24px 24px; color:#222; }
    .btn {
      display:inline-block;
      background: #248bcb;
      color:#fff;
      padding: 12px 32px;
      border-radius:6px;
      text-decoration:none;
      font-size:16px;
      margin: 16px 0;
      transition:.15s;
    }
    .btn:hover { background:#18597c; }
    @media (max-width:600px) {
      .main-container { width: 96%!important; margin:16px auto; }
      .content { padding:18px 8px; }
    }
  </style>
</head>
<body>
  <div class="main-container">
    @include('adminmailthemes::themes.modern-pro.header')
    <div class="content" style="max-width: 560px;margin: 0 auto;background: #fff;border-radius: 25px;">
      {!! $content??'' !!}
    </div>
    @include('adminmailthemes::themes.modern-pro.footer')
  </div>
</body>
</html>