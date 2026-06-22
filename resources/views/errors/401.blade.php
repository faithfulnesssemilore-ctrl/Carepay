<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized</title>
    <style>
        body{margin:0;font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Ubuntu,sans-serif;background:#070712;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center;padding:24px;}
        .card{max-width:520px;width:100%;background:rgba(15,23,42,.95);border:1px solid rgba(255,255,255,.08);border-radius:24px;padding:36px;box-shadow:0 24px 80px rgba(0,0,0,.4);}
        .badge{display:inline-flex;padding:8px 14px;border-radius:999px;background:#ef4444;color:#fff;font-weight:700;text-transform:uppercase;letter-spacing:.08em;font-size:.75rem;margin-bottom:20px;}
        h1{font-size:3rem;margin:0 0 18px;letter-spacing:-.04em;}
        p{color:#cbd5e1;line-height:1.8;margin:0 0 24px;font-size:1rem;}
        a{display:inline-block;padding:12px 24px;border-radius:12px;background:#ef4444;color:#fff;text-decoration:none;font-weight:600;transition:.2s;}
        a:hover{background:#dc2626;}
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">401 Unauthorized</span>
        <h1>Access denied</h1>
        <p>You need to sign in before you can view this page. Please login to continue.</p>
        <a href="/login">Go to Login</a>
    </div>
</body>
</html>
