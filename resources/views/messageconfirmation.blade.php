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
    </div>
    <div class="spacer">
        <a href="{{ route('home')}}" class="btn btn-default"> Clear This Page / Write a New Message</a>
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


$(document).ready(function() {
    

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
                var deleteCon = "{{ route('chat.delete', ['token' => 'CHAT_TOKEN']) }}";
                    deleteCon = deleteCon.replace('CHAT_TOKEN', response.message.conversation_token);
                    $('#delete-chat').attr('action', deleteCon); 
                
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
@endif