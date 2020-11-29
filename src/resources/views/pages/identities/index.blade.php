@extends('layout.master')

@section('content')
    <div class="row">
        <div class="card-body">
            <h6 class="card-title d-flex justify-content-md-between align-items-center">
                <div>Danh sách định danh</div>
                <a href="{{ route('identities.create') }}" class="btn btn-primary">Tạo mới</a>
            </h6>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th>Hình đại diện</th>
                        <th>Tên</th>
                        <th>Ngày tạo</th>
                        <th>Tùy chọn</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $index = $identities->perPage() * ($identities->currentPage() - 1);
                    @endphp

                    @foreach ($identities as $identity)
                        <tr>
                            <th class="text-center">{{ ++$index }}</th>
                            <td><img src="{{ !empty($identity->images[0]['url'] ) ? $identity->images[0]['url'] : asset('img/icon-avatar-default.png')}}" alt=""></td>
                            <td>{{ $identity->name }}</td>
                            <td>{{ $identity->created_at->format('h:i Y-m-d') }}</td>
                            <td>
                                <a class="btn btn-warning btn-icon" href="{{ route('identities.edit', $identity->id) }}" style="line-height: 2">
                                    <i data-feather="edit"></i>
                                </a>

                                <form onsubmit="return confirm('Bạn có chắc chắn không?');"
                                      action="{{ route('identities.delete', $identity->id) }}"
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

    {{ $identities->links('vendor.pagination.bootstrap-4') }}
@endsection
