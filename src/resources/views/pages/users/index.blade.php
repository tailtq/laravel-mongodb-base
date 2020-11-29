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
                        <th>Tùy chọn</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $index = $users->perPage() * ($users->currentPage() - 1);
                    @endphp

                    @foreach ($users as $user)
                        <tr>
                            <th class="text-center">{{ ++$index }}</th>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at->format('h:i Y-m-d') }}</td>
                            <td>
                                <form action="{{ route('users.delete', $user->id) }}" method="POST">
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

    {{ $users->links('vendor.pagination.bootstrap-4') }}
@endsection
