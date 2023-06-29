@extends('layouts.app')
@section('content')
<div class="panel-body">                                    
    <form action="{{ route('login') }}" method="post" id="login-form" autocomplete="off">
        @csrf
        <div class="well">
            <b>Login</b>                        
        </div>
        <div class="row">
            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="email" class="col-md-2 col-form-label text-md-end">Email</label>
                <div class="col-md-8">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>
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

                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password">

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


   