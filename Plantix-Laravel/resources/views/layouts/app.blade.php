<!DOCTYPE html>
<html lang="en">
@include('partials.head')
<body>

@section('header')
@include('partials.header')
@show

@yield('content')

@section('footer')
@include('partials.footer')
@show

@include('partials.scripts')
@yield('page_scripts')

</body>
</html>
