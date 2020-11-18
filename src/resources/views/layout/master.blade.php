<!DOCTYPE html>
<html>
<head>
  <title>NobleUI Laravel Admin Dashboard Template</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="_token" content="{{ csrf_token() }}">

  <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

  <!-- plugin css -->
  <link href="{{ asset('assets/fonts/feather-font/css/iconfont.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />
  <!-- end plugin css -->

  @stack('plugin-styles')

  <!-- common css -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet" />

  <!-- end common css -->

  @stack('style')
</head>
<body data-base-url="{{url('/')}}">


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

  <script src="{{ asset('js/app.js') }}"></script>
  <script src="http://localhost:6001/socket.io/socket.io.js"></script>
  <script>

      function checkData() {

      }

      let data1 = [];
      console.log(Echo);
      let processId = "{{ $process->id }}";

      Echo.channel('process.1')
          .listen('.App\\Events\\ObjectsAppear', (res) => {
              console.log(res)
              if (data1.length === 0) {
                  data1 = res.data;
              } else {
                  // if (res.data.length !== 0) {
                  //     res.data.forEach(item => {
                  //         data1.push(item)
                  //     })
                  // }
                  // console.log(data1, res);
              }
              // console.log(data1, '12312s')
          });
      console.log(data1, 11)
  </script>

  <script src="{{ asset('assets/plugins/feather-icons/feather.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
  <!-- end base js -->
  <script src="{{ asset('assets/js/spinner.js') }}"></script>

  <!-- plugin js -->
  @stack('plugin-scripts')
  <!-- end plugin js -->

  <!-- common js -->
  <script src="{{ asset('assets/js/template.js') }}"></script>
  <!-- base js -->
  <!-- end common js -->

  @stack('custom-scripts')
</body>
</html>
