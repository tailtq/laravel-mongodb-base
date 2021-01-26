@extends('layout.master')

@push('plugin-styles')
    <link href="{{ my_asset('assets/plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/plugins/lightbox/css/lightbox.min.css') }}" rel="stylesheet"/>
    <link href="{{ my_asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet"/>
    <style>
        .popover .popover-body {
            padding: 2px 5px;
        }

        #videoModal {
            z-index: 2000;
        }

        .table td, .table th {
            white-space: normal !important;
        }

        .table td .badge {
            margin-bottom: 5px !important;
        }

        .original-avatar {
            max-width: 50px;
            max-height: 50px;
        }

        .original-body {
            width: 32px;
            height: 64px;
        }

        button.badge {
            border: none;
        }

        .popover {
            max-width: 1000px !important;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('processes') }}">Luồng xử lý</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $process->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="card-body w-100">
            <div class="d-flex justify-content-md-between align-items-center mb-3">
                <h5 class="card-title process"
                    data-id="{{ $process->id }}"
                    data-mongo-id="{{ $process->mongo_id }}">
                    {{ $process->name }}
                    &nbsp;
                    <span class="
                        badge
                        @if($process->status == 'error' || $process->status == 'stopped')
                            badge-danger
                        @else
                            badge-success
                        @endif
                            text-uppercase
                            process__status
                    ">
                        {{ __('status.' . $process->status, [], 'vi') }}
                    </span>
                </h5>

                <div style="display: inline-block">
                    <button type="button"
                            @if($process->status != 'ready')
                            disabled
                            @endif
                            class="btn btn-success btn-start">
                        <i class="link-icon icon__normal-size" data-feather="play"></i>
                        Bắt đầu
                    </button>

                    <button type="button"
                            @if($process->status != 'detecting' && $process->status != 'grouping')
                            disabled
                            @endif
                            class="btn btn-danger btn-stop">
                        <i class="link-icon icon__normal-size" data-feather="stop-circle"></i>
                        Kết thúc
                    </button>

                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal">
                        <i class="link-icon icon__normal-size" data-feather="settings"></i>
                        Cấu hình
                    </button>

                    <button type="button"
                            @if($process->status != 'done')
                            disabled
                            @endif
                            data-toggle="modal"
                            data-target="#searchFaceModal"
                            class="btn btn-primary btn-search text-white search-face__btn">
                        <i class="link-icon icon__normal-size" data-feather="search"></i>
                        Tìm kiếm
                    </button>

                    <div class="dropdown" style="display: inline-block">
                        <button class="btn btn-primary dropdown-toggle export-statistic__btn"
                                @if($process->status != 'done')
                                disabled
                                @endif
                                type="button"
                                id="dropdownMenuButton"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">
                            <i class="link-icon icon__normal-size" data-feather="download"></i>
                            Xuất dữ liệu
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item"
                               target="_blank"
                               href="{{ route('processes.export.before-grouping', ['id' => $process->id]) }}">
                                Trước nhất thể hoá
                            </a>
                            <a class="dropdown-item"
                               target="_blank"
                               href="{{ route('processes.export.after-grouping', ['id' => $process->id]) }}">
                                Sau nhất thể hoá
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex">
                <video class="videoElement w-100" style="max-width: 640px" controls></video>

                <div class="w-100 ml-4">
                    <h5 class="mt-1 mb-2">Thống kê</h5>

                    <table class="table table-bordered statistical-table">
                        <tr>
                            <th>Số lượt người xuất hiện</th>
                            <td class="statistic__total-appearances" width="30%">{{ $process->total_appearances }}</td>
                        </tr>
                        <tr>
                            <th>Số người</th>
                            <td class="statistic__total-objects" width="30%">{{ $process->total_objects }}</td>
                        </tr>
                        <tr>
                            <th>Số người phát hiện được danh tính</th>
                            <td class="statistic__total-identified" width="30%">{{ $process->total_identified }}</td>
                        </tr>
                        <tr>
                            <th>Số người không phát hiện được danh tính</th>
                            <td class="statistic__total-unidentified" width="30%">{{ $process->total_unidentified }}</td>
                        </tr>
                        <tr>
                            <th>Thời gian nhận diện</th>
                            <td class="process__detecting-duration" width="30%">
                                {{ $process->detecting_duration }}
                            </td>
                        </tr>
                        {{--                        <tr>--}}
                        {{--                            <th>Thời gian kiểm tra định danh</th>--}}
                        {{--                            <td class="process__matching-duration" width="30%">--}}
                        {{--                                {{ $process->matching_duration }}--}}
                        {{--                            </td>--}}
                        {{--                        </tr>--}}
                        {{--                        <tr>--}}
                        {{--                            <th>Thời gian tổng hợp video</th>--}}
                        {{--                            <td class="process__rendering-duration" width="30%">--}}
                        {{--                                {{ $process->rendering_duration }}--}}
                        {{--                            </td>--}}
                        {{--                        </tr>--}}
                        <tr>
                            <th>Tổng thời gian</th>
                            <td class="process__total-duration" width="30%">
                                {{ $process->total_duration }}
                            </td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-between mt-3">
                        <div class="form-check form-check-flat form-check-primary my-2">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="hide-unknown">
                                Ẩn đối tượng không xác định
                                <i class="input-frame"></i>
                            </label>
                        </div>

                        <div>
                            <button class="btn {{ $process->video_result ? 'btn-success' : 'btn-secondary' }} render-video__btn"
                               @if($process->status != 'done')
                               disabled
                               @endif
                               data-href="{{ $process->video_result }}">
                                Tổng hợp video
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="process__progress-bar mt-4">
                <h5 class="mb-2">Tiến trình thực hiện</h5>

                <p>Nhận diện đối tượng</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar__detecting"
                         role="progressbar"
                         style="width: {{ $detectingPercentage }}%"
                         aria-valuenow="{{ $detectingPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $detectingPercentage }}%
                    </div>
                </div>

                <p class="mt-4">Tổng hợp video</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped bg-warning progress-bar__rendering"
                         role="progressbar"
                         style="width: {{ $renderingPercentage }}%"
                         aria-valuenow="{{ $renderingPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $renderingPercentage }}%
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="mb-2">Danh sách đối tượng</h5>

                <div class="mb-4 socket-render">
                    <div class="pt-3">
                        <table class="table table-responsive table-bordered">
                            <thead>
                            <tr>
                                <th width="5%" class="text-center">Id</th>
                                <th width="7%" class="text-center">Ảnh</th>
                                <th width="15%" class="text-center">Ảnh CMND đối chiếu</th>
                                <th width="20%">Tên đối tượng</th>
                                <th>Thời gian xuất hiện</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                                {{-- Insert objects here --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                <p class="socket__message">Hiện tại chưa có đối tượng được theo dõi</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <video controls class="w-100 h-100" preload="auto" autoplay></video>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="searchFaceModal" tabindex="-1" role="dialog" aria-labelledby="searchFaceModal"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Tìm kiếm đối tượng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="dropzone search-face__dropzone">
                        <div class="dz-message">Kéo ảnh vào đây để tải lên</div>
                    </div>

                    <div class="form-group d-flex">
                        <p style="height: 29px; margin: 10px 10px 10px 0">Loại tìm kiếm: </p>

                        <div class="form-check form-check-flat form-check-primary mr-4">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" value="face" name="search_type" checked>
                                Khuôn mặt
                                <i class="input-frame"></i>
                            </label>
                        </div>

                        <div class="form-check form-check-flat form-check-primary">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" value="body" name="search_type">
                                Thân hình
                                <i class="input-frame"></i>
                            </label>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-primary dropzone-submit">Tìm kiếm</button>
                    </div>

                    <div class="search-face__result" style="display: none">
                        <hr>
                        <h5 class="mb-4">Kết quả tìm kiếm:</h5>
                        <ul class="list-unstyled"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @include('pages.processes.modal_process')
@endsection

@push('plugin-scripts')
    <script src="{{ my_asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/jquery-steps/jquery.steps.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/lightbox/js/lightbox.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.3/dist/sweetalert2.all.min.js"></script>
@endpush

@push('custom-scripts')
    <script src="{{ my_asset('js/custom.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/flv/flv.min.js') }}"></script>
    <script src="{{ my_asset('js/shapes/circle.js') }}"></script>
    <script src="{{ my_asset('js/geometry.js') }}"></script>
    <script>
        // define global variables
        const processId = '{{ $process->id }}';
        const allStatus = <?= json_encode(__('status', [], 'vi'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        // render objects
        const frameDrop = {{ object_get($process->config, 'frame_drop', 1) }};
        const fps = Math.round(parseInt('{{ $process->fps }}', 10) / frameDrop);
        // const renderHour = totalFrames / fps / 3600 >= 1;
        const renderHour = parseInt('{{ $process->camera_id ? 1 : 0 }}');
        const isRealtime = parseInt('{{ $process->camera_id ? 1 : 0 }}');
        let globalStatus = '{{ $process->status }}';

        isDrawing = false;
        loadCanvas();
        loadOldRegions();

        const videoElement = document.getElementsByClassName('videoElement')[0];
        const flvPlayer = flvjs.createPlayer({
            type: 'flv',
            isLive: true,
            url: '{{ env('STREAMING_SERVER') }}/{{ $process->mongo_id }}.flv'
        });
        flvPlayer.attachMediaElement(videoElement);

        if (globalStatus === 'detecting') {
            flvPlayer.load();
            flvPlayer.play();
        }
    </script>
    <script src="{{ my_asset('js/detail.js') }}"></script>
@endpush
