@extends('layouts.app')

@section('content')
<div class="panel-body">                                    
    <form action="{{ route('profile.update') }}" method="post" id="update-form" autocomplete="off">
        @csrf

        <div class="well">
            <b>Profile</b>                      
        </div>

        <div class="alert alert-success" style="display:none;">                  
            
        </div>

        <div class="row">
            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="email" class="col-md-2 col-form-label text-md-end">Email</label>
                <div class="col-md-8">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" autocomplete="email">
                    <span class="error"></span>
                </div>
            </div>

            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="password" class="col-md-2 col-form-label text-md-end">Password</label>
                <div class="col-md-8">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                    <span class="error"></span>                
                </div>
            </div>

            <div class="col-md-8 mb-1 col-md-offset-2">
                <label for="password" class="col-md-2 col-form-label text-md-end">Confirm Password</label>
                <div class="col-md-8">
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">                          
                </div>
            </div>
            
            <div class="col-md-8 mb-1 col-md-offset-2 text-center">                                 
                <input type="submit" class="btn btn-default" name="update" id="update"  value="Update">
            </div>              
        </div>              
    </form>             
</div>
@endsection
@section('script')
<script>

$(document).ready(function() 
{
    var updateurl="{{ route('profile.update') }}";
    $('#update-form').submit(function(e) 
    {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: updateurl,
            type: "POST",
            data: formData,
            success: function(result) 
            {
                if (result.status == true) 
                {   
                   $('.error').html("");
                   $('.alert-success').html(result.message).show();
                   setTimeout(function() 
                   {
                        location.reload();
                    }, 2000);
                  
                } 
                else
                {                    
                    first_input = "";
                    $('.error').html("");
                    $.each(result.errors, function(key) 
                    {
                        if(first_input=="") first_input=key;                        
                            $('#'+key).closest('.col-md-8').find('.error').html(result.errors[key]);                        
                    });
                }
            },
            error: function(xhr, status, error) 
            {
                alert('Something went wrong!', 'error');
            }
        });
    });
});
</script>
@endsection