<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
    <link rel="stylesheet" href="{{ asset('path-to-filament-css.css') }}">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; flex-direction:column;">
<h1>Welcome to Admin Panel</h1>
<a href="{{ url('/admin/login') }}" class="filament-button filament-button-primary" style="margin-bottom:1rem;">Log In</a>
<a href="{{ url('/admin/register') }}" class="filament-button filament-button-secondary">Create Account</a>
</body>
</html>
