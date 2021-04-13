@extends('layout.master')

@push('plugin-styles')
    <link href="{{ my_asset('assets/plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/plugins/lightbox/css/lightbox.min.css') }}" rel="stylesheet"/>
    <link href="{{ my_asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet"/>
    <link href="{{ my_asset('css/custom.css') }}" rel="stylesheet"/>
    <style>
        #videoModal {
            z-index: 2000;
        }

        .table td, .table th {
            white-space: normal !important;
        }

        .table td .badge {
            margin-bottom: 5px !important;
        }
    </style>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('processes') }}">Luồng xử lý</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="card-body w-100">
            <div class="d-flex justify-content-md-between align-items-center mb-3">
                <h5 class="card-title process"
                    data-id="{{ $item->id }}">
                    {{ $item->name }}
                    &nbsp;
                    <span class="
                        badge
                        @if($item->status == 'error' || $item->status == 'stopped')
                            badge-danger
                        @else
                            badge-success
                        @endif
                            text-uppercase
                            process__status
                    ">
                        {{ __('status.' . $item->status, [], 'vi') }}
                    </span>
                </h5>

                <div style="display: inline-block">
                    <button type="button"
                            @if($item->status != 'ready')
                            disabled
                            @endif
                            class="btn btn-success btn-start">
                        <i class="link-icon icon__normal-size" data-feather="play"></i>
                        Bắt đầu
                    </button>

                    <button type="button"
                            @if($item->status != 'detecting' && $item->status != 'grouping')
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
                            data-toggle="modal"
                            data-target="#searchFaceModal"
                            class="btn btn-primary btn-search text-white search-face__btn">
                        <i class="link-icon icon__normal-size" data-feather="search"></i>
                        Tìm kiếm
                    </button>

                    <div class="dropdown" style="display: inline-block">
                        <button class="btn btn-primary dropdown-toggle export-statistic__btn"
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
                               href="{{ route('processes.export.before-grouping', ['id' => $item->id]) }}">
                                Trước nhất thể hoá
                            </a>
                            <a class="dropdown-item"
                               target="_blank"
                               href="{{ route('processes.export.after-grouping', ['id' => $item->id]) }}">
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
                            <td class="statistic__total-appearances" width="30%">{{ $item->statistic['total_appearances'] }}</td>
                        </tr>
                        <tr>
                            <th>Số người</th>
                            <td class="statistic__total-objects" width="30%">{{ $item->statistic['total_objects'] }}</td>
                        </tr>
                        <tr>
                            <th>Số người phát hiện được danh tính</th>
                            <td class="statistic__total-identified" width="30%">{{ $item->statistic['total_identified'] }}</td>
                        </tr>
                        <tr>
                            <th>Số người không phát hiện được danh tính</th>
                            <td class="statistic__total-unidentified" width="30%">{{ $item->statistic['total_unidentified'] }}</td>
                        </tr>
                        <tr>
                            <th>Thời gian nhận diện</th>
                            <td class="process__detecting-duration" width="30%">
                                {{ $item->detecting_duration }}
                            </td>
                        </tr>
                        {{--                        <tr>--}}
                        {{--                            <th>Thời gian kiểm tra định danh</th>--}}
                        {{--                            <td class="process__matching-duration" width="30%">--}}
                        {{--                                {{ $item->matching_duration }}--}}
                        {{--                            </td>--}}
                        {{--                        </tr>--}}
                        {{--                        <tr>--}}
                        {{--                            <th>Thời gian tổng hợp video</th>--}}
                        {{--                            <td class="process__rendering-duration" width="30%">--}}
                        {{--                                {{ $item->rendering_duration }}--}}
                        {{--                            </td>--}}
                        {{--                        </tr>--}}
                        <tr>
                            <th>Tổng thời gian</th>
                            <td class="process__total-duration" width="30%">
                                {{ $item->total_duration }}
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
                            <button class="btn {{ $item->video_result ? 'btn-success' : 'btn-secondary' }} render-video__btn"
                               @if($item->status != 'done' && $item->status != 'stopped')
                               disabled
                               @endif
                               data-href="{{ $item->video_result }}">
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
                         style="width: {{ $item->detecting_progress ?? 0 }}%"
                         aria-valuenow="{{ $item->detecting_progress ?? 0 }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $item->detecting_progress }}%
                    </div>
                </div>

                <p class="mt-4">Tổng hợp video</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped bg-warning progress-bar__rendering"
                         role="progressbar"
                         style="width: {{ $item->video_result ? 100 : 0 }}%"
                         aria-valuenow="{{ $item->video_result ? 100 : 0 }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $item->video_result ? 100 : 0 }}%
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

    <!-- Modal -->
    @include('pages.processes.saved_process_parameters')
    @include('pages.processes.search_form')
@endsection

@push('plugin-scripts')
    <script src="{{ my_asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/jquery-steps/jquery.steps.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/lightbox/js/lightbox.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.3/dist/sweetalert2.all.min.js"></script>
@endpush

@push('custom-scripts')
    <script src="{{ my_asset('js/util.js') }}"></script>
    <script src="{{ my_asset('js/search_form.js') }}"></script>
    <script src="{{ my_asset('js/custom.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/flv/flv.min.js') }}"></script>
    <script src="{{ my_asset('js/shapes/circle.js') }}"></script>
    <script src="{{ my_asset('js/geometry.js') }}"></script>
    <script>
        // define global variables
        const processId = '{{ $item->id }}';
        const allStatus = <?= json_encode(__('status', [], 'vi'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        // render objects
        const frameDrop = {{ object_get($item->config, 'frame_drop', 1) }};
        const fps = Math.round(parseInt('{{ $item->fps }}', 10) / frameDrop);
        // const renderHour = totalFrames / fps / 3600 >= 1;
        const renderHour = parseInt('{{ $item->cameraRelation ? 1 : 0 }}');
        const isRealtime = parseInt('{{ $item->cameraRelation ? 1 : 0 }}');
        let globalStatus = '{{ $item->status }}';

        isDrawing = false;
        loadCanvas();
        loadOldRegions();

        const videoElement = document.getElementsByClassName('videoElement')[0];
        const flvPlayer = flvjs.createPlayer({
            type: 'flv',
            isLive: true,
            url: '{{ env('STREAMING_SERVER') }}/{{ $item->id }}.flv'
        });
        flvPlayer.attachMediaElement(videoElement);

        if (globalStatus === 'detecting') {
            flvPlayer.load();
            flvPlayer.play();
        }
    </script>
    <script src="{{ my_asset('js/detail.js') }}"></script>
@endpush
