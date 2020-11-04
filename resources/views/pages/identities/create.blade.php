@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/identities">Identities</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create identity</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Create new identity</h6>
                    <form action="{{ route('identities.store') }}" method="POST" enctype="multipart/form-data" >
                        @csrf

                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" placeholder="Enter Name" name="name">

                                    @error('name')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Info</label>
                                    <textarea name="info" id="" class="form-control" rows="20"></textarea>

                                    @error('info')
                                    <label class="error mt-2 text-danger">
                                        {{ $message }}
                                    </label>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>File upload</label>

                                    <div class="stretch-card grid-margin grid-margin-md-0">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">Dropzone</h6>
                                                <div class="dropzone" id="dropzone"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="fallback">
                                    <input type="file" name="files[]" multiple>
                                </div>
                                <div class="form-check form-check-flat form-check-primary">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="status">
                                        Theo dõi
                                        <i class="input-frame"></i></label>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">Submit form</button>
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
    <script>
        Dropzone.autoDiscover = false;

        $(function() {
            'use strict';

            {{--$('.dropzone').dropzone({--}}
                {{--url: '/identities/create',--}}
                {{--paramName: 'files',--}}
                {{--maxFiles: 999999999999,--}}
                {{--headers: {--}}
                    {{--'X-CSRF-TOKEN': "{{ csrf_token() }}"--}}
                {{--},--}}
                {{--uploadMultiple: true,--}}
                {{--init: function () {--}}
                    {{--console.log('hello world');--}}
                {{--},--}}
            {{--});--}}
        });
    </script>
{{--    <script src="{{ asset('assets/js/dropzone.js') }}"></script>--}}
@endpush
