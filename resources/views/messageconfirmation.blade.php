@extends('layouts.app')

@section('content')
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
    @else
        @php
            $auth_id = Auth::user()->id;
            $expiryTimestamp = strtotime($conversation->expiry);
            $currentTimestamp = time();
            $diffInSeconds = $expiryTimestamp - $currentTimestamp;
            $diffInDays = $diffInSeconds / (60 * 60 * 24);
            $alertExpiryMessage ="";
            if ($diffInDays <= 1) {
                $alertExpiryMessage = "This chat will be deleted within 1 days.";
            }
            $expirydate = date('d-m-Y H:i:s', $expiryTimestamp);
        @endphp
        <div class="panel-body" id="messagereply">
            <div class="alert alert-warning">
                <b>Attention!</b><br>
                <div style="text-align: justify;">
                    • If you need to save the message contents somewhere, please make sure you use appropriate
                    encryption.<br>
                    • The contents of this page will disappear in <span id="message_time2">{{ $expirydate }}</span>.
                </div>
            </div>
            <div class="panel-body" id="message-list">
                @php $lastmessageid = ''; @endphp
                @foreach ($data as $replydata)
                    @php
                        $date = date('d-m-Y H:i', strtotime($replydata->created_at));
                        $lastmessageid = $replydata->id;
                    @endphp
                    <div class="panel panel-default panel-message1">
                        <div class="panel-body panel-message2">
                            <b>{{ $replydata->email }} - </b> {{ $date }} <br>
                            <pre>{{ $replydata->message }}</pre>
                            <a data-toggle="modal" data-target="#notesModal" class="open-notes-modal" data-sender-id="{{ $replydata->user_id }}" data-message="{{ $replydata->message }}" title="Note"><i class="fa fa-sticky-note-o ml-1"
                                    aria-hidden="true" style="cursor: pointer;"></i></a>
                            @if ($replydata->user_id == $auth_id)
                                <a class="delete-message" data-message-id="{{ $replydata->id }}" title="Delete">
                                    <i class="fa fa-trash-o" aria-hidden="true" style="cursor: pointer;"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="post" id="reply-form" autocomplete="off" style="display:none;">
                @csrf
                <input type="hidden" name="imgids" value="" id="img-ids">
                <input type="hidden" name="token" value="{{ $conversation->conversation_token }}" id="token">
                <input type="hidden" name="last_message_id" value="{{ $lastmessageid }}" id="last_message_id">
                <div class="form-group">
                    <textarea name="reply" id="reply" class="form-control form-message" rows="8" maxlength="10000"
                        autofocus="autofocus" autocomplete="off" style="margin-bottom: 20px; resize: vertical;"></textarea>
                    <span class="error" id="error"></span>
                    <div id="char-count">
                        Characters remaining:
                        <span id="count">10000</span>
                        <span id="maximum">/ 10000</span>
                    </div>
                    <div class="text-danger" style="font-weight: bold;">{{$alertExpiryMessage}}</div>
                </div>
                <div class="spacer form-group">
                    <button type="button" name="sendreply" class="btn btn-default" id="sendreply">Send Message</a>
                </div>
                <div class="spacer">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#imageModal">
                        <img src="{{ asset('images/upload.png') }}" width="16" height="16" border="0"
                            align="absmiddle">
                        <b>Upload files !</b>
                    </button>
                </div>
            </form>
            <div class="form-group">
                <a name="Reply" class="btn btnreply btn-primary" id="reply-btn" onclick="showReplyTextarea()">Reply</a>
            </div>
            <div class="spacer-block">
                <div class="spacer">
                    <a href="{{ route('home') }}" class="btn btn-default"> Create a New Chat</a>
                </div>

                <div class="spacer">
                    <a href="" class="btn btn-default" onclick="DeleteChat(event)">Delete This Chat</a>
                    <form id="delete-chat"
                        action="{{ route('chat.delete', ['token' => $conversation->conversation_token]) }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                </div>

                @if($conversation->no_of_extends < 2)
                    <div class="spacer">
                        <button class="btn btn-info btn-extends-validity" data-id="{{ $conversation->conversation_token }}" data-toggle="modal" data-target="#validityModal">Extends Validity?</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
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
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="file_name">File Name : <span class="mandatory">*</span></label>
                                </div>
                                <div class="col-md-5 form-group">
                                    <input type="text" id="file_name" name="file_name" class="form-control">
                                    <span class="error"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="choose-file">Choose File<span class="error">*</span>:</label>
                                </div>
                                <div class="col-md-5 form-input form-group">
                                    <input type="file" id="files" name="files[]" class="form-control" multiple>
                                    <span class="error"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 form-group">
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

    <div class="modal fade" id="notesModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
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
                            <input type="hidden" name="sender_id" id="sender_id">
                            <div class="row mb-1">
                                <div class="col-md-2">
                                    <label for="choose-file">Notes : <span class="mandatory">*</span></label>
                                </div>
                                <div class="col-md-10 form-input">
                                    <textarea type="text" id="notes" name="notes" rows="5" class="form-control"
                                        placeholder="Enter Your Notes"></textarea>
                                    <span class="error"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="message">Message : </label>
                                </div>
                                <div class="col-md-10">
                                    <textarea name="message" class="form-control" id="messages" rows="10"></textarea>
                                    {{-- <span id="message"></span> --}}
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

    <div class="modal fade" id="validityModal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <!-- Modal Header -->
                <form method="post" id="extends-validity">
                    @csrf
                    <div class="modal-header">
                        <input type="hidden" name="token" value="" class="conversation_token">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="extends-validity">
                                    <h4 class="modal-title">Extends Validity</h4>
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
                                    <label for="days">Days : <span class="error">*</span></label>
                                </div>
                                <div class="col-md-6 form-input">
                                    <select id="extend_days" name="extend_days" class="form-control">
                                        <option value="" style="display:none;">Select Days</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                    <span class="error"></span>
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

@if (isset($conversation))
    @section('script')
        <script>
            var fetchData = "{{ route('message.fetchData') }}";
            var replyurl = "{{ route('messages.reply') }}";
            var deleteUrl = "{{ route('message.delete') }}";
            var createimage = "{{ route('image.store') }}";

            $('body').on('click', '.delete-message', function() {
                var id = $(this).attr('data-message-id');
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var confirmDelete = window.confirm("Are you sure you want to delete this message?");
                if(confirmDelete){
                    $.ajax({
                        url: deleteUrl,
                        data: {
                            id: id
                        },
                        type: 'POST',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken // Include CSRF token in the request header
                        },
                        success: function(result) {
                            if (result.status == true) {
                                toastr.success(result.message);
                                setTimeout(function() {
                                    window.location.reload();
                                }, 3000);
                            } else {
                                toastr.error(result.message);
                            }
                        },
                        error: function(error) {
                            toastr.error('An error occurred while deleting the user.');
                        }
                    });
                }else{
                    toastr.info('Deletion canceled.');
                }
            });


            // document.querySelectorAll('.delete-message').forEach(function(button) {
            //     button.addEventListener('click', function() {
            //         const messageId = this.getAttribute('data-message-id');
            //         alert(messageId);
            //         $.ajax({
            //             url: deleteUrl,
            //             data: { 'messageId': messageId},
            //             type: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            //             },
            //             success: function(result) {
            //                 if (result.status == true) {
            //                     toastr.success("message deleted successfully");
            //                 }
            //             }
            //         });
            //     });
            // });

            function fetchMessages() {
                var token = $('#token').val();
                var lastid = $('#last_message_id').val();

                if (token !== undefined && lastid !== undefined) {
                    $.ajax({
                        url: fetchData,
                        data: {
                            'token': token,
                            'lastid': lastid
                        },
                        type: 'GET',
                        success: function(result) {
                            if (result.status == true) {
                                if (result.data.length > 0) {
                                    $.each(result.data, function(key, value) {
                                        if (value.user_id != value.auth_id) {
                                            $("#last_message_id").val(value.id);
                                            var userEmail = value.user.email;
                                            var messageHtml = `
                                                <div class="panel panel-default panel-message1">
                                                    <div class="panel-body panel-message2">
                                                        <b>${userEmail} -</b> ${value.created_at} <br>
                                                        <pre>${value.message}</pre>
                                                        <a data-toggle="modal" data-target="#notesModal" class="open-notes-modal" data-sender-id="${value.user_id}" data-message="${value.message}" title="Note"><i class="fa fa-sticky-note-o ml-1" aria-hidden="true" style="cursor: pointer;"></i></a>
                                                        <a class="delete-message" data-message-id="${value.id}" title="Delete"><i class="fa fa-trash-o" aria-hidden="true" style="cursor: pointer;"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            `;

                                            $('#message-list').append(messageHtml);
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }

            setInterval(fetchMessages, 10000);


            function showReplyTextarea() {
                var replyform = document.getElementById('reply-form');
                replyform.style.display = 'block';
                var replybut = document.getElementById('reply-btn');
                replybut.style.display = 'none';
                callTextCounter();
            }

            $(document).ready(function() {

                // Note model start
                $('#notesModal').on('show.bs.modal', function(event) {
                    var link = $(event.relatedTarget);
                    var message = link.data('message');
                    var sender_id = link.data('sender-id');

                    var formattedMessage = '';
                    if (message) {
                        var messageStr = typeof message === 'string' ? message : String(message);
                        formattedMessage = '<pre>' + messageStr.replace(/\n/g, '<br>') + '</pre>';
                    }

                    // $('#message').html(formattedMessage);
                    $('#messages').val(message);
                    $('#sender_id').val(sender_id);
                });

                $('#notesModal').on('hidden.bs.modal', function() {
                    $('.error').html("");
                    // $('#message').empty();
                    // $('#messages').val(message);
                    $('#messages').val();
                    $('#sender_id').val();
                });

                var notesUrl = "{{ route('notes.add') }}";
                $('#message-notes').submit(function(e) {
                    e.preventDefault();
                    $('.error').html("");

                    var $this = $(this);
                    var formData = $(this).serialize();

                    $.ajax({
                        url: notesUrl,
                        type: "post",
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

                $('#message-notes').on('keyup change','input, textarea, select',function(event) {
                    if($.trim($(this).val()) && $(this).val().length >0){
                        $(this).closest('.form-input').find('.error').html('');
                    }
                });
                // Note model end

                var uploadedImgIds = [];
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
                                uploadedImgIds = uploadedImgIds.concat(imgIds);

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
                                        errorMessage.textContent = ""; // Clear the error message
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
                                                errorMessage.textContent = ""; // Clear the error message
                                            }, 5000);
                                            return currentValue; // Do not exceed character limit
                                        }
                                    });
                                }

                                const remainingChars = maxCharLimit - textarea.value.length;
                                charCount.textContent = remainingChars;
                                // $('#img-ids').val(imgIds);
                                $('#img-ids').val(uploadedImgIds.join(','));
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
                                        $('#' + key).closest('.form-group').find('.error')
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
                                $('#img-ids').val("");
                                uploadedImgIds = [];
                                $('.error').html("");

                                fetchMessages();

                                // var deleteAction =
                                //     "{{ route('message.delete', ['token' => 'TOKEN_PLACEHOLDER']) }}";
                                // deleteAction = deleteAction.replace('TOKEN_PLACEHOLDER', response
                                //     .message.url);
                                // $('#delete-form').attr('action', deleteAction);

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

                // extends Validity start
                var validityUrl = "{{ route('chat.extends-validity') }}";
                $('body').on('click','.btn-extends-validity',function()
                {
                    var id = $(this).data('id');
                    $(".conversation_token").val(id);
                });

                $('#extends-validity').submit(function(e) {
                    e.preventDefault();
                    $('.error').html("");

                    var $this = $(this);
                    var formData = $(this).serialize();

                    $.ajax({
                        url: validityUrl,
                        type: "POST",
                        data: formData,
                        beforeSend: function () {
                            $($this).find('button[type="submit"]').prop('disabled', true);
                        },
                        success: function(response) {
                            $($this).find('button[type="submit"]').prop('disabled', false);
                            if (response.status == true) {
                                $this[0].reset();
                                toastr.success(response.message);
                                $('#validityModal').modal('hide');
                                setTimeout(function() {
                                    window.location.reload();
                                }, 3000);
                            } else {
                                if (response.message){
                                    toastr.error(response.message);
                                }
                                first_input = "";
                                $.each(response.errors, function(key) {
                                    if (first_input == "") first_input = key;
                                    $('#' + key).closest('.form-input').find('.error').html(
                                        response.errors[key]);
                                });
                                $('#extends-validity').find("#" + first_input).focus();
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Something went wrong!', 'error');
                        }
                    });
                });

                $('#validityModal').on('hidden.bs.modal', function() {
                    $('.error').html("");
                    $('#extends-validity')[0].reset();
                    $(".conversation_token").val("");
                });

                $('#extends-validity').on('keyup change','input, textarea, select',function(event) {
                    if($.trim($(this).val()) && $(this).val().length >0){
                        $(this).closest('.form-input').find('.error').html('');
                    }
                });
                // extends Validity end

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
