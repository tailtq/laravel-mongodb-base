@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/users">Users</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create user</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Create new user</h6>
                    <form action="{{ route('users.create') }}" method="POST">
                        @csrf

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
                            <label>Email</label>
                            <input type="email" class="form-control" placeholder="Enter Email" name="email">

                            @error('email')
                                <label class="error mt-2 text-danger">
                                    {{ $message }}
                                </label>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" placeholder="Enter Password" name="password">

                            @error('password')
                                <label class="error mt-2 text-danger">
                                    {{ $message }}
                                </label>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Password Confirmation</label>
                            <input type="password" class="form-control" placeholder="Confirm Password" name="password_confirmation">

                            @error('password_confirmation')
                            <label class="error mt-2 text-danger">
                                {{ $message }}
                            </label>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">Submit form</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
