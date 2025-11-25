<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'beTaskee - Tất cả những gì nhà bạn cần')</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@hasSection('global_styles')
@yield('global_styles')
@else
<link rel="stylesheet" href="{{ asset('css/main.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
@stack('styles')
