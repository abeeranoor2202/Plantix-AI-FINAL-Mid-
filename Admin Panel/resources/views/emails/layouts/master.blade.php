<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $emailSubject ?? config('app.name') }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #f4f6f3;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: #333333;
            -webkit-text-size-adjust: 100%;
        }
        .wrapper { width: 100%; background-color: #f4f6f3; padding: 32px 16px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            padding: 28px 40px;
            text-align: center;
        }
        .header .logo-text {
            font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: -.5px;
        }
        .header .logo-leaf { font-size: 28px; margin-right: 6px; }
        .header .tagline { font-size: 12px; color: #a5d6a7; margin-top: 4px; letter-spacing: .05em; text-transform: uppercase; }

        /* Hero / Title strip */
        .hero {
            background: #e8f5e9;
            padding: 24px 40px;
            display: flex; align-items: center; gap: 14px;
        }
        .hero-icon { font-size: 36px; line-height: 1; }
        .hero h1 { font-size: 20px; font-weight: 700; color: #1b5e20; margin: 0; }
        .hero p  { font-size: 13px; color: #555; margin: 3px 0 0; }

        /* Body */
        .body { padding: 32px 40px; }
        .body p { margin-bottom: 16px; color: #444; }
        .body p:last-child { margin-bottom: 0; }

        /* Info box */
        .info-box {
            background: #f9fbe7; border: 1px solid #dcedc8;
            border-left: 4px solid #8bc34a;
            border-radius: 6px; padding: 16px 20px; margin: 20px 0;
        }
        .info-box .info-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f0f4e8; font-size: 14px; }
        .info-box .info-row:last-child { border-bottom: none; }
        .info-box .info-label { color: #777; font-weight: 500; }
        .info-box .info-value { font-weight: 600; color: #333; text-align: right; }

        /* Alert box */
        .alert-box {
            border-radius: 6px; padding: 14px 18px; margin: 20px 0; font-size: 14px;
        }
        .alert-success { background: #e8f5e9; border-left: 4px solid #4caf50; color: #2e7d32; }
        .alert-warning { background: #fff8e1; border-left: 4px solid #ffc107; color: #856404; }
        .alert-danger  { background: #ffebee; border-left: 4px solid #f44336; color: #c62828; }
        .alert-info    { background: #e3f2fd; border-left: 4px solid #2196f3; color: #0d47a1; }

        /* CTA Button */
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn {
            display: inline-block;
            background: #2e7d32; color: #ffffff !important;
            text-decoration: none; font-weight: 700; font-size: 15px;
            padding: 14px 36px; border-radius: 8px;
            letter-spacing: .02em;
        }
        .btn-secondary {
            background: #ffffff; color: #2e7d32 !important;
            border: 2px solid #2e7d32;
        }
        .btn-danger { background: #d32f2f; }
        .btn-warning { background: #f57c00; }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px; }
        .data-table th { background: #e8f5e9; color: #2e7d32; font-weight: 600; text-align: left; padding: 10px 12px; border-bottom: 2px solid #c8e6c9; }
        .data-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table .total-row td { font-weight: 700; background: #f9fbe7; border-top: 2px solid #c8e6c9; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #eeeeee; margin: 24px 0; }

        /* Status badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success  { background: #e8f5e9; color: #2e7d32; }
        .badge-warning  { background: #fff8e1; color: #856404; }
        .badge-danger   { background: #ffebee; color: #c62828; }
        .badge-info     { background: #e3f2fd; color: #0d47a1; }
        .badge-secondary{ background: #f5f5f5; color: #555; }

        /* Steps */
        .step-list { list-style: none; padding: 0; margin: 16px 0; }
        .step-list li { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; font-size: 14px; }
        .step-num { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }

        /* Footer */
        .footer { background: #f4f6f3; padding: 24px 40px; text-align: center; }
        .footer p { font-size: 12px; color: #999; margin: 4px 0; }
        .footer a { color: #2e7d32; text-decoration: none; }
        .footer .divider-dot { margin: 0 6px; color: #ccc; }
        .footer .social { margin: 10px 0; }
        .footer .app-name { font-weight: 700; color: #555; font-size: 13px; }

        @media only screen and (max-width: 600px) {
            .header { padding: 20px 24px; }
            .hero   { padding: 18px 24px; flex-direction: column; text-align: center; }
            .body   { padding: 24px 20px; }
            .footer { padding: 20px; }
            .data-table th, .data-table td { padding: 8px; }
        }
    </style>
</head>
<body>
<div class="wrapper">
<div class="container">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="logo-text">
            <span class="logo-leaf">🌱</span>Plantix AI
        </div>
        <div class="tagline">Smart Agriculture Platform</div>
    </div>

    {{-- ── Hero strip ──────────────────────────────────────────────────────── --}}
    @isset($heroIcon, $heroTitle)
    <div class="hero">
        <div class="hero-icon">{{ $heroIcon }}</div>
        <div>
            <h1>{{ $heroTitle }}</h1>
            @isset($heroSubtitle)<p>{{ $heroSubtitle }}</p>@endisset
        </div>
    </div>
    @endisset

    {{-- ── Body ───────────────────────────────────────────────────────────── --}}
    <div class="body">
        @yield('content')
    </div>

    {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
    <div class="footer">
        <p class="app-name">Plantix AI — Smart Agriculture Platform</p>
        <p>
            <a href="{{ config('app.url') }}">Visit Website</a>
            <span class="divider-dot">·</span>
            <a href="{{ config('app.url') }}/contact">Contact Support</a>
            <span class="divider-dot">·</span>
            <a href="{{ config('app.url') }}/unsubscribe">Unsubscribe</a>
        </p>
        <p style="margin-top:10px; font-size:11px; color:#bbb;">
            © {{ date('Y') }} Plantix AI. All rights reserved.<br>
            This email was sent to <strong>{{ $recipientEmail ?? '' }}</strong>.
            If you did not expect this email, you can safely ignore it.
        </p>
    </div>

</div>
</div>
</body>
</html>
