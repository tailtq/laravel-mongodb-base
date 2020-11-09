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
                    Xem cấu hình
                </button>
            </h6>

            <div class="table-responsive">
                <div>
                    <video controls="true" class="w-100">
                    </video>
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
                                    <input type="text" class="form-control required" placeholder="Nhập tên" name="name"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label>Đường dẫn *</label>
                                    <input type="text" class="form-control required" placeholder="Nhập video"
                                           name="video_url" required>
                                </div>

                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea name="description"
                                              cols="30"
                                              rows="10"
                                              class="form-control mb-0">{{ old('description') }}</textarea>
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
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="detection_scale"
                                                   value="{{ old('detection_scale', 0.25) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Frame drop</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="frame_drop"
                                                   value="{{ old('frame_drop', 2) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Frame step</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="frame_step"
                                                   value="{{ old('frame_step', 2) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Max pitch</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="max_pitch"
                                                   value="{{ old('max_pitch', 30) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Max roll</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="max_roll"
                                                   value="{{ old('max_roll', 30) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Max yaw</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="max_yaw"
                                                   value="{{ old('max_yaw', 30) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Kích thước khuôn mặt tối thiểu</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="min_face_size"
                                                   value="{{ old('min_face_size', 50) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tỉ lệ theo dõi</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="tracking_scale"
                                                   value="{{ old('tracking_scale', 0.5) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Ngưỡng so sánh sinh trắc</label>
                                            <input type="text"
                                                   class="form-control"
                                                   required
                                                   placeholder="Nhập thông số"
                                                   name="biometric_threshold"
                                                   value="{{ old('biometric_threshold', 50) }}">
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
    <script src="{{ asset('assets/js/wizard.js') }}"></script>
    <script>
      function init() {
        var video,
          player,
          url = "https://dash.akamaized.net/akamai/bbb_30fps/bbb_30fps.mpd";

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
