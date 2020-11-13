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

    <div class="row">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Luồng xử lý chi tiết</div>

                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal">
                    Chỉnh sửa cấu hình
                </button>
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

                <div class="mb-4">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="media d-block mb-2 d-sm-flex">
                            <img src="https://www.nobleui.com/laravel/template/light/assets/images/placeholder.jpg" class="wd-100p wd-sm-200 mb-3 mb-sm-0 mr-3" alt="...">
                            <div class="media-body">
                                <p class="mt-1 mb-2"><b>{{ $i + 1 }}. Nicolas Tesla</b></p>

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
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Thêm luồng xử lý</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <form action="" id="process-form">
                            <h2>Thông tin</h2>
                            <section>
                                <div class="form-group">
                                    <label>Tên video *</label>
                                    <input type="text"
                                           class="form-control"
                                           placeholder="Nhập tên"
                                           name="name"
                                           disabled
                                           value="{{ old('name', $process->name) }}">
                                </div>

                                <div class="form-group">
                                    <label>Đường dẫn *</label>
                                    <input type="text"
                                           class="form-control"
                                           placeholder="Nhập video"
                                           name="video_url"
                                           disabled
                                           value="{{ old('video_url', $process->video_url) }}">
                                </div>

                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea name="description"
                                              cols="30"
                                              rows="10"
                                              disabled
                                              class="form-control mb-0">{{ old('description', $process->description) }}</textarea>
                                </div>
                            </section>

                            <h2>Cấu hình</h2>
                            <section>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Detection scale</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="detection_scale"
                                                   disabled
                                                   value="{{ old('detection_scale', object_get($process->mongoData, 'detection_scale')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Frame drop</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="frame_drop"
                                                   disabled
                                                   value="{{ old('frame_drop', object_get($process->mongoData, 'frame_drop')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Frame step</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="frame_step"
                                                   disabled
                                                   value="{{ old('frame_step', object_get($process->mongoData, 'frame_step')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Max pitch</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="max_pitch"
                                                   disabled
                                                   value="{{ old('max_pitch', object_get($process->mongoData, 'max_pitch')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Max roll</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="max_roll"
                                                   disabled
                                                   value="{{ old('max_roll', object_get($process->mongoData, 'max_roll')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Max yaw</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="max_yaw"
                                                   disabled
                                                   value="{{ old('max_yaw', object_get($process->mongoData, 'max_yaw')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Kích thước khuôn mặt tối thiểu</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="min_face_size"
                                                   disabled
                                                   value="{{ old('min_face_size', object_get($process->mongoData, 'min_face_size')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tỉ lệ theo dõi</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="tracking_scale"
                                                   disabled
                                                   value="{{ old('tracking_scale', object_get($process->mongoData, 'tracking_scale')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Ngưỡng so sánh sinh trắc</label>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Nhập thông số"
                                                   name="biometric_threshold"
                                                   disabled
                                                   value="{{ old('biometric_threshold', object_get($process->mongoData, 'biometric_threshold')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tỉ lệ chính xác đầu tối thiểu</label>
                                            <input type="number"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="min_head_confidence"
                                                   disabled
                                                   value="{{ old('min_head_confidence', object_get($process->mongoData, 'min_head_confidence')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tỉ lệ chính xác khuôn mặt tối thiểu</label>
                                            <input type="number"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="min_face_confidence"
                                                   disabled
                                                   value="{{ old('min_face_confidence', object_get($process->mongoData, 'min_face_confidence')) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tỉ lệ chính xác thân hình tối thiểu</label>
                                            <input type="number"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="min_body_confidence"
                                                   disabled
                                                   value="{{ old('min_body_confidence', object_get($process->mongoData, 'min_body_confidence')) }}">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/jquery-steps/jquery.steps.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dashjs/3.1.3/dash.all.min.js" integrity="sha512-KbtNOWr7e/rlM9utrUc5cO9PeJZO3jFfCjWPe1mHe2sPvIike3IZIH6h4ja6wH7aXNKrecP8zh6/SYDc3t6Jog==" crossorigin="anonymous"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>
      function init() {
        var video,
          player,
          url = 'http://192.168.1.252:9994/production/streaming/{{ $process->mongo_id }}/dash_out.mpd';

        video = document.querySelector("video");
        player = dashjs.MediaPlayer().create();
        player.initialize(video, url, false);
      }
    </script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        init();
      });
    </script>
@endpush
