@extends('layout.master2')

@section('content')
    <div class="page-content d-flex align-items-center justify-content-center">

        <div class="row w-100 mx-0 auth-page">
            <div class="col-md-8 col-xl-6 mx-auto">
                <div class="card">
                    <div class="row">
                        <div class="col-md-4 pr-md-0">
                            <div class="auth-left-wrapper"
                                 style="background-image: url({{ url('/public/img/img6.jpg') }})">
                            </div>
                        </div>
                        <div class="col-md-8 pl-md-0">
                            <div class="auth-form-wrapper px-4 py-5">
                                <a href="/" class="noble-ui-logo d-block mb-2">Noble<span>UI</span></a>
                                <h5 class="text-muted font-weight-normal mb-4">Welcome back! Log in to your
                                    account.</h5>
                                <form method="POST" class="forms-sample" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Email address</label>
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
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password"
                                               autocomplete="current-password" placeholder="Password"
                                               value="{{ old('password') }}">

                                        @error('password')
                                            <label class="error mt-2 text-danger">
                                                {{ $message }}
                                            </label>
                                        @enderror
                                    </div>

                                    <div class="form-check form-check-flat form-check-primary">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="remember">
                                            Remember me
                                        </label>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary mr-2 mb-2 mb-md-0">Login</button>
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
