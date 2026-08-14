<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Operator Credentials</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; max-width: 600px; margin: 0 auto; padding: 30px; }
        .logo { font-size: 20px; font-weight: bold; color: #3b82f6; margin-bottom: 20px; }
        h2 { color: #ffffff; margin-top: 0; }
        .cred-box { background-color: #0f172a; border: 1px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .cred-row { margin-bottom: 10px; font-size: 14px; }
        .cred-label { color: #94a3b8; font-weight: bold; width: 120px; display: inline-block; }
        .cred-val { color: #60a5fa; font-family: monospace; font-weight: bold; font-size: 15px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; text-decoration: none; font-weight: bold; padding: 12px 25px; border-radius: 8px; margin-top: 15px; }
        .footer { margin-top: 30px; font-size: 12px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">⚡ eBay Dropshipping Hub</div>
        <h2>Welcome, {{ $user->name }}!</h2>
        <p>An Operator account has been created for you under <strong>{{ $user->company->name ?? 'Company' }}</strong>.</p>
        
        <div class="cred-box">
            <div class="cred-row">
                <span class="cred-label">Portal URL:</span>
                <span class="cred-val">{{ url('/login') }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Username/Email:</span>
                <span class="cred-val">{{ $user->email }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Password:</span>
                <span class="cred-val">{{ $plainPassword }}</span>
            </div>
        </div>

        <p>You can log into the portal to manage order entries, upload Excel spreadsheets, and view store reports.</p>

        <a href="{{ url('/login') }}" class="btn">Log In to Portal</a>

        <div class="footer">
            This email was sent automatically by the eBay Dropshipping Hub system.
        </div>
    </div>
</body>
</html>
