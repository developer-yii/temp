@extends('layouts.app')

@section('content')
<div class="panel-body">                                    
    <form action="{{ route('password.update') }}" method="post">
        @csrf
        <div class="well">
            <b>Reset Password</b>                      
        </div>
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="row">
            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="email" class="col-md-2 col-form-label text-md-end">Email</label>
                <div class="col-md-8">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" autocomplete="email" autofocus readonly>

                    @error('email')
                        <span class="error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                </div>
            </div>

            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="password" class="col-md-2 col-form-label text-md-end">Password</label>
                <div class="col-md-8">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">

                    @error('password')
                        <span class="error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror                
                </div>
            </div>

            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="password" class="col-md-2 col-form-label text-md-end">Confirm Password</label>
                <div class="col-md-8">
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">                       
                </div>
            </div>
            
            <div class="col-md-8 mb-1 col-md-offset-2 text-center">                                 
                <input type="submit" class="btn btn-default" value="Reset Password">
            </div>  
            
        </div>              
    </form>             
</div>
@endsection
