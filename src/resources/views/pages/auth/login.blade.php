@extends('layout.auth-master')

@section('content')
    <div class="page-content d-flex align-items-center justify-content-center">

        <div class="row w-100 mx-0 auth-page">
            <div class="col-md-8 col-xl-6 mx-auto">
                <div class="card">
                    <div class="row">
                        <div class="col-md-4 pr-md-0">
                            <div class="auth-left-wrapper"
                                 style="background-image: url({{ url('/img/img6.jpg') }})">
                            </div>
                        </div>
                        <div class="col-md-8 pl-md-0">
                            <div class="auth-form-wrapper px-4 py-5">
                                <a href="/" class="noble-ui-logo d-block mb-2">Face<span>AI</span></a>
                                <form method="POST" class="forms-sample" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Địa chỉ email</label>
                                        <input type="email" class="form-control" name="email"
                                               placeholder="Email"
                                               value="{{ old('email') }}">

                                        @error('email')
                                            <label class="error mt-2 text-danger">
                                                {{ $message }}
                                            </label>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Mật khẩu</label>
                                        <input type="password" class="form-control" name="password"
                                               autocomplete="current-password" placeholder="Mật khẩu"
                                               value="{{ old('password') }}">

                                        @error('password')
                                            <label class="error mt-2 text-danger">
                                                {{ $message }}
                                            </label>
                                        @enderror
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-primary mr-2 mb-2 mb-md-0">Đăng nhập</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
