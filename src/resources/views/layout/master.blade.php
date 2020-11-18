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

      console.log(Echo);
      let processId = "{{ $process->id }}";


      Echo.channel('process.' + processId)
          .listen('.App\\Events\\ObjectsAppear', (res) => {
              // console.log(res)
              let result = [];
              if (result.length === 0) {
                  result = res.data;
              } else {
                  if (res.data.length !== 0) {
                      let result = [];
                      res.data.forEach(item => {
                          result.push(item)
                      })
                  }
              }

              result.forEach((value, index) => {
                  console.log(value, index);

                  $('.socket-render').append(
                      `
                    <div class="media d-block mb-2 d-sm-flex">
                        <img src="${value['image'] ? value['image'] : 'https://www.nobleui.com/laravel/template/light/assets/images/placeholder.jpg'}" class="wd-100p wd-sm-200 mb-3 mb-sm-0 mr-3" alt="...">
                        <div class="media-body">
                            <p class="mt-1 mb-2"><b>${value['process_id']}. Nicolas Tesla</b></p>

                            <div class="progress ht-10">
                                <div class="progress-bar bg-success" role="progressbar"
                                     data-toggle="tooltip"
                                     style="width: 15%"
                                     aria-valuenow="15"
                                     aria-valuemin="0"
                                     aria-valuemax="100" title="hello"></div>

                                <div class="progress-bar bg-transparent" role="progressbar"
                                     data-toggle="tooltip"
                                     style="width: 30%"
                                     aria-valuenow="30"
                                     aria-valuemin="0"
                                     aria-valuemax="100" title="hello"></div>

                                <div class="progress-bar bg-success" role="progressbar"
                                     data-toggle="tooltip"
                                     style="width: 20%"
                                     aria-valuenow="20"
                                     aria-valuemin="0"
                                     aria-valuemax="100" title="hello"></div>
                            </div>
                        </div>
                    </div>
                      `
                  );
                  // $('.image-links').append(`
                  //     <div>
                  //       <input type="hidden" name="images[${startIndex + index}][url]" value="${url}">
                  //     </div>
                  //   `);
                  // $('.images-visualization').append(`
                  //       <div class="col-md-4 mb-2">
                  //         <img src="${url}" alt="" class="img-fluid">
                  //       </div>
                  //     `)
              });
          });
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
