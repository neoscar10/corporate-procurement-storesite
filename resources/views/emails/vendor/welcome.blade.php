<!doctype html>
<html>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto;">
    <h2>Welcome to Corporate Procurement</h2>
    <p>Hi {{ $user->name ?? 'Vendor' }},</p>
    <p>Your vendor account has been created.</p>
    <p>
        <strong>Email:</strong> {{ $user->email }}<br>
        <strong>Temporary password:</strong> {{ $plainPassword }}
    </p>
    <p>Please log in and change your password.</p>
</body>

</html>