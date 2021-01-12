@extends('layout.master')

@push('plugin-styles')
    <script src="{{ my_asset('assets/plugins/flv/flv.min.js') }}"></script>
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

        <button class="btn btn-primary sidebar__btn-open">Danh sách đối tượng</button>
    </div>

    <div class="nav hidden monitor-sidebar bg-white d-block">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5>Danh sách đối tượng</h5>

            <a href="#" class="sidebar__close-btn mr-2 text-black">x</a>
        </div>

        <ul class="nav flex-column list-unstyled">

            @for ($i = 0; $i < 10; $i++)
                <li class="media mb-2">
                    <img class="mr-2" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2264%22%20height%3D%2264%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2064%2064%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_176ec726fa7%20text%20%7B%20fill%3Argba(255%2C255%2C255%2C.75)%3Bfont-weight%3Anormal%3Bfont-family%3AHelvetica%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_176ec726fa7%22%3E%3Crect%20width%3D%2264%22%20height%3D%2264%22%20fill%3D%22%23777%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2213.8359375%22%20y%3D%2236.65%22%3E64x64%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" alt="Generic placeholder image">
                    <div class="media-body">
                        <p class="mt-0 mb-1"><b>Nguyen Le Van A</b></p>
                        11:00 - 55:00
                    </div>
                </li>
            @endfor


            <li class="media mb-2">
                <img class="mr-2" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2264%22%20height%3D%2264%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2064%2064%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_176ec726fa7%20text%20%7B%20fill%3Argba(255%2C255%2C255%2C.75)%3Bfont-weight%3Anormal%3Bfont-family%3AHelvetica%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_176ec726fa7%22%3E%3Crect%20width%3D%2264%22%20height%3D%2264%22%20fill%3D%22%23777%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2213.8359375%22%20y%3D%2236.65%22%3E64x64%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" alt="Generic placeholder image">
                <div class="media-body">
                    <p class="mt-0 mb-1"><b>Nguyen Le Van A</b></p>
                    11:00 - 55:00
                </div>
            </li>

            <li class="media mb-2">
                <img class="mr-2" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2264%22%20height%3D%2264%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2064%2064%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_176ec726fa7%20text%20%7B%20fill%3Argba(255%2C255%2C255%2C.75)%3Bfont-weight%3Anormal%3Bfont-family%3AHelvetica%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_176ec726fa7%22%3E%3Crect%20width%3D%2264%22%20height%3D%2264%22%20fill%3D%22%23777%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2213.8359375%22%20y%3D%2236.65%22%3E64x64%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" alt="Generic placeholder image">
                <div class="media-body">
                    <p class="mt-0 mb-1"><b>Nguyen Le Van A</b></p>
                    11:00 - 55:00
                </div>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12">
            <div class="row d-flex">
                @for ($i = 0; $i < 3; $i++)
                    <div class="monitor-block">
                        <div class="monitor-icon__group" title="Live">
{{--                            <i class="monitor-icon mdi mdi-video"></i>--}}
                            <i class="monitor-icon mdi mdi-access-point"></i>
                        </div>
                        <video class="videoElement w-100" controls></video>

                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td colspan="2">

                                        <a href="{{ route('processes.detail', 1) }}" target="_blank">
                                            <b>Camera 1 - Nhận diện khuôn mặt</b>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Số lượt người xuất hiện</b></td>
                                    <td>200</td>
                                </tr>
                                <tr>
                                    <td><b>Số người</b></td>
                                    <td>100</td>
                                </tr>
                                <tr>
                                    <td><b>Số người phát hiện được danh tính</b></td>
                                    <td>50</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endfor
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ my_asset('assets/plugins/flv/flv.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/video-js/video.min.js') }}"></script>
    <script>
        $(document).ready(function () {
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

        if (flvjs.isSupported()) {
            var a;
            const videoElements = document.getElementsByClassName('videoElement');

            for (const videoElement of videoElements) {
                const flvPlayer = flvjs.createPlayer({
                    type: 'flv',
                    isLive: true,
                    url: '/converted.flv'
                });
                flvPlayer.attachMediaElement(videoElement);
                flvPlayer.load();
                // let isPlaying = false;

                // $(videoElement).on('play', function (e) {
                //     e.preventDefault();
                    // if (!isPlaying) {
                    //     flvPlayer.unload();
                    //     flvPlayer.load();
                    //     console.log('log');
                    // }
                // });

                // $(videoElement).on('pause', function (e) {
                //     isPlaying = false;
                //     console.log('test');
                // });
            }
        }
    </script>
@endpush
