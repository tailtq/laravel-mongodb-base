<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <form action="" id="process-form" class="editable">
                        <h2>Thông tin</h2>
                        <section>
                            <div class="form-group">
                                <label>Tên luồng xử lý *</label>
                                <input type="text" class="form-control required" placeholder="Nhập tên" name="name" required>
                            </div>

                            <div class="form-group">
                                <label>Loại tiến trình *</label>
                                <select name="process_type" class="form-control" required>
                                    <option value="camera">Camera</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Camera *</label>
                                <select name="camera" class="form-control" data-type="number" required>
                                    <option value="0">Chọn camera</option>

                                    @foreach($cameras as $camera)
                                        <option value="{{ $camera->id }}"
                                                data-url="{{ $camera->url }}">
                                            {{ $camera->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group d-none">
                                <label>Đường dẫn *</label>

                                <div class="input-group mb-3">
                                    <input type="text" class="form-control dropzone-field required"
                                           placeholder="Nhập video" name="url">

                                    <div class="input-group-append">
                                        <a class="btn btn-primary"
                                           data-toggle="collapse"
                                           href="#collapseDropzone"
                                           role="button"
                                           aria-expanded="false"
                                           aria-controls="collapseDropzone">
                                            <i class="link-icon" data-feather="upload" style="width: 16px; height: 16px"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group d-none">
                                <label>Ngày bắt đầu</label>
                                <input class="form-control mb-4 mb-md-0"
                                       name="started_at"
                                       data-inputmask="'alias': 'datetime'"
                                       data-inputmask-inputformat="HH:MM dd-mm-yyyy" />
                            </div>

                            <div class="collapse form-group" id="collapseDropzone">
                                <div class="card card-body">
                                    <div class="dropzone"></div>
                                </div>

                                <div class="text-center mt-3">
                                    <button class="btn btn-primary dropzone-submit">Tải lên</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Mô tả</label>
                                <textarea name="description"
                                          cols="30"
                                          rows="10"
                                          class="form-control mb-0">{{ old('description') }}</textarea>
                            </div>

                            <div class="thumbnail-error"></div>
                        </section>

                        <h2>Cấu hình</h2>
                        <section>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Detection scale</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="detection_scale"
                                               value="{{ old('detection_scale', 0.35) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Frame drop</label>
                                        <input type="number"
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
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="frame_step"
                                               value="{{ old('frame_step', 1) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Max pitch</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="max_pitch"
                                               value="{{ old('max_pitch', 90) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Max roll</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="max_roll"
                                               value="{{ old('max_roll', 90) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Max yaw</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="max_yaw"
                                               value="{{ old('max_yaw', 90) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kích thước khuôn mặt tối thiểu</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="min_face_size"
                                               value="{{ old('min_face_size', 25) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tỉ lệ theo dõi</label>
                                        <input type="number"
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
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="biometric_threshold"
                                               value="{{ old('biometric_threshold', 60) }}">
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
                                               value="{{ old('min_head_confidence', 50) }}">
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
                                               value="{{ old('min_face_confidence', 50) }}">
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
                                               value="{{ old('min_body_confidence', 50) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Write data step</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="write_data_step"
                                               value="{{ old('write_data_step', 300) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Write video step</label>
                                        <input type="number"
                                               class="form-control"
                                               required
                                               placeholder="Nhập thông số"
                                               name="write_video_step"
                                               value="{{ old('write_video_step', 3000) }}">
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h2>Vùng nhận diện</h2>
                        <section>
                            <div class="d-flex h-100 justify-content-center align-items-center">
                                <div class="position-relative h-100 mr-3">
                                    <img style="width: 451px;"
                                         id="canvas-img"
                                         class="h-100">

                                    <canvas id="canvas" class="position-absolute" style="top: 0; left: 0"></canvas>
                                </div>

                                <div class="canvas-toolbox d-flex flex-column">
                                    <button class="btn btn-info mb-3"
                                            type="button"
                                            id="canvas__detecting">
                                        Vẽ vùng nhận diện
                                    </button>

                                    <button class="btn btn-info mb-3 text-white"
                                            type="button"
                                            style="background-color: rgb(151, 35, 44); border-color: rgb(151, 35, 44)"
                                            id="canvas__tracking">
                                        Vẽ vùng theo dõi
                                    </button>

                                    <button class="btn btn-success mb-3"
                                            type="button"
                                            id="canvas__finish">
                                        Hoàn thành
                                    </button>

                                    <button class="btn btn-danger"
                                            type="button"
                                            id="canvas__delete">
                                        Xoá
                                    </button>
                                </div>
                            </div>

                            <p class="error text-danger mt-2 text-center canvas__error-message"></p>

                            <input type="hidden" name="thumbnail">
                            <input type="hidden" name="regions">
                        </section>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
