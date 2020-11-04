@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
    </nav>

    <div class="row">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Danh sách theo dõi</div>
                <a href="{{ route('identities.create') }}" class="btn btn-primary">Tạo mới</a>
            </h6>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Created at</th>
                        <th>Options</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $index = $identities->perPage() * ($identities->currentPage() - 1);
                    @endphp

                    @foreach ($identities as $identity)
                        <tr>
                            <th>{{ ++$index }}</th>
                            <td><img src="{{ json_decode($identity->images)[0] }}" alt=""></td>
                            <td>{{ $identity->name }}</td>
                            <td>{{ $identity->created_at }}</td>
                            <td>
                                <form action="{{ route('identities.delete', $identity->id) }}" method="POST">
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

    {{ $identities->links('vendor.pagination.bootstrap-4') }}
@endsection
