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
