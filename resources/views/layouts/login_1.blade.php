<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Portal</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
</head>
<body>

<div class="container" style="justify-content:center; align-items:center;">
    <div class="login_box">

        <div class="login_logo">
            <img src="{{ asset('emblem_white_1.svg') }}" alt="logo">
        </div>

        <div class="form-header">
            <h2>Admin Login</h2>
            <p>Authorized personnel only</p>
        </div>

        @if(session('error'))
            <p style="color:red; text-align:center;">
                {{ session('error') }}
            </p>
        @endif

        <form method="POST" action="/login">
            @csrf

            <input
                type="email"
                name="email"
                placeholder="Admin Email"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button type="submit" class="main-btn">
                Login
            </button>
        </form>

    </div>
</div>

</body>
</html>