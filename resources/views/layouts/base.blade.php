<!DOCTYPE html>
<html lang="vi">
<head>
    @include('partials.head')
</head>
<body>
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.scripts')
</body>
</html>
