@extends('layouts.app')

@section('content')
    @php
        $auth_id = Auth::user()->id;
    @endphp
    @if (isset($error))
        <div class="panel-body">
            <div class="alert alert-danger">
                <b>Error!</b><br>
                {{ $error }}
            </div>
            <div class="spacer">
                <a href="{{ route('home') }}" class="btn btn-default"> Write a New Message</a>
            </div>
        </div>
    @endif
    @if (isset($message))
        <div class="panel-body" id="confirmation">
            <div class="alert alert-warning">
                <b>Confirmation</b><br>
                Click on the button below to read the message.
            </div>

            <div class="spacer">
                <form id="confirmation-form" action="{{ route('message.confirm', ['token' => $message->url]) }}"
                    method="post">
                    @csrf
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" rel="nofollow" class="btn btn-default"> Continue</a>
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
                                <label for="upload-files">
                                    <h4 class="modal-title">Upload files</h4>
                                </label>
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

    <div class="modal fade" id="notesModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <!-- Modal Header -->
                <form action="" method="post" id="message-notes">
                    @csrf
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="message-notes">
                                    <h4 class="modal-title">Message Notes</h4>
                                </label>
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
                                <div class="col-md-2">
                                    <label for="choose-file">Notes : <span class="error">*</span></label>
                                </div>
                                <div class="col-md-6 form-input">
                                    <textarea type="text" id="notes" name="notes" rows="5" class="form-control"
                                        placeholder="Enter Your Notes"></textarea>
                                    <span class="error"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="message">Message : </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="message" class="form-control" id="messages" readonly>
                                    <span id="message"></span>
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

@if (isset($message))
    @section('script')
        <script>
            function showReplyTextarea() {
                var replyform = document.getElementById('reply-form');
                replyform.style.display = 'block';
                var replybut = document.getElementById('reply-btn');
                replybut.style.display = 'none';
                callTextCounter();
            }


            $(document).ready(function() {

                $('#notesModal').on('show.bs.modal', function(event) {
                    var link = $(event.relatedTarget); // Link that triggered the modal
                    var message = link.data('message'); // Extract info from data-* attributes
                    var formattedMessage = message ? '<pre>' + message.replace(/\n/g, '<br>') + '</pre>' : '';

                    $('#message').html(formattedMessage);
                    $('#messages').val(message);
                });

                $('#notesModal').on('hidden.bs.modal', function() {
                    $('#message').empty();
                    $('#messages').val(message);
                });

                var notesUrl = "{{ route('notes.add') }}";
                $('#message-notes').submit(function(e) {
                    e.preventDefault();
                    $('.error').html("");

                    var $this = $(this);
                    var formData = $(this).serialize();

                    $.ajax({
                        url: notesUrl,
                        type: "get",
                        data: formData,
                        success: function(response) {

                            $($this).find('button[type="submit"]').prop('disabled', false);
                            if (response.status == true) {
                                $this[0].reset();
                                toastr.success(response.message);
                                $('#notesModal').modal('hide');
                            } else {

                                first_input = "";
                                $.each(response.errors, function(key) {
                                    if (first_input == "") first_input = key;
                                    $('#' + key).closest('.form-input').find('.error').html(
                                        response.errors[key]);
                                });
                                $('#message-notes').find("#" + first_input).focus();

                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Something went wrong!', 'error');
                        }
                    });
                });

                $('#confirmation-form').submit(function(e) {
                    var getHtmlurl = "{{ route('message.read', ['token' => $message->url]) }}";
                    e.preventDefault();
                    var formData = $(this).serialize();

                    $.ajax({
                        url: getHtmlurl,
                        type: "get",
                        data: formData,
                        success: function(response) {
                            $('#confirmation').hide();
                            $('#message-box').html(response.message_html);
                            /*var deleteCon = "{{ route('chat.delete', ['token' => 'CHAT_TOKEN']) }}";
                                deleteCon = deleteCon.replace('CHAT_TOKEN', response.message.conversation_token);
                                $('#delete-chat').attr('action', deleteCon); */

                        },
                        error: function(xhr, status, error) {
                            alert('Something went wrong!', 'error');
                        }
                    });
                });

                var replyurl = "{{ route('messages.reply') }}";
                $('body').on('click', '#sendreply', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var formData = $('#reply-form').serialize();
                    $('#count').text('10000');
                    $('#copy-url-button').html('Copy');
                    $.ajax({
                        url: replyurl,
                        type: "POST",
                        data: formData,
                        beforeSend: function() {
                            $($this).find('button[type="submit"]').prop('disabled', true);
                        },
                        success: function(response) {
                            $($this).find('button[type="submit"]').prop('disabled', false);
                            if (response.status == true) {
                                $('#reply-form')[0].reset();
                                $('.error').html("");
                                $('#createurl').show();
                                $('#message_time').html(response.ttl);
                                var generatedurl = "{{ asset('/') }}" + response.message.url;
                                //var token=response.message.conversation_token;
                                $('#noteurl1').val(generatedurl);

                                var deleteAction =
                                    "{{ route('message.delete', ['token' => 'TOKEN_PLACEHOLDER']) }}";
                                deleteAction = deleteAction.replace('TOKEN_PLACEHOLDER', response
                                    .message.url);
                                $('#delete-form').attr('action', deleteAction);

                            } else {
                                first_input = "";
                                $('.error').html("");
                                $.each(response.errors, function(key) {
                                    if (first_input == "") first_input = key;

                                    $('#' + key).closest('.form-group').find('.error').html(
                                        response.errors[key]);

                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            $($this).find('button[type="submit"]').prop('disabled', false);
                            alert('Something went wrong!', 'error');
                        }
                    });
                });

                var createimage = "{{ route('image.store') }}";
                $('#image-store').submit(function(e) {
                    e.preventDefault();
                    var dataString = new FormData($('#image-store')[0]);
                    var $this = $(this);

                    $.ajax({
                        type: 'POST',
                        url: createimage,
                        data: dataString,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            $($this).find('button[type="submit"]').prop('disabled', true);
                        },
                        success: function(result) {
                            $('.error').html("");
                            $($this).find('button[type="submit"]').prop('disabled', false);
                            if (result.status == true) {
                                $this[0].reset();
                                toastr.success(result.message);
                                $('#imageModal').modal('hide');

                                var imgLinks = result.imageLinks;
                                var imgIds = result.imageIds;

                                var linksHtml = '';
                                $.each(imgLinks, function(index, link) {
                                    linksHtml += link + '\n';
                                });

                                const textarea = document.getElementById("reply");
                                const charCount = document.getElementById("count");
                                const maxCharLimit = 10000;

                                if (textarea.value.length + linksHtml.length > maxCharLimit) {
                                    const errorMessage = document.getElementById("error");
                                    errorMessage.textContent = "Character limit exceeded!";
                                    setTimeout(function() {
                                        errorMessage.textContent =
                                            ""; // Clear the error message
                                    }, 5000);
                                } else {
                                    $('#reply').val(function(index, currentValue) {
                                        const newContent = currentValue + '\n' + linksHtml;
                                        if (newContent.length <= maxCharLimit) {
                                            return newContent;
                                        } else {
                                            const errorMessage = document.getElementById(
                                                "error");
                                            errorMessage.textContent =
                                                "Character limit exceeded!";
                                            setTimeout(function() {
                                                errorMessage.textContent =
                                                    ""; // Clear the error message
                                            }, 5000);
                                            return currentValue; // Do not exceed character limit
                                        }
                                    });
                                }

                                const remainingChars = maxCharLimit - textarea.value.length;
                                charCount.textContent = remainingChars;
                                $('#img-ids').val(imgIds);
                            } else {
                                first_input = "";
                                $('.error').html("");
                                $.each(result.errors, function(key) {
                                    if (first_input == "") first_input = key;
                                    if (key.includes(".")) {
                                        let main_key = key.split('.')[0];
                                        $('#' + main_key).closest('.form-input').find(
                                            '.error').html(result.errors[key]);
                                    } else {
                                        $('#' + key).closest('.form-input').find('.error')
                                            .html(result.errors[key]);
                                    }

                                });
                                $('#image-store').find("#" + first_input).focus();
                            }
                        },
                        error: function(error) {
                            $($this).find('button[type="submit"]').prop('disabled', false);
                            alert('Something went wrong!', 'error');
                        }
                    });

                });

                $('#imageModal').on('hidden.bs.modal', function(e) {
                    $('#image-store')[0].reset();
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                callTextCounter();
            });

            function callTextCounter() {
                const textarea = document.getElementById("reply");
                const charCount = document.getElementById("count");
                if (textarea) {
                    textarea.addEventListener("input", function() {
                        const remainingChars = 10000 - textarea.value.length;
                        charCount.textContent = remainingChars;
                    });
                }
            }
        </script>
    @endsection
@endif
