@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/identities">Đối tượng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tạo đối tượng</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Create new identity</h6>
                    <form action="{{ route('identities.store') }}" method="POST" enctype="multipart/form-data" id="form_objects">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên:</label>
                                    <input type="text" class="form-control" placeholder="Nhập tên" name="name" >

                                    @error('name')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Số CMND</label>
                                    <input type="text" class="form-control" placeholder="Nhập số CMND" name="card_number" maxlength="9">

                                    @error('card_number')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Thông tin:</label>
                                    <textarea name="info" id="" class="form-control" rows="10"></textarea>

                                    @error('info')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Upload:</label>

                                    <div class="stretch-card grid-margin grid-margin-md-0">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="dropzone" id="dropzone"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="fallback">
                                    <input type="file" name="files[]" multiple>
                                </div>
                                @error('files')
                                <label class="error mt-2 text-danger">
                                    {{ $message }}
                                </label>
                                @enderror
                                <div class="form-check form-check-flat form-check-primary">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="status">
                                        Theo dõi
                                        <i class="input-frame"></i></label>
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
    {{--<script>--}}

        {{--Dropzone.autoDiscover = false;--}}

        {{--$(function() {--}}

            {{--// var myDropzone = new Dropzone(".dropzone");--}}
            {{--// myDropzone.on("addedfile", function(file) {--}}
            {{--//     /* Maybe display some more file information on your page */--}}
            {{--// });--}}

            {{--$('.dropzone').dropzone({--}}
                {{--autoProcessQueue: false,--}}
                {{--addRemoveLinks: true,--}}
                {{--dictRemoveFile: 'Xóa hình',--}}
                {{--url: '/identities/create',--}}
                {{--paramName: 'files',--}}
                {{--maxFiles: 999999999999,--}}
                {{--headers: {--}}
                    {{--'X-CSRF-TOKEN': "{{ csrf_token() }}"--}}
                {{--},--}}
                {{--uploadMultiple: true,--}}
                {{--init: function () {--}}
                    {{--// this.on("addedfile", function(file) {--}}
                    {{--//     var myDropzone = this;--}}
                    {{--//     console.log(myDropzone.getAcceptedFiles());--}}
                    {{--// });--}}
                    {{--//--}}
                    {{--// $("#form-submit").click(function (e) {--}}
                    {{--//     e.preventDefault();--}}
                    {{--//     // myDropzone.processQueue();--}}
                    {{--//--}}
                    {{--// });--}}

                    {{--let myDropzone = this;--}}
                    {{--$("#form_submit").click(function (e) {--}}
                        {{--e.preventDefault();--}}
                        {{--myDropzone.processQueue();--}}
                    {{--});--}}

                    {{--this.on('sending', function(file, xhr, formData) {--}}
                        {{--// console.log(myDropzone.getUploadingFiles());--}}
                        {{--// return true;--}}
                        {{--let data = $('#form_objects').serializeArray();--}}
                        {{--$.each(data, function(key, el) {--}}
                            {{--formData.append(el.name, el.value);--}}
                        {{--});--}}
                    {{--});--}}

                {{--},--}}
                {{--success: function (response) {--}}
                    {{--console.log(response);--}}
                {{--}--}}
            {{--});--}}
            {{----}}
        {{--});--}}
    {{--</script>--}}
{{--    <script src="{{ asset('assets/js/dropzone.js') }}"></script>--}}
@endpush
