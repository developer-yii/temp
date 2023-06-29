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
        <div class="well">
            <b>How to use this?</b><br>
            1. Write a message<br>
            2. Set the timer. It will trigger the auto self-destruction if the message won't be read in time<br>
            3. Click "Create Message"<br>
            4. Copy the URL that will be generated for you and send it to the message recipient<br>
        </div>
        <div class="form-group">
            <textarea name="note" id="note" class="form-control form-message" rows="8" maxlength="33554432" autofocus="autofocus" autocomplete="off" style="margin-bottom: 20px; resize: vertical;"></textarea>
            <span class="error"></span> 
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
            • The contents of this page will disappear in 1 hour.
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
        <a href="#" class="btn btn-default" onclick="confirmDelete(event)">Delete This Message</a>
        <form id="delete-form" action="" method="POST" style="display: none;">
            @csrf
        </form>
    </div>                            
</div>
@endsection
@section('script')

<script>
$(document).ready(function() {
    var createurl="{{ route('messages.store') }}";
    $('#message-form').submit(function(e) 
    {        
        e.preventDefault();
       var formData = $(this).serialize();

        $.ajax({
            url: createurl,
            type: "POST",
            data: formData,
            success: function(response) 
            {
                if (response.status == true) 
                {
                    //$('#createmessage').hide();
                    $('#message-form')[0].reset();
                    $('#createurl').show();
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
});

</script>
@endsection
