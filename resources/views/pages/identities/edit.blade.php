@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet"/>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('identities') }}">Định danh</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa định danh</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Chỉnh sửa định danh</h6>
                    <form action="{{ route('identities.update', $identity->id) }}" method="POST" enctype="multipart/form-data"
                          id="form_objects">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên</label>
                                    <input type="text" class="form-control" placeholder="Nhập tên" name="name"
                                           required
                                           value="{{ old('name', $identity->name) }}">

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
                                           value="{{ old('card_number', $identity->card_number) }}">

                                    @error('card_number')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Thông tin:</label>
                                    <textarea name="info" id="" class="form-control" rows="10">{{ old('info', $identity->info) }}</textarea>

                                    @error('info')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Upload</label>

                                    <div class="stretch-card grid-margin grid-margin-md-0">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="dropzone" id="dropzone" data-type="image"></div>

                                                <div class="text-center mt-3">
                                                    <button class="btn btn-primary dropzone-submit">Tải lên</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="images-visualization row my-2">
                                        @foreach ($identity->images as $index => $image)
                                            <div class="col-md-4">
                                                <img src="{{ $image['url'] }}" alt="" class="img-fluid">
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="image-links">
                                        @foreach ($identity->images as $index => $image)
                                            <div>
                                                <input type="hidden" name="images[{{ $index }}][mongo_id]" value="{{ $image['mongo_id'] }}">
                                                <input type="hidden" name="images[{{ $index }}][url]" value="{{ $image['url'] }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    @error('images')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check form-check-flat form-check-primary">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="status"
                                                    {{ old('info', $identity->status === 'tracking') ? 'checked' : '' }}
                                            >
                                            Theo dõi
                                            <i class="input-frame"></i>
                                        </label>
                                    </div>
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
    <script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/custom.js') }}"></script>
@endpush
