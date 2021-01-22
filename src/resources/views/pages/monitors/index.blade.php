@extends('layout.master')

@push('plugin-styles')
    <style>
        .monitor-sidebar {
            position: fixed;
            height: 100%;
            top: 59px;
            overflow-x: hidden;
            transition: 0.5s;
            z-index: 50;
            padding: 10px 5px !important;
            right: -250px;
            width: 250px;
        }

        .monitor-sidebar.show {
            right: 0;
        }

        .monitor-block {
            padding: 0 5px;
            max-width: 33%;
            position: relative;
        }

        @media (max-width: 991px) {
            .monitor-block {
                max-width: 50%;
            }
        }

        .sidebar__close-btn {
            font-size: 25px;
            font-weight: bold;

        }

        .sidebar__close-btn:hover {
            color: rgba(0, 0, 0, 0.7);
        }

        .monitor-sidebar img {
            width: 64px;
            height: 64px;
        }

        .monitor-icon__group {
            position: absolute;
            right: 5px;
            padding: 0 7px;
            top: 0;
            z-index: 5;
        }

        .monitor-icon {
            font-size: 20px;
            animation: blink 1.5s infinite;
            color: #FF0000;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <h4 class="mb-3 mb-md-0">Màn hình giám sát</h4>

{{--        <button class="btn btn-primary sidebar__btn-open">Danh sách đối tượng</button>--}}
    </div>

    <div class="nav hidden monitor-sidebar bg-white d-block">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5>Danh sách đối tượng</h5>

            <a href="#" class="sidebar__close-btn mr-2 text-black">x</a>
        </div>

        <ul class="nav flex-column list-unstyled monitor__objects">
            {{--            @for ($i = 0; $i < 10; $i++)--}}
            {{--                <li class="media mb-2">--}}
            {{--                    <img class="mr-2"--}}
            {{--                         src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2264%22%20height%3D%2264%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2064%2064%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_176ec726fa7%20text%20%7B%20fill%3Argba(255%2C255%2C255%2C.75)%3Bfont-weight%3Anormal%3Bfont-family%3AHelvetica%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_176ec726fa7%22%3E%3Crect%20width%3D%2264%22%20height%3D%2264%22%20fill%3D%22%23777%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2213.8359375%22%20y%3D%2236.65%22%3E64x64%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E"--}}
            {{--                         alt="Generic placeholder image">--}}
            {{--                    <div class="media-body">--}}
            {{--                        <p class="mt-0 mb-1"><b>Nguyen Le Van A</b></p>--}}
            {{--                        11:00 - 55:00--}}
            {{--                    </div>--}}
            {{--                </li>--}}
            {{--            @endfor--}}
        </ul>
    </div>

    <div class="d-flex monitor-manager">
        @if (count($processes) == 0)
            <p>Không có tiến trình nào đang thực hiện</p>
        @endif

        @foreach ($processes as $process)
            <div class="monitor-block mb-3"
                 data-id="{{ $process->id }}"
                 data-total-appearances="{{ $process->total_appearances }}"
                 data-total-objects="{{ $process->total_objects }}"
                 data-total-identified="{{ $process->total_identified }}"
                 data-total-unidentified="{{ $process->total_unidentified }}">
                @if ($process->camera_id)
                    <div class="monitor-icon__group" title="Live">
                        <i class="monitor-icon mdi mdi-access-point"></i>
                    </div>
                @else
                    <div class="monitor-icon__group" title="Video">
                        <i class="monitor-icon mdi mdi-video"></i>
                    </div>
                @endif

                <video class="videoElement w-100" controls
                       resource="{{ env('STREAMING_SERVER') }}/{{ $process->mongo_id }}.flv">
                </video>

                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td colspan="8">
                            <a href="{{ route('processes.detail', $process->id) }}" target="_blank">
                                <b>{{ $process->name }}</b>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td title="Số lượt người xuất hiện">
                            <b class="display-5">
                                <i class="mdi mdi-walk"></i>
                            </b>
                        </td>
                        <td class="statistic__total-appearances">{{ $process->total_appearances }}</td>

                        <td title="Số người">
                            <b class="display-5">
                                <i class="mdi mdi-account-group"></i>
                            </b>
                        </td>
                        <td class="statistic__total-objects">{{ $process->total_objects }}</td>

                        <td title="Số người phát hiện được danh tính">
                            <b class="display-5">
                                <i class="mdi mdi-card-account-details"></i>
                            </b>
                        </td>
                        <td class="statistic__total-identified">{{ $process->total_identified }}</td>

                        <td title="Số người không phát hiện được danh tính">
                            <b class="display-5">
                                <i class="mdi mdi-account-question"></i>
                            </b>
                        </td>
                        <td class="statistic__total-unidentified">{{ $process->total_unidentified }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ my_asset('assets/plugins/flv/flv.min.js') }}"></script>
    <script>
        const processes = {};

        function loadVideos() {
            const videoElements = $('.videoElement');

            for (const videoElement of videoElements) {
                const $monitorBlock = $(videoElement).parent().closest('.monitor-block');
                const processId = $monitorBlock.data('id');
                processes[processId] = {
                    totalAppearances: $monitorBlock.data('total-appearances'),
                    totalObjects: $monitorBlock.data('total-objects'),
                    totalIdentified: $monitorBlock.data('total-identified'),
                    totalUnidentified: $monitorBlock.data('total-unidentified'),
                };

                const flvPlayer = flvjs.createPlayer({
                    type: 'flv',
                    isLive: true,
                    url: videoElement.getAttribute('resource')
                });
                flvPlayer.attachMediaElement(videoElement);
                flvPlayer.load();
                flvPlayer.play();
            }
        }

        function listenAnalysisEvent() {
            Echo.channel('monitor.analysis').listen('.App\\Events\\AnalysisProceeded', function (res) {
                console.log(res);
                res.data.forEach((process) => {
                    const {
                        id: processId,
                        total_appearances: totalAppearances,
                        total_objects: totalObjects,
                        total_identified: totalIdentified,
                        total_unidentified: totalUnidentified,
                    } = process;

                    if (!processes[processId]) {
                        loadNewProcesses(Object.keys(processes).map(e => parseInt(e)));
                    }
                    const existingProcess = processes[processId];
                    existingProcess.totalAppearances = totalAppearances;
                    existingProcess.totalObjects = totalObjects;
                    existingProcess.totalIdentified = totalIdentified;
                    existingProcess.totalUnidentified = totalUnidentified;

                    const $block = $(`.monitor-block[data-id="${processId}"]`);
                    $block.find('td.statistic__total-appearances').text(existingProcess.totalAppearances);
                    $block.find('td.statistic__total-objects').text(existingProcess.totalObjects);
                    $block.find('td.statistic__total-identified').text(existingProcess.totalIdentified);
                    $block.find('td.statistic__total-unidentified').text(existingProcess.totalUnidentified);
                });
            });
        }

        function loadNewProcesses(ignoredIds) {
            $.ajax({
                url: '/monitors/new-processes',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json; charset=UTF-8',
                data: JSON.stringify({
                    _token: $('meta[name="_token"]').attr('content'),
                    ignored_ids: ignoredIds,
                }),
                success: function (res) {
                    res.data.forEach((process) => {
                        $('.monitor-manager').append(`
                            <div class="monitor-block mb-3"
                                 data-id="${process.id}"
                                 data-total-appearances="${process.total_appearances}"
                                 data-total-objects="${process.total_objects}"
                                 data-total-identified="${process.total_identified}"
                                 data-total-unidentified="${process.total_unidentified}">
                                ${process.camera_id ? `
                                    <div class="monitor-icon__group" title="Live">
                                        <i class="monitor-icon mdi mdi-access-point"></i>
                                    </div>
                                ` : `
                                    <div class="monitor-icon__group" title="Video">
                                        <i class="monitor-icon mdi mdi-video"></i>
                                    </div>
                                `}

                                <video class="videoElement w-100" controls
                                       resource="{{ env('STREAMING_SERVER') }}/${process.mongo_id}.flv">
                                </video>

                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td colspan="2">
                                                <a href="" target="_blank">
                                                    <b>${process.name}</b>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Số lượt người xuất hiện</b></td>
                                            <td class="statistic__total-appearances">${process.total_appearances}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Số người</b></td>
                                            <td class="statistic__total-objects">${process.total_objects}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Số người phát hiện được danh tính</b></td>
                                            <td class="statistic__total-identified">${process.total_identified}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Số người không phát hiện được danh tính</b></td>
                                            <td class="statistic__total-unidentified">${process.total_unidentified}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `);

                        const videoElement = $(`.monitor-block[data-id="${process.id}"] video`)[0];
                        const flvPlayer = flvjs.createPlayer({
                            type: 'flv',
                            isLive: true,
                            url: videoElement.getAttribute('resource')
                        });
                        flvPlayer.attachMediaElement(videoElement);
                        flvPlayer.load();
                        flvPlayer.play();
                    });
                },
            })
        }

        $(document).ready(function () {
            loadVideos();
            listenAnalysisEvent();

            $('.sidebar__btn-open, .sidebar__close-btn').on('click', function (e) {
                e.preventDefault();

                const $objectSidebar = $('.monitor-sidebar');

                if ($objectSidebar.hasClass('show')) {
                    $objectSidebar.removeClass('show');
                } else {
                    $objectSidebar.addClass('show');
                }
            });
        });
    </script>
@endpush
