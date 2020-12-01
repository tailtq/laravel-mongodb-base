@extends('layout.master')

@push('plugin-styles')
    <link href="{{ my_asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet"/>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('identities') }}">Định danh</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tạo định danh</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Tạo định danh</h6>
                    <form action="{{ route('identities.store') }}" method="POST" enctype="multipart/form-data"
                          id="form_objects">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên</label>
                                    <input type="text" class="form-control" placeholder="Nhập tên" name="name"
                                           required
                                           value="{{ old('name') }}">

                                    @error('name')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Số CMND</label>
                                    <input type="text" class="form-control" placeholder="Nhập số CMND"
                                           name="card_number" maxlength="9" required
                                           value="{{ old('card_number') }}">

                                    @error('card_number')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Thông tin</label>
                                    <textarea name="info" id="" class="form-control" rows="10">{{ old('info') }}</textarea>

                                    @error('info')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ảnh</label>

                                    <div class="stretch-card grid-margin grid-margin-md-0">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="dropzone" id="dropzone" data-type="image">
                                                    <div class="dz-message">Kéo ảnh vào đây để tải lên</div>
                                                </div>

                                                <div class="text-center mt-3">
                                                    <button class="btn btn-primary dropzone-submit">Tải lên</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="images-visualization row my-4"></div>

                                    <div class="image-links"></div>

                                    @error('images')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-check form-check-flat form-check-primary">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="status">
                                        Theo dõi
                                        <i class="input-frame"></i>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-success" type="submit" id="form_submit">Lưu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ my_asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ my_asset('assets/js/custom.js') }}"></script>
@endpush
