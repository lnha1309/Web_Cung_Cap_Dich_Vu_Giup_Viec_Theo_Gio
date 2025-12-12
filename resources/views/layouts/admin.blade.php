<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <base href="{{ asset('admin-dashboard') }}/">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin-dashboard/style.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')

    <script src="{{ asset('admin-dashboard/constants/recent-order-data.js') }}"></script>
    <script src="{{ asset('admin-dashboard/constants/update-data.js') }}"></script>
    <script src="{{ asset('admin-dashboard/constants/sales-analytics-data.js') }}"></script>
    <script src="{{ asset('admin-dashboard/index.js') }}"></script>
    @stack('scripts')
</body>
</html>
