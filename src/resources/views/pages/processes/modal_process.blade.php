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
                <label>Tên luồng xử lý *</label>
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