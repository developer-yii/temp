@php
    $userid = Auth::user()->id;    
    $baseUrl = asset('uploaded_images')."/";
@endphp
@extends('layouts.app')

@section('content')
<div class="panel-body" id="messagereply">
    @if($image)
        @if(!isset($image->password))
        <fieldset class="fieldset-border">
            <legend class="legend-border">{{ $image->image_name}}</legend>
            <div class="row mb-1">
                <div class="col-md-12 text-center">
                    <img src="{{ $image->getImageUrl() }}" height="100">
                </div>
            </div>
        </fieldset>
        @endif
        
        <form action="" method="post" id="file-download">
            <input type="hidden" value="{{ $image->id}}" name="id">      

            <fieldset class="fieldset-border">
                <legend class="legend-border">Download : {{ $image->image_name}}</legend>            
                <div class="container">
                  <div class="row mb-1">
                    <div class="col-md-6">
                      <center><button type="submit" name="btn btn-default">Download</button><br>
                      
                      <img src="{{ asset('images/download.gif') }}" border="0"></center>

                    </div>
                    @if(!empty($image->password))
                    <div class="col-md-4 form-input">
                        <fieldset class="fieldset-border">
                            <legend class="legend-border">Password</legend>
                            <div class="ml-1">                        
                              <input type="password" name="password"><br>
                              <strong>Please enter your password, <br>this file is password protected.</strong>
                            </div>
                        </fieldset>  
                    </div>
                    @endif
                  </div>             
                </div>
            </fieldset>
        </form>
    @else
        <fieldset class="fieldset-border">
            <legend class="legend-border">No file found</legend>
            <div class="row" style="padding:10px;">
                <div class="col-md-12 ml-1">
                    <font class="message-font">File does not exist</font>
                </div>
            </div>
        </fieldset>
    @endif
</div>
@endsection
@section('script')

<script>
$(document).ready(function() {
    var downloadurl="{{ route('image.download') }}";    

    $.ajaxSetup({
        headers : {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#file-download').submit(function(e) 
    {        
        e.preventDefault();
        var formData = $(this).serialize();
        var $this = $(this);

        $.ajax({
            url: downloadurl,
            type: "POST",
            data: formData,
            success: function(response) 
            {
                if (response.imagePath) 
                {
                    var link = document.createElement('a');
                    link.href = response.imagePath;
                    link.download = response.imagename;
                    link.target = '_blank';
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    $this[0].reset();                 
                    toastr.success(response.message);
                } 
                else 
                {
                    toastr.error(response.message);
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


