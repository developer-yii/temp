@extends('layouts.app')

@section('content')
@if(session('success'))
<div class="panel-body">                
    <div class="alert alert-success">
        <b>{{ session('success') }}</b><br>
        • It has now been securely deleted, as you requested.<br>
    </div>

    <div class="spacer">
        <a href="{{ route('home') }}" class="btn btn-default"> Write a New Message</a>
    </div>
                            
</div>
@elseif(session('error'))
<div class="panel-body">
    <div class="alert alert-danger">
        <b>Error!</b><br>
        {{ session('error') }}
    </div>   
    <div class="spacer">
        <a href="{{ route('home')}}" class="btn btn-default"> Write a New Message</a>
    </div>   
</div>
@else
<div class="panel-body" id="createmessage">                                    
    <form action="" method="post" id="message-form" autocomplete="off">
        @csrf
        <input type="hidden" name="imgids" value="" id="img-ids">
        <div class="well">
            <b>How to use this?</b><br>
            1. Write a message<br>
            2. Set the timer. It will trigger the auto self-destruction if the message won't be read in time<br>
            3. Click "Create Message"<br>
            4. Copy the URL that will be generated for you and send it to the message recipient<br>
        </div>
        <div class="form-group">
            <textarea name="note" id="note" class="form-control form-message" rows="8" maxlength="500" autofocus="autofocus" autocomplete="off" style="margin-bottom: 20px; resize: vertical;"></textarea>
            <span class="error" id="error"></span> 
            <div id="char-count">
                Characters remaining: 
                <span id="count">500</span>
                <span id="maximum">/ 500</span>
            </div>
        </div>

        <div class="spacer">
            <input type="submit" class="btn btn-default" name="create" id="create"  value="Create Message">
        </div>

        <div class="spacer">
            <div class="form-group">
                <select name="ttl" class="form-control">
                    <optgroup label="Minutes">
                        <option value="15m">15 minutes</option>
                        <option value="30m">30 minutes</option>
                        <option value="45m">45 minutes</option>
                    </optgroup>

                    <optgroup label="Hours">
                        <option value="1h">1 hour</option>
                        <option value="6h">6 hours</option>
                        <option value="12h">12 hours</option>
                    </optgroup>

                    <optgroup label="Days">
                        <option value="1d">1 day</option>
                        <option value="3d" selected="">3 days</option>
                        <option value="7d">7 days</option>
                    </optgroup>

                    <optgroup label="Months">
                        <option value="30d">1 month</option>
                        <option value="60d">2 months</option>
                    </optgroup>
                </select>
                <span class="error"></span> 
            </div>
        </div>

        <div class="spacer">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#imageModal"> 
                <img src="{{ asset('images/upload.png') }}" width="16" height="16" border="0" align="absmiddle"> 
                <b>Upload files !</b> 
            </button>
        </div>                              
    </form>            
</div>
@endif
<div class="panel-body" id="createurl" style="display: none;">
    <div class="alert alert-success">
        <b>Message was created successfully!</b><br>
        <div style="text-align: justify;">
            • Copy the URL below and send it to the recipient.<br>
            • The message will self-destruct after being read or after the timer expires if the message hasn't been read in time.<br>
            • In case you need to delete the message you just wrote, use the corresponding button.<br>
            • The contents of this page will disappear in <span id="message_time"></span>.
        </div>        
    </div>

    <div class="well">               
        <div class="input-group" id="clipboardjs-group" style=""><b>URL</b>
            <input type="text" class="form-control form-url-normal clipboardjs" name="noteurl1" id="noteurl1" data-clipboard-target="#noteurl1" autocomplete="off" readonly>
            
            <span class="input-group-btn">
                <button class="btn btn-default clipboardjs" type="button" id="copy-url-button" data-clipboard-target="#noteurl1" style="margin-top: 27px;">Copy</button>
            </span>
        </div>
    </div>
    <div class="spacer">
        <a href="" class="btn btn-default"> Write Another Message</a>
        <!-- <a href="#" class="btn btn-default" onclick="confirmDelete(event)">Delete This Message</a>
        <form id="delete-form" action="" method="POST" style="display: none;">
            @csrf
        </form> -->
    </div>                            
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
                              <input type="file" id="files" name="files[]" class="form-control" multiple>
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
@section('script')

<script>
$(document).ready(function() {
    var createurl="{{ route('messages.store') }}";
    var createimage="{{ route('image.store') }}";

    $.ajaxSetup({
        headers : {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#message-form').submit(function(e) 
    {        
        e.preventDefault();
        var $this = $(this);
        var formData = $(this).serialize();
        $('#count').text('500');
        $('#copy-url-button').html('Copy');
        $.ajax({
            url: createurl,
            type: "POST",
            data: formData,
            beforeSend: function() 
            {
                $($this).find('button[type="submit"]').prop('disabled', true);
            },
            success: function(response) 
            {
                $($this).find('button[type="submit"]').prop('disabled',false);
                if (response.status == true) 
                {
                    $('#message-form')[0].reset();
                    $('.error').html("");
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
                $($this).find('button[type="submit"]').prop('disabled', false);
                alert('Something went wrong!', 'error');
            }
        });
    });

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

                    const textarea = document.getElementById("note");
                    const charCount = document.getElementById("count");
                    const maxCharLimit = 500;

                    if (textarea.value.length + linksHtml.length > maxCharLimit) 
                    {                    
                        const errorMessage = document.getElementById("error");
                        errorMessage.textContent = "Character limit exceeded!";
                        setTimeout(function () {
                            errorMessage.textContent = ""; // Clear the error message
                        }, 5000);
                    }
                    else 
                    {
                        $('#note').val(function (index, currentValue) {
                            const newContent = currentValue + '\n' + linksHtml;
                            if (newContent.length <= maxCharLimit) 
                            {
                                return newContent;
                            } 
                            else 
                            {
                                const errorMessage = document.getElementById("error");
                                errorMessage.textContent = "Character limit exceeded!";
                                setTimeout(function () {
                                    errorMessage.textContent = ""; // Clear the error message
                                }, 5000);                                
                                return currentValue; // Do not exceed character limit
                            }
                        });
                    }

                    const remainingChars = maxCharLimit - textarea.value.length;
                    charCount.textContent = remainingChars;

                    $('#img-ids').val(imgIds);

                }                
                else 
                {                     
                    first_input = "";
                    $('.error').html("");
                    $.each(result.errors, function(key) {                        
                        if(first_input=="") first_input=key;
                        if (key.includes(".")) 
                        {
                            let main_key = key.split('.')[0];
                            $('#'+main_key).closest('.form-input').find('.error').html(result.errors[key]);
                        }
                        else
                        {
                            $('#'+key).closest('.form-input').find('.error').html(result.errors[key]);    
                        }
                        
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

    $('#imageModal').on('hidden.bs.modal',function(e){
        $('#image-store')[0].reset();       
    });

});
document.addEventListener("DOMContentLoaded", function () {
    const textarea = document.getElementById("note");
    const charCount = document.getElementById("count");
    textarea.addEventListener("input", function () {
        const remainingChars = 500 - textarea.value.length;
        charCount.textContent = remainingChars;
    });
});
</script>
@endsection

