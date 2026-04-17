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
        :root {
            --bg: #f3f5f2;
            --card: #ffffff;
            --text: #1f2933;
            --muted: #667085;
            --border: #e5e7eb;
            --brand: #166534;
            --brand-2: #0f766e;
            --brand-soft: #ecfdf3;
            --warning-soft: #fff7ed;
            --danger-soft: #fef2f2;
            --info-soft: #eff6ff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: radial-gradient(circle at top, #eef5ee 0%, var(--bg) 50%, #eef2f1 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: var(--text);
            -webkit-text-size-adjust: 100%;
        }
        .wrapper { width: 100%; background-color: transparent; padding: 32px 16px; }
        .container { max-width: 640px; margin: 0 auto; background: var(--card); border-radius: 18px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, .10); border: 1px solid rgba(255,255,255,.65); }

        .header {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
            padding: 30px 40px;
            text-align: center;
        }
        .header .logo-text {
            font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -.4px;
        }
        .header .logo-leaf { font-size: 28px; margin-right: 6px; }
        .header .tagline { font-size: 12px; color: rgba(255,255,255,.82); margin-top: 6px; letter-spacing: .12em; text-transform: uppercase; }

        .hero {
            background: linear-gradient(180deg, var(--brand-soft) 0%, #f8fbf8 100%);
            padding: 24px 40px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid rgba(22, 101, 52, .08);
        }
        .hero-icon { width: 52px; height: 52px; border-radius: 16px; background: #ffffff; box-shadow: 0 8px 20px rgba(22, 101, 52, .12); display: flex; align-items: center; justify-content: center; font-size: 26px; line-height: 1; flex-shrink: 0; }
        .hero h1 { font-size: 20px; font-weight: 800; color: var(--brand); margin: 0; }
        .hero p  { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

        .body { padding: 32px 40px; }
        .body p { margin-bottom: 16px; color: #344054; }
        .body p:last-child { margin-bottom: 0; }

        .info-box {
            background: #f9fafb;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px 18px;
            margin: 20px 0;
        }
        .info-box .info-row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid #eef2f7; font-size: 14px; }
        .info-box .info-row:last-child { border-bottom: none; }
        .info-box .info-label { color: var(--muted); font-weight: 600; }
        .info-box .info-value { font-weight: 600; color: var(--text); text-align: right; }

        .alert-box {
            border-radius: 14px; padding: 14px 18px; margin: 20px 0; font-size: 14px;
        }
        .alert-success { background: var(--brand-soft); border-left: 4px solid #22c55e; color: var(--brand); }
        .alert-warning { background: var(--warning-soft); border-left: 4px solid #f59e0b; color: #92400e; }
        .alert-danger  { background: var(--danger-soft); border-left: 4px solid #ef4444; color: #b91c1c; }
        .alert-info    { background: var(--info-soft); border-left: 4px solid #3b82f6; color: #1d4ed8; }

        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
            color: #ffffff !important;
            text-decoration: none; font-weight: 700; font-size: 15px;
            padding: 14px 34px; border-radius: 999px;
            letter-spacing: .01em;
            box-shadow: 0 10px 20px rgba(22, 101, 52, .18);
        }
        .btn-secondary {
            background: #ffffff;
            color: var(--brand) !important;
            border: 1px solid rgba(22, 101, 52, .25);
            box-shadow: none;
        }
        .btn-danger { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

        .data-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px; }
        .data-table th { background: #f8fafc; color: var(--brand); font-weight: 700; text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border); }
        .data-table td { padding: 10px 12px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table .total-row td { font-weight: 700; background: #f8fafc; border-top: 1px solid var(--border); }

        .divider { border: none; border-top: 1px solid var(--border); margin: 24px 0; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success  { background: var(--brand-soft); color: var(--brand); }
        .badge-warning  { background: var(--warning-soft); color: #92400e; }
        .badge-danger   { background: var(--danger-soft); color: #b91c1c; }
        .badge-info     { background: var(--info-soft); color: #1d4ed8; }
        .badge-secondary{ background: #f3f4f6; color: #475467; }

        .step-list { list-style: none; padding: 0; margin: 16px 0; }
        .step-list li { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; font-size: 14px; color: #344054; }
        .step-num { flex-shrink: 0; width: 28px; height: 28px; border-radius: 999px; background: var(--brand); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }

        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid var(--border); }
        .footer p { font-size: 12px; color: var(--muted); margin: 4px 0; }
        .footer a { color: var(--brand); text-decoration: none; font-weight: 600; }
        .footer .divider-dot { margin: 0 8px; color: #cbd5e1; }
        .footer .social { margin: 10px 0; }
        .footer .app-name { font-weight: 800; color: var(--text); font-size: 13px; }

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

    {{-- Header --}}
    <div class="header">
        <div class="logo-text">
            <span class="logo-leaf">🌱</span>Plantix AI
        </div>
        <div class="tagline">Smart Agriculture Platform</div>
    </div>

    {{-- Hero strip --}}
    @isset($heroIcon, $heroTitle)
    <div class="hero">
        <div class="hero-icon">{{ $heroIcon }}</div>
        <div>
            <h1>{{ $heroTitle }}</h1>
            @isset($heroSubtitle)<p>{{ $heroSubtitle }}</p>@endisset
        </div>
    </div>
    @endisset

    {{-- Body --}}
    <div class="body">
        @yield('content')
    </div>

    {{-- Footer --}}
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
