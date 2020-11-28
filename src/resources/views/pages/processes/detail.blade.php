@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('assets/plugins/@mdi/css/materialdesignicons.min.css') }}">
    <style>
        .status-overlay {
            top: 3px;
            padding: inherit;
            width: calc(100% - 1.875rem);
            text-align: center;
            font-size: 12px;
            z-index: -1;
        }

        .status-overlay.increasing {
            z-index: 100;
        }
    </style>
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
                    <span class="
                        badge
                        @if($process->status == 'error' || $process->status == 'stopped')
                            badge-danger
                        @else
                            badge-success
                        @endif
                        text-uppercase
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
                <video controls class="w-60" preload="auto">
                    <source src="{{ $process->video_url }}" type="video/mp4">
                </video>

                <div class="w-100 ml-4">
                    <h5 class="mb-2">Thống kê</h5>

                    <table class="table table-bordered">
                        <tr>
                            <th>Số lượng đối tượng</th>
                            <td class="process__total-objects" width="80">{{ $process->ungrouped_count }}</td>
                        </tr>
                        <tr>
                            <th>Số lượng đối tượng đã gom nhóm</th>
                            <td width="80">{{ $process->grouped_count }}</td>
                        </tr>
                        <tr>
                            <th>Số lượng đối tượng xác định</th>
                            <td width="80">{{ $process->identified_count }}</td>
                        </tr>
                        <tr>
                            <th>Số lượng đối tượng không xác định</th>
                            <td width="80">{{ $process->unidentified_count }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="process__progress-bar mt-4">
                <h5 class="mb-2">Tiến trình thực hiện</h5>



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

                <p class="mt-4">Kiểm tra định danh</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success progress-bar__matching"
                         role="progressbar"
                         style="width: {{ $matchingPercentage }}%"
                         aria-valuenow="{{ $matchingPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $matchingPercentage }}%
                    </div>
                </div>
            </div>

            <p class="mt-4">Tổng hợp video</p>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning progress-bar__rendering"
                     role="progressbar"
                     style="width: {{ $renderingPercentage }}%"
                     aria-valuenow="{{ $renderingPercentage }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                    {{ $renderingPercentage }}%
                </div>
            </div>

            <div class="mt-5">
                <h5 class="mb-2">Danh sách đối tượng</h5>

                <div class="mb-4 socket-render">
                    <div class="table-responsive pt-3">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th width="5%" class="text-center">Id</th>
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
                    if (type === 'start') {
                        $('.btn-start').attr('disabled', true);
                        $('.btn-stop').attr('disabled', false);
                    } else if (type === 'stop') {
                        $('.btn-stop').attr('disabled', true);
                    }
                    processMessage(type);
                },
                error: function ({responseJSON: res}) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    Toast.fire({
                        type: 'error',
                        title: res.message
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

        // render objects
        const frameStep = {{ object_get($process->mongoData, 'frame_step', 1) }};
        const totalFrames = Math.round(parseInt({{ $process->total_frames }}, 10) / frameStep);
        const fps = Math.round(parseInt('{{ $process->fps }}', 10) / frameStep);
        const renderHour = totalFrames / 3600 >= 1;
        let currentFrame = 0;
        let trackIds = [];

        function buildProgressBar(times, totalFrames, fps, renderHour, shouldIncreasing = false) {
            let bars = ``;
            let currentTime = 0;

            times.forEach(({frame_from: frameFrom, frame_to: frameTo}) => {
                const length = frameTo - frameFrom;
                const transparentLength = frameFrom - currentTime;

                // for shouldIncreasing case, we just render a red slice of the bar
                bars += `
                    <div class="progress-bar bg-transparent" role="progressbar"
                         data-toggle="tooltip"
                         style="width: ${transparentLength / totalFrames * 100}%"
                         title="hello"></div>

                    <div class="progress-bar progress-bar-striped progress-bar-animated ${shouldIncreasing ? 'bg-danger' : 'bg-success'}" role="progressbar"
                         data-toggle="tooltip"
                         data-frame-from="${frameFrom}"
                         style="width: ${shouldIncreasing ? 1 : (length / totalFrames * 100)}%"
                         title="${getTimeString(frameFrom, frameTo, fps, renderHour)}"></div>
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

            if (!frameTo) {
                return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - now`;
            }

            let secondTo = Math.floor(frameTo / fps);
            let minTo = Math.floor(secondTo / 60);
            const hourTo = (Math.floor(minTo / 60)).toString().padStart(2, '0');
            minTo = (minTo % 60).toString().padStart(2, '0');
            secondTo = (secondTo % 60).toString().padStart(2, '0');

            return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - ${renderHour ? `${hourTo}:` : ''}${minTo}:${secondTo}`;
        }

        function renderBlock(object, appearances, fps, renderHour, shouldIncreasing) {
            return (`
                <tr data-track-id="${object.track_id}" data-id="${object.id}">
                    <td class="text-center">${object.track_id}</td>
                    <td class="py-1 text-center">
                        <img src="${object.image}" alt="image" style="width: 40px; height: 40px;">
                    </td>
                    <td>${object.name || 'Unknown'}</td>
                    <td class="position-relative">
                        ${buildProgressBar(appearances, totalFrames, fps, renderHour, shouldIncreasing)}
                        <div class="status-overlay position-absolute ${shouldIncreasing ? 'increasing' : ''}">
                            ${shouldIncreasing ? 'Đang nhận diện' : ''}
                        </div>
                    </td>
                </tr>
            `);
        }

        function renderBlockInOrder(html, order, trackIds) {
            let index;

            if (order !== 0) {
                index = order - 1;
            }
            if (order === 0) {
                $('.socket-render tbody').prepend(html);
            } else {
                const prevTrackId = trackIds[index];
                const $element = $(`.socket-render tbody tr[data-track-id="${prevTrackId}"]`);

                if ($element.length === 0) {
                    $('.socket-render tbody').append(html);
                } else {
                    $element.after(html);
                }
            }
        }

        function insertInOrder(element, array) {
            array.push(element);
            array.sort(function (a, b) {
                return a - b;
            });

            return [array, array.indexOf(element)];
        }

        function renderData() {
            $.ajax({
                url: `/processes/${processId}/objects`,
                type: 'GET',
                success: function (res) {
                    if (res.data.length > 0) {
                        $('.socket__message').remove();
                    }

                    res.data.forEach((value) => {
                        [trackIds, trackIndex] = insertInOrder(value.track_id, trackIds);

                        renderBlockInOrder(
                            renderBlock(value, value.appearances, fps, renderHour, value.appearances[0].frameTo === null),
                            trackIndex,
                            trackIds
                        );
                    });
                },
            });
        }

        Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectsAppear', (res) => {
            $('.socket__message').remove();

            res.data.forEach((value) => {
                if (trackIds.indexOf(value.track_id) >= 0) {
                    $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:last-child`).html(`
                        ${buildProgressBar([value], totalFrames, fps, renderHour, false)}
                        <div class="status-overlay position-absolute"></div>
                    `);
                    if (value.name) {
                        $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:nth-child(3)`).text(value.name);
                    }
                } else {
                    [trackIds, trackIndex] = insertInOrder(value.track_id, trackIds);

                    renderBlockInOrder(
                        renderBlock(value, [value], fps, renderHour, true),
                        trackIndex,
                        trackIds
                    );
                }
            });

            $('.process__total-objects').html(trackIds.length);
        });

        Echo.channel(`process.${processId}.progress`).listen('.App\\Events\\ProgressChange', (res) => {
            const {status, progress, total, frame_index: frameIndex} = res.data;
            const $detecting = $('.progress-bar__detecting');

            if (!isNaN(frameIndex)) {
                currentFrame = frameIndex;
            }

            if (allStatus[status]) {
                $('.process__status').text(allStatus[status]);
            }

            if (status === 'grouping' && parseFloat($detecting.attr('aria-valuenow')) === 0) {
                $detecting.css({width: '100%'});
                $detecting.attr('aria-valuenow', '100');
                $detecting.text('100%');

                setTimeout(() => $detecting.popover('show'), 400);
            }
            if (status === 'detecting' || status === 'rendering') {
                const $element = $(`.progress-bar__${status}`);

                $element.css({width: `${progress}%`});
                $element.attr('aria-valuenow', progress);
                $element.text(`${progress}%`);

                setTimeout(() => $element.popover('show'), 400);
            }
            if (status === 'matching') {
                const $element = $(`.progress-bar__${status}`);
                const percentage = trackIds.length > 0 ? total / trackIds.length * 100 : 0;

                $element.css({width: `${percentage}%`});
                $element.attr('aria-valuenow', progress);
                $element.text(`${parseInt(percentage, 10)}%`);
            }
            if (status === 'grouped') {
                $.ajax({
                    url: `/processes/${processId}/objects`,
                    type: 'GET',
                    success: function (res) {
                        res.data.forEach((value) => {
                            $(`.socket-render tbody tr[data-id="${value.id}"] td:last-child`).html(`
                                ${buildProgressBar(value.appearances, totalFrames, fps, renderHour, false)}
                                <div class="status-overlay position-absolute"></div>
                            `);

                            value.appearances.forEach((appearance) => {
                                if (appearance.object_id !== appearance.old_object_id) {
                                    $(`.socket-render tbody tr[data-id="${appearance.old_object_id}"]`).fadeOut(3000);
                                }
                            });
                        });
                    },
                })
            }
        });

        $(document).ready(function () {
            renderData();
        });
    </script>
@endpush
