@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('assets/plugins/@mdi/css/materialdesignicons.min.css') }}">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('processes') }}">Luồng xử lý</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>

    <div class="row">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Luồng xử lý chi tiết</div>
                <div style="display: inline-block">
                    <button type="button"
                            @if($process->status == 'detecting' || $process->status == 'grouping')
                                disabled
                            @endif
                            class="btn btn-success btn-start">
                        <i class="link-icon" data-feather="play" style="width: 15px; height: 15px;"></i>
                        Bắt đầu
                    </button>

                    <button type="button"
                            @if($process->status != 'detecting' && $process->status != 'grouping')
                                disabled
                            @endif
                            class="btn btn-danger btn-stop">
                        <i class="mdi mdi-stop" style="font-size: 15px;"></i>
                        Kết thúc
                    </button>

                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal">
                        <i class="link-icon" data-feather="settings" style="width: 15px; height: 15px;"></i>
                        Cấu hình
                    </button>
                </div>
            </h5>

            <div class="table-responsive d-flex">
                <video controls class="w-60" preload="auto" autoplay>
                    <source src="{{ $process->video_url }}" type="video/mp4">
                </video>

                <div class="w-100 ml-4">
                    <h5 class="mb-2">Cấu hình</h5>

                    <table class="table table-bordered">
                        <tr>
                            <th>Ngưỡng so sánh sinh trắc</th>
                            <td>{{ object_get($process->mongoData, 'biometric_threshold', 0) }}%</td>
                        </tr>
                        <tr>
                            <th>Độ chính xác đầu tối thiểu</th>
                            <td>{{ object_get($process->mongoData, 'min_head_confidence', 0) }}%</td>
                        </tr>
                        <tr>
                            <th>Độ chính xác khuôn mặt tối thiểu</th>
                            <td>{{ object_get($process->mongoData, 'min_face_confidence', 0) }}%</td>
                        </tr>
                        <tr>
                            <th>Độ chính xác thân hình tối thiểu</th>
                            <td>{{ object_get($process->mongoData, 'min_body_confidence', 0) }}%</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="process__progress-bar mt-4">
                <h5 class="mb-2">Tiến trình thực hiện</h5>

                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated progress-bar__detecting"
                         data-toggle="popover"
                         data-placement="bottom"
                         role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                         data-content="Nhận diện đối tượng">
                    </div>

                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success progress-bar__grouping"
                         data-toggle="popover"
                         data-placement="bottom"
                         role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                         data-content="Nhất thể hoá">
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="mb-2">Danh sách đối tượng</h5>

                <div class="mb-4 socket-render"></div>

                <p class="socket__message">Hiện tại chưa có đối tượng được theo dõi</p>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @include('pages.processes.modal_process')
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/jquery-steps/jquery.steps.min.js') }}"></script>
    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/dashjs/3.1.3/dash.all.min.js"></script>--}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.3/dist/sweetalert2.all.min.js"></script>

@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>
      const processId = '{{ $process->id }}';

      function renderData() {
        $.ajax({
          url: `/processes/${processId}/objects`,
          type: 'GET',
          success: function (res) {
            console.log(res);
          },
        })
      }

      // function for alert message when click action play, stop
      function processMessage(type) {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000,
        });

        if (type === 'start') {
          Toast.fire({
            type: 'success',
            title: 'Bắt đầu thực thi'
          });
        } else {
          Toast.fire({
            type: 'success',
            title: 'Kết thúc thực thi'
          });
        }
      }

      function sendStartStopRequest(processId, type) {
        $.ajax({
          url: `/processes/${type}-process`,
          type: 'POST',
          dataType: 'json',
          contentType: 'application/json; charset=UTF-8',
          data: JSON.stringify({
            _token: $('meta[name="_token"]').attr('content'),
            processId: processId
          }),
          success: function (res) {
            const otherType = type === 'start' ? 'stop' : 'start';

            $(`.btn-${type}`).attr('disabled', true);
            $(`.btn-${otherType}`).attr('disabled', false);

            processMessage(type);
          },
          error: function (res) {
            Toast.fire({
              type: 'error',
              title: 'Đã có lỗi xảy ra'
            });
          }
        });
      }

      $('.btn-start').click(function () {
        sendStartStopRequest(processId, 'start');
      });

      $('.btn-stop').click(function () {
        sendStartStopRequest(processId, 'stop');
      });

      // dash player
      {{--function init() {--}}
      {{--  --}}{{--const url = '{{ env('STREAMING_SERVER') }}/dev/streaming/{{ $process->mongo_id }}/dash_out.mpd';--}}
      {{--  const url = '{{ $process->video_url }}';--}}
      {{--  const video = document.querySelector('video');--}}
      {{--  const player = dashjs.MediaPlayer().create();--}}

      {{--  player.initialize(video, url, false);--}}
      {{--}--}}

      {{--document.addEventListener('DOMContentLoaded', function () {--}}
      {{--  init();--}}
      {{--});--}}
      // end dash player

      // render objects
      const totalFrames = parseInt('{{ $process->total_frames }}', 10);
      const fps = parseInt('{{ $process->fps }}', 10);
      const renderHour = totalFrames / 3600 >= 1;

      function buildProgressBar(times, totalFrames) {
        let bars = ``;
        let currentTime = 0;

        times.forEach(({frameFrom, frameTo}) => {
          const length = frameTo - frameFrom;
          const transparentLength = frameFrom - currentTime;

          bars += `
            <div class="progress-bar bg-transparent" role="progressbar"
                 data-toggle="tooltip"
                 style="width: ${transparentLength / totalFrames * 100}%"
                 title="hello"></div>

            <div class="progress-bar bg-success" role="progressbar"
                 data-toggle="tooltip"
                 style="width: ${length / totalFrames * 100}%"
                 title="hello"></div>
            `;
          currentTime += frameTo;
        });

        return `<div class="progress ht-10">${bars}</div>`;
      }

      function getTimeString(frameFrom, frameTo, fps, renderHour) {
        let secondFrom = Math.floor(frameFrom / fps);
        let minFrom = Math.floor(secondFrom / 60);
        const hourFrom = (Math.floor(minFrom / 60)).toString().padStart(2, '0');
        minFrom = (minFrom % 60).toString().padStart(2, '0');
        secondFrom = (secondFrom % 60).toString().padStart(2, '0');

        let secondTo = Math.floor(frameTo / fps);
        let minTo = Math.floor(secondTo / 60);
        const hourTo = (Math.floor(minTo / 60)).toString().padStart(2, '0');
        minTo = (minTo % 60).toString().padStart(2, '0');
        secondTo = (secondTo % 60).toString().padStart(2, '0');

        return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - ${renderHour ? `${hourTo}:` : ''}${minTo}:${secondTo}`
      }

      Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectsAppear', (res) => {
        $('.socket__message').remove();

        res.data.forEach(value => {
          const {frame_from: frameFrom, frame_to: frameTo} = value;

          $('.socket-render').prepend(`
            <div class="media d-block mb-2 d-sm-flex">
                <img src="${value.image ? value.image : 'https://www.nobleui.com/laravel/template/light/assets/images/placeholder.jpg'}"
                     class="wd-100p wd-sm-200 mb-3 mb-sm-0 mr-3" alt="...">
                <div class="media-body">
                    <p class="mt-1 mb-2"><b>${value.name || 'Unknown'}</b> &nbsp; ${getTimeString(frameFrom, frameTo, fps, renderHour)}</p>

                    ${buildProgressBar([{frameFrom, frameTo}], totalFrames)}
                </div>
            </div>
          `);
        });
      });

      Echo.channel(`process.${processId}.progress`).listen('.App\\Events\\ProgressChange', (res) => {
        const {status, progress} = res.data;
        const $detecting = $('.progress-bar__detecting');

        if (status === 'grouping' && parseFloat($detecting.attr('aria-valuenow')) === 0) {
          $detecting.css({width: '50%'});
          $detecting.attr('aria-valuenow', '100');

          setTimeout(() => $detecting.popover('show'), 400);
        }
        if (status === 'detecting' || status === 'grouping') {
          const $element = $(`.progress-bar__${status}`);

          $element.css({width: `${progress / 2}%`});
          $element.attr('aria-valuenow', progress);

          setTimeout(() => $element.popover('show'), 400);
        }
      });

      $(document).ready(function () {
        renderData();

        $('.process__progress-bar .progress-bar').each((index, element) => {
          const value = parseFloat($(element).attr('aria-valuenow'));

          if (value > 0) {
            $(element).popover('show');
          }
        });
      });
    </script>
@endpush
