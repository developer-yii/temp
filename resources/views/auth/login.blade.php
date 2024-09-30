@extends('layouts.app')
@section('content')
<div class="panel-body">
    <form action="{{ route('login') }}" method="post" id="login-form" autocomplete="off">
        @csrf
        <div class="well">
            <b>Login</b>
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @error('approve')
            <div class="alert alert-danger">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
        <div class="row">
            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="email" class="col-md-2 col-form-label text-md-end">Email</label>
                <div class="col-md-8">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" @if($errors->has('email')) autofocus @endif>
                    @error('email')
                    <span class="error" id="email-error" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="password" class="col-md-2 col-form-label text-md-end">Password</label>
                <div class="col-md-8">

                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" @if($errors->has('password')) autofocus @endif>

                    @error('password')
                    <span class="error" id="password-error" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="col-md-8 mb-1 col-md-offset-2 text-center">
                <input type="submit" class="btn btn-default" name="login" id="login"  value="Login">
            </div>
            <div class="col-md-8 mb-1 col-md-offset-2 text-center">
                <label>
                    @if (Route::has('password.request'))
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </label>
            </div>
        </div>
    </form>
</div>
@endsection


