@php
    $userid = Auth::user()->id;
    $baseUrl = asset('uploaded_images')."/";
@endphp
@extends('layouts.app')

@section('content')
<div class="panel-body" id="messagereply">
    @if($image)
        <form action="" method="post" id="file-download">
            <input type="hidden" value="{{ $image->id}}" name="id">

            <fieldset class="fieldset-border">
                <legend class="legend-border">Download : {{ $image->file_name}}</legend>
                <div class="container">
                  <div class="row mb-1">
                    <div class="col-md-6">
                      <center>
                        <button type="submit" name="btn btn-default" style="border : 0px;">
                            <img src="{{ asset('images/download.gif') }}" border="0">
                        </button>
                      </center>
                    </div>
                    <!-- <div class="col-md-6">
                        <center>
                            @php
                                $fileInfo = pathinfo($imagePath);
                                $validExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'bmp'];
                            @endphp

                            @if(empty($image->password) && !empty($imagePath) && in_array($fileInfo['extension'], $validExtensions))
                                <img src="{{ $imagePath }}" style="max-height: 200px; max-width: 100%;">
                            @endif
                        </center>
                    </div> -->
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

                @php
                    $fileInfo = pathinfo($imagePath);
                    $validExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'bmp', 'jfif'];
                @endphp
                @if(empty($image->password) && !empty($imagePath) && in_array($fileInfo['extension'], $validExtensions))
                    <div class="container row col-md-12">
                    <div class="row mb-1 col-md-11">
                        <fieldset class="fieldset-border col-md-offset-1">
                            <legend class="legend-border">Image Preview</legend>
                            <div class="col-md-12 mb-1">
                                <center>
                                    <img src="{{ $imagePath }}" style="max-height: auto; max-width: 100%;">
                                </center>
                            </div>
                        </fieldset>
                    </div>
                    </div>
                @endif
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


