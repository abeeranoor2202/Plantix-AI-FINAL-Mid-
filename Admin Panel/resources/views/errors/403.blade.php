<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied | Plantix AI</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
               background: #f8f9fa; display: flex; align-items: center; justify-content: center;
               min-height: 100vh; padding: 2rem; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.08);
                max-width: 480px; width: 100%; padding: 3rem 2.5rem; text-align: center; }
        .code { font-size: 5rem; font-weight: 800; color: #dc3545; line-height: 1; }
        h1 { font-size: 1.5rem; margin: 1rem 0 .5rem; color: #212529; }
        p { color: #6c757d; line-height: 1.6; }
        a { display: inline-block; margin-top: 2rem; padding: .7rem 1.8rem;
            background: #198754; color: #fff; border-radius: 8px; text-decoration: none;
            font-weight: 600; transition: background .2s; }
        a:hover { background: #146c43; }
    </style>
</head>
<body>
<div class="card">
    <div class="code">403</div>
    <h1>Access Denied</h1>
    <p>You don't have permission to access this page. If you believe this is a mistake, please contact support.</p>
    <a href="{{ url('/') }}">Return to Home</a>
</div>
</body>
</html>
