<table width="100%" style="background:#f4f4f4; color:#888; padding:24px 0 0 0;">
  <tr>
    <td align="center" style="font-size: 13px;">
      <div style="margin-bottom:8px;">&copy; {{ date('Y') }} {{ get_option('contact_company_name', 'Your Company Name') }}. All rights reserved.</div>
      <div style="font-size:12px;">{{ get_option('contact_location', 'Your Company Address') }}</div>
      <div style="margin-top:8px;">
        <a href="{{ get_option('social_page_facebook', '#') }}" style="color:#248bcb; text-decoration:none;"><img width="30" src="{{ url(asset('public/img/fb-black.png')) }}"></a> &middot;
        <a href="{{ get_option('social_page_x', '#') }}" style="color:#248bcb; text-decoration:none;"><img width="30" src="{{ url(asset('public/img/x-twitter.png')) }}"></a>
      </div>
    </td>
  </tr>
</table>
