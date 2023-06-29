@extends('layouts.app')

@section('content')
<div class="panel-body">                                    
    <form action="{{ route('password.email') }}" method="post" id="reset-password-form" autocomplete="off">
        @csrf
        <div class="well">
            <b>Reset Password</b>                      
        </div>
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="email" class="col-md-2 col-form-label text-md-end">Email</label>
                <div class="col-md-8">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>

                    @error('email')
                        <span class="error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror                          
                </div>
            </div>                   
            <div class="col-md-8 mb-1 col-md-offset-2 text-center">                                 
                <input type="submit" class="btn btn-default" name="reset-password" id="reset-password"  value="Send Password Reset Link">
            </div>
        </div>                                  
    </form>             
</div>
@endsection
