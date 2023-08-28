@extends('layouts.app')

@section('content')
@php
$auth_id=Auth::user()->id;
@endphp  
@if(isset($error))
    <div class="panel-body">
        <div class="alert alert-danger">
            <b>Error!</b><br>
            {{ $error }}
        </div>  
        <div class="spacer">
            <a href="{{ route('home')}}" class="btn btn-default"> Write a New Message</a>
        </div>   
    </div>
@endif
@if(isset($message))
    <div class="panel-body" id="confirmation">
        <div class="alert alert-warning">
            <b>Confirmation</b><br>
            Click on the button below to read the message.
        </div> 

        <div class="spacer">
            <form id="confirmation-form" action="{{ route('message.confirm', ['token' => $message->url]) }}" method="post">
                @csrf
                <input type="hidden" name="confirm" value="yes">
                <button type="submit"  rel="nofollow" class="btn btn-default"> Continue</a>
            </form>
        </div>
    </div>
@endif
    <div id="message-box">
    </div>
    
@endsection
@section('modal')
    <div class="modal fade" id="imageModal" role="dialog">
        <div class="modal-dialog">
        
          <!-- Modal content-->
            <div class="modal-content">
                <!-- Modal Header -->
                    <form action="" method="post" id="image-store" enctype="multipart/form-data">
                        @csrf
                      <div class="modal-header">
                        <div class="row">
                            <div class="col-md-6">
                              <label for="upload-files"><h4 class="modal-title">Upload files</h4></label>
                            </div>
                            <div class="col-md-6">
                              <button type="button" id="btn-close" class="close" data-dismiss="modal">&times;</button>
                            </div>
                        </div>                    
                      </div>
                      
                      <!-- Modal Body -->
                      <div class="modal-body">
                        <div class="container">
                          <div class="row mb-1">
                            <div class="col-md-3">
                              <label for="choose-file">Choose File<span class="error">*</span>:</label>
                            </div>
                            <div class="col-md-5 form-input">
                              <input type="file" id="files" name="files[]" class="form-control" multiple accept="image/*">
                              <span class="error"></span>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-3">
                              <label for="password">File(s) Password :</label>
                            </div>
                            <div class="col-md-5">
                              <input type="password" id="password" name="password" class="form-control">
                            </div>
                          </div>
                        </div>
                      </div>
                  
                      <!-- Modal Footer -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Save</button>
                      </div>
                  </form>
            </div>
        </div>
    </div>
@endsection

@if(isset($message))
@section('script')
<script>
function showReplyTextarea() 
{
    var replyform = document.getElementById('reply-form');
    replyform.style.display = 'block';
    var replybut = document.getElementById('reply-btn');
    replybut.style.display = 'none';
}


$(document).ready(function() 
{
    $('#confirmation-form').submit(function(e) 
    {    
        var getHtmlurl="{{ route('message.read', ['token' => $message->url]) }}";
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: getHtmlurl,
            type: "get",
            data: formData,
            success: function(response) 
            {
                $('#confirmation').hide();
                $('#message-box').html(response.message_html); 
                /*var deleteCon = "{{ route('chat.delete', ['token' => 'CHAT_TOKEN']) }}";
                    deleteCon = deleteCon.replace('CHAT_TOKEN', response.message.conversation_token);
                    $('#delete-chat').attr('action', deleteCon); */
                
            },
            error: function(xhr, status, error) 
            {
                alert('Something went wrong!', 'error');
            }
        });
    });

    var replyurl="{{ route('messages.reply') }}";
    $('body').on('click', '#sendreply', function(e) {
        e.preventDefault();
       var formData = $('#reply-form').serialize();

        $.ajax({
            url: replyurl,
            type: "POST",
            data: formData,
            success: function(response) 
            {
                if (response.status == true) 
                {

                    //$('#createmessage').hide();
                    $('#reply-form')[0].reset();
                    $('#createurl').show();
                    $('#message_time').html(response.ttl);
                    var generatedurl="{{ asset('/') }}" + response.message.url;
                    //var token=response.message.conversation_token;
                    $('#noteurl1').val(generatedurl);     

                    var deleteAction = "{{ route('message.delete', ['token' => 'TOKEN_PLACEHOLDER']) }}";
                    deleteAction = deleteAction.replace('TOKEN_PLACEHOLDER', response.message.url);
                    $('#delete-form').attr('action', deleteAction);
                    
                } 
                else
                {                    
                    first_input = "";
                    $('.error').html("");
                    $.each(response.errors, function(key) 
                    {
                        if(first_input=="") first_input=key;
                        
                            $('#'+key).closest('.form-group').find('.error').html(response.errors[key]);
                        
                    });
                }
            },
            error: function(xhr, status, error) 
            {
                alert('Something went wrong!', 'error');
            }
        });
    });

    var createimage="{{ route('image.store') }}";
    $('#image-store').submit(function(e) 
    { 
        e.preventDefault();        
        var dataString = new FormData($('#image-store')[0]);
        var $this = $(this);

        $.ajax({
            type: 'POST',
            url: createimage,
            data: dataString,
            processData: false,
            contentType: false,
            beforeSend: function() 
            {
                $($this).find('button[type="submit"]').prop('disabled', true);
            },
            success: function(result) 
            {
                $($this).find('button[type="submit"]').prop('disabled',false);
                if(result.status == true)
                {                
                    $this[0].reset();
                    toastr.success(result.message);
                    $('#imageModal').modal('hide'); 
                    
                    var imgLinks = result.imageLinks;           
                    var imgIds = result.imageIds;           

                    var linksHtml = '';
                    $.each(imgLinks, function(index, link) 
                    {
                        linksHtml += link + '\n';
                    });
                                       
                    $('#reply').val(function(index, currentValue) 
                    {
                        return currentValue + '\n' + linksHtml; // Add the links below the existing content
                    });
                    $('#img-ids').val(imgIds);

                }                
                else 
                {   
                    first_input = "";
                    $('.error').html("");
                    $.each(result.errors, function(key) {
                        if(first_input=="") first_input=key;
                        $('#'+key).closest('.form-input').find('.error').html(result.errors[key]);
                    });
                    $('#image-store').find("#"+first_input).focus();
                }
            },
            error: function(error) 
            {
                $($this).find('button[type="submit"]').prop('disabled', false);
                alert('Something went wrong!', 'error');
            }
        });
        
    });
});
</script>
@endsection
@endif