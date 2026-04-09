<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Access') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --auth-primary: #2e7d32;
            --auth-primary-dark: #1b5e20;
            --auth-bg-start: #1a3c34;
            --auth-bg-end: #2e7d32;
        }

        body {
            background: linear-gradient(135deg, var(--auth-bg-start) 0%, var(--auth-bg-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            padding: 2.25rem;
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .auth-brand i {
            font-size: 3rem;
            color: var(--auth-primary);
        }

        .auth-brand h4 {
            font-weight: 700;
            margin-top: .5rem;
            margin-bottom: .25rem;
        }

        .btn-auth {
            background: var(--auth-primary);
            color: #fff;
            border: none;
            font-weight: 600;
        }

        .btn-auth:hover {
            background: var(--auth-primary-dark);
            color: #fff;
        }

        @yield('styles')
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
