@extends('layout.master')

@push('plugin-styles')
    <link href="{{ my_asset('assets/plugins/jquery-steps/jquery.steps.css') }}" rel="stylesheet" />
    <link href="{{ my_asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <link href="{{ my_asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet" />
    <link href="{{ my_asset('css/custom.css') }}" rel="stylesheet"/>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Luồng xử lý</li>
        </ol>
    </nav>

    <div class="row">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Danh sách luồng xử lý</div>

                <div>
                    <button type="button"
                            data-toggle="modal"
                            data-target="#searchFaceModal"
                            class="btn btn-primary btn-search text-white search-face__btn">
                        <i class="link-icon icon__normal-size" data-feather="search" style="width: 13px; height: 13px;"></i>
                        Tìm kiếm
                    </button>

                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal">
                        Tạo mới
                    </button>
                </div>
            </h6>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th class="text-center">Hình đại diện</th>
                        <th>Tên</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Tùy chọn</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $index = $items->perPage() * ($items->currentPage() - 1);
                    @endphp

                    @foreach ($items as $item)
                        <tr>
                            <td class="text-center">{{ ++$index }}</td>
                            <td class="text-center">
                                <img src="{{ $item->thumbnail }}" />
                            </td>
                            <td><a href="{{ route('processes.detail', $item->id) }}">{{ $item->name }}</a></td>
                            <td>
                                <span class="badge
                                    @if($item->status == 'error' || $item->status == 'stopped')
                                        badge-danger
                                    @else
                                        badge-success
                                    @endif
                                        text-uppercase">
                                    {{ __('status.' . $item->status, [], 'vi') }}
                                </span>
                            </td>
                            <td>{{ $item->created_at->format('H:i d-m-Y') }}</td>
                            <td>
                                <a class="btn btn-primary btn-icon" role="button"
                                   href="{{ route('processes.detail', $item->id) }}" style="line-height: 2">
                                    <i data-feather="eye"></i>
                                </a>

                                <form onsubmit="return confirm('Bạn có chắc chắn không?');"
                                      action="{{ route('processes.delete', $item->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-icon">
                                        <i data-feather="trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $items->links('vendor.pagination.bootstrap-4') }}

    @include('pages.processes.search_form')
    @include('pages.processes.unsaved_process_parameters')
@endsection

@push('plugin-scripts')
    <script src="{{ my_asset('assets/plugins/jquery-steps/jquery.steps.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ my_asset('assets/plugins/inputmask/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="{{ my_asset('assets/js/inputmask.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ my_asset('js/custom.js') }}"></script>
    <script src="{{ my_asset('js/shapes/circle.js') }}"></script>
    <script src="{{ my_asset('js/geometry.js') }}"></script>
    <script src="{{ my_asset('js/util.js') }}"></script>
    <script src="{{ my_asset('js/search_form.js') }}"></script>
    <script>
        const isRealtime = false;
        const fps = 20;
        const renderHour = false;

        $(document).ready(function () {
            $('[name="started_at"]').inputmask();

            $('select[name="process_type"]').on('change', function () {
                const type = $(this).find('option:selected').val();
                const $cameraElement = $('select[name="camera_id"]');
                const $videoUrl = $('input[name="video_url"]');
                const $startedAt = $('input[name="started_at"]');

                if (type === 'camera') {
                    $cameraElement.attr('required', true).parent().closest('.form-group').removeClass('d-none');
                    $videoUrl.attr('required', false).parent().parent().closest('.form-group').addClass('d-none');
                    $startedAt.parent().closest('.form-group').addClass('d-none');
                } else {
                    $cameraElement.attr('required', false).parent().closest('.form-group').addClass('d-none');
                    $videoUrl.attr('required', true).parent().parent().closest('.form-group').removeClass('d-none');
                    $startedAt.parent().closest('.form-group').removeClass('d-none');
                }
            });
        });
    </script>
@endpush
