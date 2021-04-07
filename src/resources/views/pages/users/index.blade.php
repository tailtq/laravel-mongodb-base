@extends('layout.master')

@section('content')
    <div class="row">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Quản lý người dùng</div>
                <a href="{{ route('users.create') }}" class="btn btn-primary">Tạo mới</a>
            </h6>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Ngày tạo</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $index = $items->perPage() * ($items->currentPage() - 1);
                    @endphp

                    @foreach ($items as $item)
                        <tr>
                            <th class="text-center">{{ ++$index }}</th>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->created_at->format('H:i d-m-Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $items->links('vendor.pagination.bootstrap-4') }}
@endsection
