<!DOCTYPE html>
<html>
<head>
    <title>FaceAI Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="_token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ my_asset('/favicon.png') }}">

    <!-- plugin css -->
    <link href="{{ my_asset('assets/fonts/feather-font/css/iconfont.css') }}" rel="stylesheet"/>
    <link href="{{ my_asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css" id="theme-styles">

    <!-- end plugin css -->

    @stack('plugin-styles')

<!-- common css -->
    <link href="{{ my_asset('css/app.css') }}" rel="stylesheet"/>

    <!-- end common css -->

    @stack('style')
</head>
<body data-base-url="{{url('/')}}" data-echo-server="{{ env('ECHO_SERVER') }}">

<div class="main-wrapper" id="app">
    @include('layout.sidebar')

    <div class="page-wrapper">
        @include('layout.header')

        <div class="page-content">
            @yield('content')
        </div>

        @include('layout.footer')
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
<script src="{{ my_asset('js/app.js') }}"></script>
<script src="{{ my_asset('assets/plugins/feather-icons/feather.min.js') }}"></script>
<script src="{{ my_asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<!-- end base js -->
<script src="{{ my_asset('assets/js/spinner.js') }}"></script>

<!-- plugin js -->
@stack('plugin-scripts')
<!-- end plugin js -->

<!-- common js -->
<script src="{{ my_asset('assets/js/template.js') }}"></script>
<!-- base js -->
<!-- end common js -->

@stack('custom-scripts')
</body>
</html>
