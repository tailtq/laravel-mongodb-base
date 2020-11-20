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
            <div class="d-flex justify-content-md-between align-items-center mb-3">
                <h5 class="card-title">
                    Luồng xử lý chi tiết &nbsp;
                    <span class="badge badge-success text-uppercase process__status">{{ __('status.' . $process->status, [], 'vi') }}</span>
                </h5>
                <div style="display: inline-block">
                    <button type="button"
                            @if($process->status != 'ready')
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
            </div>

            <div class="table-responsive d-flex">
                <video controls class="w-60" preload="auto" autoplay>
                    <source src="{{ $process->video_url }}" type="video/mp4">
                </video>

                <div class="w-100 ml-4">
                    <h5 class="mb-2">Thống kê</h5>

                    <table class="table table-bordered">
                        <tr>
                            <th>Số lượng đối tượng</th>
                            <td class="process__total-objects" width="80"></td>
                        </tr>
                    </table>

                    <h5 class="mt-4 mb-2">Cấu hình</h5>

                    <table class="table table-bordered">
                        <tr>
                            <th>Ngưỡng so sánh sinh trắc</th>
                            <td width="80">{{ object_get($process->mongoData, 'biometric_threshold', 0) }}%</td>
                        </tr>
                        <tr>
                            <th>Độ chính xác đầu tối thiểu</th>
                            <td width="80">{{ object_get($process->mongoData, 'min_head_confidence', 0) }}%</td>
                        </tr>
                        <tr>
                            <th>Độ chính xác khuôn mặt tối thiểu</th>
                            <td width="80">{{ object_get($process->mongoData, 'min_face_confidence', 0) }}%</td>
                        </tr>
                        <tr>
                            <th>Độ chính xác thân hình tối thiểu</th>
                            <td width="80">{{ object_get($process->mongoData, 'min_body_confidence', 0) }}%</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="process__progress-bar mt-4">
                <h5 class="mb-2">Tiến trình thực hiện</h5>

                @php
                    $detectingPercentage = 0;
                    $groupingPercentage = 0;
                @endphp
                @if ($process->status == 'done')
                    @php $detectingPercentage = 100; @endphp
                @endif

                <p>Nhận diện đối tượng</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated progress-bar__detecting"
                         role="progressbar"
                         style="width: {{ $detectingPercentage }}%"
                         aria-valuenow="{{ $detectingPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $detectingPercentage }}%
                    </div>
                </div>

                <p class="mt-4">Nhất thể hoá</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success progress-bar__grouping"
                         role="progressbar"
                         style="width: {{ $groupingPercentage }}%"
                         aria-valuenow="{{ $groupingPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $groupingPercentage }}%
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="mb-2">Danh sách đối tượng</h5>

                <div class="mb-4 socket-render">
                    <div class="table-responsive pt-3">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th width="7%" class="text-center">Ảnh</th>
                                <th width="25%">Tên đối tượng</th>
                                <th>Thời gian xuất hiện</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

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
      const allStatus = <?= json_encode(__('status', [], 'vi'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      let totalObjects = 0;

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
            const Toast = Swal.mixin({
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 2000,
            });
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

        times.forEach(({frame_from: frameFrom, frame_to: frameTo}) => {
          const length = frameTo - frameFrom;
          const transparentLength = frameFrom - currentTime;

          bars += `
            <div class="progress-bar bg-transparent" role="progressbar"
                 data-toggle="tooltip"
                 style="width: ${transparentLength / totalFrames * 100}%"
                 title="hello"></div>

            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                 data-toggle="tooltip"
                 style="width: ${length / totalFrames * 100}%"
                 title="hello"></div>
            `;
          currentTime += frameTo;
        });

        return `<div class="progress ht-15">${bars}</div>`;
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

      function renderBlock(object, appearances, fps, renderHour, index) {
        return (`
            <tr>
                <td>${index + 1}</td>
                <td class="py-1 text-center">
                    <img src="${object.image}" alt="image">
                </td>
                <td>${object.name || 'Unknown'}</td>
                <td>
                    ${buildProgressBar(appearances, totalFrames, fps, renderHour)}
                </td>
            </tr>
        `);
      }

      function renderData() {
        $.ajax({
          url: `/processes/${processId}/objects`,
          type: 'GET',
          success: function (res) {
            $('.process__total-objects').html(totalObjects);

            const { length: total } = res.data;

            totalObjects += total;

            res.data.forEach((value, index) => {
              $('.socket-render tbody').prepend(
                // for not appending to last index in the same time
                renderBlock(value, value.appearances, fps, renderHour, total - index - 1)
              );
            });
          },
        })
      }

      Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectsAppear', (res) => {
        $('.socket__message').remove();

        $('.process__total-objects').html(totalObjects);

        res.data.forEach((value, index) => {
          $('.socket-render tbody').append(renderBlock(value, [value], fps, renderHour, totalObjects + index));
        });

        totalObjects += res.data.length;
      });

      Echo.channel(`process.${processId}.progress`).listen('.App\\Events\\ProgressChange', (res) => {
        const {status, progress} = res.data;
        const $detecting = $('.progress-bar__detecting');

        $('.process__status').text(allStatus[status]);

        if (status === 'grouping' && parseFloat($detecting.attr('aria-valuenow')) === 0) {
          $detecting.css({ width: '100%' });
          $detecting.attr('aria-valuenow', '100');
          $detecting.text('100%');

          setTimeout(() => $detecting.popover('show'), 400);
        }
        if (status === 'detecting' || status === 'grouping') {
          const $element = $(`.progress-bar__${status}`);

          $element.css({width: `${progress}%`});
          $element.attr('aria-valuenow', progress);
          $element.text(`${progress}%`);

          setTimeout(() => $element.popover('show'), 400);
        }
      });

      $(document).ready(function () {
        renderData();
      });
    </script>
@endpush
