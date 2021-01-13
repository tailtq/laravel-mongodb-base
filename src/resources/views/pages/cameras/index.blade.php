@extends('layout.master')

@section('content')
    <div class="row">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Danh sách camera</div>
                <button data-href="{{ route('cameras.create') }}"
                        type="button"
                        data-toggle="modal"
                        data-target="#modal"
                        class="btn btn-primary btn-create">Tạo mới</button>
            </h6>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th>Tên camera</th>
                        <th>Ngày tạo</th>
                        <th>Tùy chọn</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $index = $items->perPage() * ($items->currentPage() - 1);
                    @endphp

                    @if ($items->count() == 0)
                        <tr>
                            <td colspan="4" class="text-center">Không có camera nào được tìm thấy</td>
                        </tr>
                    @endif

                    @foreach ($items as $item)
                        <tr data-value="{{ json_encode($item) }}">
                            <td class="text-center">{{ ++$index }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->created_at->format('H:i d-m-Y') }}</td>
                            <td>
                                <button data-href="{{ route('cameras.edit', $item->id) }}"
                                        type="button"
                                        data-toggle="modal"
                                        data-target="#modal"
                                        class="btn btn-warning btn-icon btn-edit">
                                    <i data-feather="edit"></i>
                                </button>

                                <form onsubmit="return confirm('Bạn có chắc chắn không?');"
                                      action="{{ route('cameras.delete', $item->id) }}"
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

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5 class="mb-3">Tạo camera</h5>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Tên camera</label>
                            <input type="text" class="form-control" placeholder="Nhập tên camera" name="name"
                                   required value="">
                        </div>

                        <div class="form-group">
                            <label>Đường dẫn</label>
                            <input type="text" class="form-control" placeholder="Nhập đường dẫn" name="url"
                                   required value="">
                        </div>

                        <div class="text-right">
                            <button class="btn btn-success">Lưu trữ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{ $items->links('vendor.pagination.bootstrap-4') }}
@endsection

@push('plugin-scripts')
    <script src="{{ my_asset('js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.15.3/dist/sweetalert2.all.min.js"></script>
@endpush

@push('custom-scripts')
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
        });
        let type;

        $(document).ready(function () {
            $('.btn-edit').on('click', function (e) {
                e.preventDefault();

                type = 'edit';
                const href = $(this).data('href');
                const value = $(this).parent().closest('tr').data('value');
                $('#modal form').attr('action', href);

                Object.keys(value).forEach((key) => {
                    console.log(`#modal form input[name="${key}"]`, value[key]);
                    $(`#modal form input[name="${key}"]`).val(value[key]);
                });
                // TODO: Pass old value to form
            });

            $('.btn-create').on('click', function (e) {
                e.preventDefault();

                type = 'create';
                const href = $(this).data('href');
                $('#modal form').attr('action', href);
            });

            $('#modal form').on('submit', function (e) {
                e.preventDefault();
                const serializableData = $(this).serializeArray();
                const action = $(this).attr('action');
                const data = serializeObject(serializableData);

                $.ajax({
                    url: action,
                    type: type === 'create' ? 'POST' : 'PUT',
                    dataType: 'json',
                    contentType: 'application/json; charset=UTF-8',
                    data: JSON.stringify({
                        _token: $('meta[name="_token"]').attr('content'),
                        ...data,
                    }),
                    success: function () {
                        window.location.reload();
                    },
                    error: function (res) {
                        Toast.fire({
                            type: 'error',
                            title: res.responseJSON.message,
                        });
                    },
                });
            });
        });
    </script>
@endpush
