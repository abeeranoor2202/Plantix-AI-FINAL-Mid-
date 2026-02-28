<!DOCTYPE html>
<html lang="en">
@include('partials.head')
<body>

@hasSection('header')
    @yield('header')
@else
    @include('partials.header-notopbar')
@endif

@yield('content')

@hasSection('footer')
    @yield('footer')
@else
    @include('partials.footer')
@endif

@include('partials.scripts')
@yield('page_scripts')

</body>
</html>
