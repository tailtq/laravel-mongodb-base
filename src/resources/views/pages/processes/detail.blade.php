@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet"/>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('processes') }}">Luồng xử lý</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
        </ol>
    </nav>

    <div class="row echo-server" data-echo-server="{{ env('ECHO_SERVER') }}">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Luồng xử lý chi tiết</div>
                <div style="display: inline-block">
                    <button type="button" class="btn btn-success btn-play">
                        <i class="link-icon" data-feather="play"></i>
                        Bắt đầu
                    </button>
                    <button type="button" class="btn btn-danger btn-stop">
                        {{--<i class="link-icon" data-feather="mdi mdi-stop"></i>.--}}
                        <i class="mdi mdi-stop"></i>
                        Kết thúc
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal">
                        <i class="link-icon" data-feather="settings"></i>
                        Cấu hình
                    </button>
                </div>
            </h6>

            <div class="table-responsive d-flex">
                <video controls="true" class="w-60" preload="auto"></video>

                <div class="w-100 ml-4">
                    <h6 class="mb-2">Cấu hình</h6>

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

            <div class="mt-4">
                <h6 class="mb-2">Danh sách đối tượng</h6>

                <div class="mb-4 socket-render">
                    <div class="media d-block mb-2 d-sm-flex">
                        <img src="https://www.nobleui.com/laravel/template/light/assets/images/placeholder.jpg"
                             class="wd-100p wd-sm-200 mb-3 mb-sm-0 mr-3" alt="...">
                        <div class="media-body">
                            <p class="mt-1 mb-2"><b>{{ 1 }}. Nicolas Tesla</b></p>

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
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @include('pages.processes.modal_process')
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/jquery-steps/jquery.steps.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dashjs/3.1.3/dash.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.3/dist/sweetalert2.all.min.js"></script>

@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>

        const processId = '{{ $process->id }}';

        $(document).ready(function () {
            $('.btn-stop').attr('disabled', true);
        });

        $('.btn-play').click(function(){
            console.log("START PROCESS");
            $.ajax({
                url: '/processes/start-process',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json; charset=UTF-8',
                data: JSON.stringify({
                    _token: $('meta[name="_token"]').attr('content'),
                    processId: processId
                }),
                success: function (res) {
                    processMessage('start');
                    $('.btn-play').attr('disabled', true);
                    $('.btn-stop').attr('disabled', false);
                },
                error: function (res) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Đã có lỗi xảy ra',
                        text: res.responseJSON.message,
                    });
                }
            });
        });

        $('.btn-stop').click(function(){
            console.log("STOP PROCESS");
            processMessage('stop');
            $('.btn-play').attr('disabled', false);
            $('.btn-stop').attr('disabled', true);
        });

        // function for alert message when click action play, stop
        function processMessage (type) {

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
                })
            } else {
                Toast.fire({
                    type: 'success',
                    title: 'Kết thúc thực thi'
                })
            }
        }

          // dash player
        function init() {
            const url = '{{ env('STREAMING_SERVER') }}/dev/streaming/{{ $process->mongo_id }}/dash_out.mpd';
            const video = document.querySelector('video');
            const player = dashjs.MediaPlayer().create();

            player.initialize(video, url, false);
        }
        document.addEventListener('DOMContentLoaded', function () {
            init();
        });
        // end dash player

        // render objects
        const totalFrames = parseInt('{{ $process->total_frames }}', 10);

        function buildProgressBar(times, totalFrames) {
        let bars = ``;
        let currentTime = 0;

        times.forEach(({ frameFrom, frameTo }) => {
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

        Echo.channel(`process.${processId}`).listen('.App\\Events\\ObjectsAppear', (res) => {
            res.data.forEach(value => {
                $('.socket-render').prepend(`
                    <div class="media d-block mb-2 d-sm-flex">
                        <img src="${value.image ? value.image : 'https://www.nobleui.com/laravel/template/light/assets/images/placeholder.jpg'}"
                             class="wd-100p wd-sm-200 mb-3 mb-sm-0 mr-3" alt="...">
                        <div class="media-body">
                            <p class="mt-1 mb-2"><b>${value.track_id}. ${value.name || 'Unknown'}</b></p>

                            ${buildProgressBar([{ frameFrom: value.frame_from, frameTo: value.frame_to }], totalFrames)}
                        </div>
                    </div>`);
            });
        });
    </script>
@endpush
