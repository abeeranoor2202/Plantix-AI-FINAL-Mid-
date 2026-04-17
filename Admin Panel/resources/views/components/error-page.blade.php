@props([
    'code' => '500',
    'title' => 'Something went wrong',
    'message' => 'An unexpected error occurred. Please try again.',
    'actionText' => 'Return Home',
    'actionHref' => '/',
    'accent' => '#ef4444',
    'supportText' => null,
])

@php
    $pageTitle = $code . ' — Plantix AI';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.15), transparent 30%),
                radial-gradient(circle at bottom right, rgba(239, 68, 68, 0.12), transparent 28%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: #0f172a;
        }
        .shell {
            width: min(100%, 720px);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .accent {
            height: 10px;
            background: linear-gradient(90deg, {{ $accent }}, #0ea5e9);
        }
        .content {
            padding: 40px 28px;
            text-align: center;
        }
        .code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 96px;
            padding: 10px 20px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.04);
            color: {{ $accent }};
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        h1 {
            margin: 20px 0 10px;
            font-size: 2rem;
            line-height: 1.15;
        }
        p {
            margin: 0 auto;
            max-width: 54ch;
            color: #475569;
            line-height: 1.7;
            font-size: 1rem;
        }
        .actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: #0f172a;
            color: #fff;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.18);
        }
        .btn-secondary {
            background: rgba(15, 23, 42, 0.04);
            color: #0f172a;
        }
        .support {
            margin-top: 18px;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        @media (max-width: 640px) {
            .content { padding: 32px 18px; }
            h1 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
    <main class="shell" role="main" aria-labelledby="error-title">
        <div class="accent"></div>
        <div class="content">
            <div class="code">{{ $code }}</div>
            <h1 id="error-title">{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="actions">
                <a class="btn btn-primary" href="{{ $actionHref }}">{{ $actionText }}</a>
                <a class="btn btn-secondary" href="{{ url()->previous() !== url()->current() ? url()->previous() : $actionHref }}">Go Back</a>
            </div>
            @if($supportText)
                <div class="support">{{ $supportText }}</div>
            @endif
        </div>
    </main>
</body>
</html>