@extends('layouts.app')

@section('css')
    <style>
        .panel-message2 pre {
            background: transparent !important;
        }

        .highlight {
            background-color: #ffd700;
            color: #000;
        }

        .highlight.active {
            background-color: #ff8c00;
            font-weight: bold;
        }

        /* Quote selection popup */
        #quote-selection-btn {
            display: none;
            position: absolute;
            background-color: #337ab7;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000;
            font-size: 13px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        #quote-selection-btn:hover {
            background-color: #286090;
        }

        /* Quoted text styling in reply preview */
        .quoted-text {
            font-style: italic;
            border-left: 3px solid #666;
            padding-left: 8px;
            color: #555;
        }
    </style>
@endsection

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
            $alertExpiryMessage = '';
            if ($diffInDays <= 1) {
                $alertExpiryMessage = 'This chat will be deleted within 1 days.';
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
                        $userBgColor = $userColors[$replydata->user_id] ?? '#ffffff';
                    @endphp
                    <div class="panel panel-default panel-message1" data-message-id="{{ $replydata->id }}" data-date="{{ date('Y-m-d', strtotime($replydata->created_at)) }}">
                        <div class="panel-body panel-message2" style="background-color: {{ $userBgColor }}">
                            @if ($replydata->repliedMessage)
                                @php
                                    $repliedUserColor = $userColors[$replydata->repliedMessage->user_id] ?? '#e0e0e0';
                                    $displayText = $replydata->quoted_text ?: $replydata->repliedMessage->message;
                                    $isQuoted = !empty($replydata->quoted_text);
                                    // Truncate long messages to 150 chars for preview
                                    if (strlen($displayText) > 150) {
                                        $displayText = substr($displayText, 0, 150) . '...';
                                    }
                                @endphp
                                <div class="reply-preview clickable-reply" data-replied-message-id="{{ $replydata->replied_message_id }}"
                                    style="padding:8px; position: relative; margin-bottom: 8px; background-color: {{ $repliedUserColor }}; border-left: 4px solid rgba(0,0,0,0.2); border-radius: 4px; cursor: pointer;">
                                    <strong>
                                        @if ($replydata->repliedMessage->user_id == $auth_id)
                                            You
                                        @else
                                            {{ $replydata->repliedMessage->user->email }}
                                        @endif
                                        @if ($isQuoted)
                                            <span style="font-weight: normal; color: #666;">(quoted)</span>
                                        @endif
                                    </strong>
                                    <pre style="margin:0; border: none; padding: 0;">@if ($isQuoted)"@endif{{ $displayText }}@if ($isQuoted)"@endif</pre>
                                </div>
                            @endif

                            <b>{{ $replydata->email }} - </b> {{ $date }} <br>
                            <pre>{!! makeLinksClickable(e($replydata->message)) !!}</pre>

                            <div class="message-actions"
                                style="margin-top: 10px; display: flex; gap: 15px; align-items: center;">
                                <a class="reply-specific-message" data-message-id="{{ $replydata->id }}"
                                    data-sender-id="{{ $replydata->email }}" data-message="{{ $replydata->message }}"
                                    data-color="{{ $userBgColor }}" title="Reply" style="text-decoration: none;">
                                    <i class="fa fa-reply" aria-hidden="true" style="cursor: pointer; font-size: 18px;"></i>
                                </a>

                                <a data-toggle="modal" data-target="#notesModal" class="open-notes-modal"
                                    data-sender-id="{{ $replydata->user_id }}" data-message="{{ $replydata->message }}"
                                    title="Note" style="text-decoration: none;">
                                    <i class="fa fa-sticky-note-o" aria-hidden="true"
                                        style="cursor: pointer; font-size: 18px;"></i>
                                </a>
                                @if ($replydata->user_id == $auth_id)
                                    <a class="delete-message" data-message-id="{{ $replydata->id }}" title="Delete"
                                        style="text-decoration: none;">
                                        <i class="fa fa-trash-o" aria-hidden="true"
                                            style="cursor: pointer; font-size: 18px;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Search Section -->
            <div
                style="margin-bottom: 20px; padding: 10px; background: #f9f9f9; border: 1px solid #eee; border-radius: 4px;">
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="width: 50%;">
                        <input type="text" id="word-search" class="form-control" placeholder="Search in chat..."
                            style="width: 100%;">
                    </div>
                    <div style="margin-left: auto;">
                        <input type="date" id="date-search" class="form-control" style="width: auto;">
                    </div>
                </div>
                <div id="search-nav" style="display: none; margin-top: 10px; align-items: center; gap: 5px;">
                    <span id="search-count" style="margin-right: 10px; font-weight: bold;"></span>
                    <button type="button" id="prev-match" class="btn btn-default btn-xs"><i
                            class="fa fa-arrow-up"></i></button>
                    <button type="button" id="next-match" class="btn btn-default btn-xs"><i
                            class="fa fa-arrow-down"></i></button>
                    <button type="button" id="clear-search" class="btn btn-danger btn-xs" title="Clear">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Scroll to Bottom Button -->
            <form method="post" id="reply-form" autocomplete="off" style="display:none;">
                @csrf
                <input type="hidden" name="imgids" value="" id="img-ids">
                <input type="hidden" name="token" value="{{ $conversation->conversation_token }}" id="token">
                <input type="hidden" name="last_message_id" value="{{ $lastmessageid }}" id="last_message_id">
                <div class="form-group">
                    <div class="custom_rpyBox">
                        {{-- Reply Preview (hidden initially) --}}
                        <div id="reply-preview" class="d-none reply-preview"
                            style="padding:10px; position: relative; border-radius: 4px; margin-bottom: 5px; border-left: 4px solid rgba(0,0,0,0.2);">
                            <strong id="reply-user"></strong>
                            <div class="panel-body panel-message2" style="background: transparent; padding: 0;">
                                <pre id="reply-text"
                                    style="margin:0; background: transparent; border: none; padding: 0; white-space: pre-wrap; word-wrap: break-word;"></pre>
                            </div>
                            <button type="button" class="close" id="cancel-reply"
                                style="position:absolute; top:5px; right:10px;">&times;</button>
                            <input type="hidden" id="reply_to_message_id" name="reply_to_message_id" value="">
                            <input type="hidden" id="quoted_text" name="quoted_text" value="">
                        </div>
                        <textarea name="reply" id="reply" class="form-control form-message" rows="8" maxlength="10000"
                            autofocus="autofocus" autocomplete="off"></textarea>
                    </div>
                    <span class="error" id="error"></span>
                    <div id="char-count">
                        Characters remaining:
                        <span id="count">10000</span>
                        <span id="maximum">/ 10000</span>
                    </div>
                    <div class="text-danger" style="font-weight: bold;">{{ $alertExpiryMessage }}</div>
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
                <a name="Reply" class="btn btnreply btn-primary" id="reply-btn"
                    onclick="showReplyTextarea()">Reply</a>
            </div>
            <div class="spacer-block">
                <div class="spacer">
                    <a href="{{ route('home') }}" class="btn btn-default"> Create a New Chat</a>
                </div>

                <div class="spacer">
                    <a href="" class="btn btn-default" onclick="DeleteChat(event)">Delete This Chat</a>
                    <form id="delete-chat"
                        action="{{ route('chat.delete', ['token' => $conversation->conversation_token]) }}"
                        method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>

                @if ($conversation->no_of_extends < 2)
                    <div class="spacer">
                        <button class="btn btn-info btn-extends-validity"
                            data-id="{{ $conversation->conversation_token }}" data-toggle="modal"
                            data-target="#validityModal">Extends Validity?</button>
                    </div>
                @endif

                <div class="spacer">
                    <button type="button" id="invite-button" class="btn btn-default" data-toggle="modal"
                        data-target="#inviteModal" data-user-id="{{ $auth_id }}"
                        data-conversation-id="{{ $conversation->id }}"
                        data-creator="{{ $conversation->user_id }}">Invite</button>
                </div>
            </div>
        </div>
        <button id="scrollToBottomBtn" style="display:none; position:fixed; bottom:20px; right:20px; z-index:1000;"
            class="btn btn-primary">
            <i class="fa fa fa-arrow-down"></i>
        </button>
        <!-- Quote Selection Button (appears on text selection) -->
        <button id="quote-selection-btn" type="button">
            <i class="fa fa-quote-left"></i> Reply to Selection
        </button>
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
                                <button type="button" id="btn-close" class="close"
                                    data-dismiss="modal">&times;</button>
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
                                <button type="button" id="btn-close" class="close"
                                    data-dismiss="modal">&times;</button>
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
                                <button type="button" id="btn-close" class="close"
                                    data-dismiss="modal">&times;</button>
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

    @include('modal.invite-user')
@endsection

@if (isset($conversation))
    @section('script')
        <script>
            var fetchData = "{{ route('message.fetchData') }}";
            var replyurl = "{{ route('messages.reply') }}";
            var deleteUrl = "{{ route('message.delete') }}";
            var createimage = "{{ route('image.store') }}";
            var loginUrl = "{{ route('login') }}";
            var getInviteUserUrl = "{{ route('invite.user.get') }}";
            var inviteUserUrl = "{{ route('invite.user.store') }}";
            var suggestableUsersUrl = "{{ route('invite.suggestable.users') }}";

            // Function to make URLs clickable
            function makeLinksClickable(text) {
                var urlPattern = /(https?:\/\/[^\s]+)/gi;
                return text.replace(urlPattern, '<a href="$1" target="_blank">$1</a>');
            }

            hideReplyPreview();
            $(document).on("click", ".reply-specific-message", function() {
                let senderId = $(this).data("sender-id");
                let message = $(this).data("message");
                let messageId = $(this).data("message-id");
                let color = $(this).data("color");

                // Show preview
                showReplyPreview();
                $("#reply-user").text(senderId);
                $("#reply-text").text(message);
                $("#reply_to_message_id").val(messageId);
                $("#quoted_text").val(""); // Clear quoted text for full message reply
                $("#reply-preview")
                    .removeClass("d-none")
                    .css("background-color", color);
                showReplyTextarea();
                $("#reply").focus();
            });

            // --- Text Selection Quote Feature ---
            let selectedText = '';
            let selectedMessagePanel = null;

            // Show quote button when text is selected inside a message
            $(document).on('mouseup', '.panel-message2 pre', function(e) {
                const selection = window.getSelection();
                const text = selection.toString().trim();

                if (text.length > 0) {
                    selectedText = text;
                    selectedMessagePanel = $(this).closest('.panel-message2');

                    // Position the button near the selection
                    const range = selection.getRangeAt(0);
                    const rect = range.getBoundingClientRect();

                    $('#quote-selection-btn').css({
                        display: 'block',
                        top: (rect.top + window.scrollY - 40) + 'px',
                        left: (rect.left + window.scrollX + (rect.width / 2) - 70) + 'px'
                    });
                }
            });

            // Hide quote button when clicking elsewhere
            $(document).on('mousedown', function(e) {
                if (!$(e.target).is('#quote-selection-btn') && !$(e.target).closest('#quote-selection-btn').length) {
                    const selection = window.getSelection();
                    if (selection.toString().trim().length === 0) {
                        $('#quote-selection-btn').hide();
                        selectedText = '';
                        selectedMessagePanel = null;
                    }
                }
            });

            // Handle quote button click
            $('#quote-selection-btn').on('click', function() {
                if (selectedText && selectedMessagePanel) {
                    const $messageActions = selectedMessagePanel.find('.reply-specific-message');
                    const senderId = $messageActions.data('sender-id');
                    const messageId = $messageActions.data('message-id');
                    const color = $messageActions.data('color');

                    // Show reply preview with quoted text only
                    showReplyPreview();
                    $("#reply-user").html(senderId + ' <span class="quoted-text-label" style="font-weight: normal; color: #666;">(quoted)</span>');
                    $("#reply-text").html('<span class="quoted-text">"' + escapeHtml(selectedText) + '"</span>');
                    $("#reply_to_message_id").val(messageId);
                    $("#quoted_text").val(selectedText);
                    $("#reply-preview")
                        .removeClass("d-none")
                        .css("background-color", color);

                    showReplyTextarea();

                    // Hide the quote button and clear selection
                    $('#quote-selection-btn').hide();
                    window.getSelection().removeAllRanges();
                    selectedText = '';
                    selectedMessagePanel = null;
                }
            });

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Cancel reply
            $(document).on("click", "#cancel-reply", function() {

                $("#reply_to_message_id").val("");
                $("#reply_to_message_id").val("");
                hideReplyPreview();
                hideReplyTextarea();
            });

            // Click on reply preview to scroll to original message
            $(document).on("click", ".clickable-reply", function(e) {
                e.stopPropagation();
                const repliedMessageId = $(this).data("replied-message-id");
                const $targetMessage = $(`.panel-message1[data-message-id="${repliedMessageId}"]`);

                if ($targetMessage.length) {
                    $targetMessage[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Highlight effect
                    const $panel = $targetMessage.find('.panel-message2');
                    const originalShadow = $panel.css('box-shadow');

                    $panel.css({
                        'transition': 'box-shadow 0.3s ease',
                        'box-shadow': '0 0 15px rgba(255, 165, 0, 0.6)',
                        'border': '2px solid orange'
                    });

                    setTimeout(() => {
                        $panel.css({
                            'box-shadow': originalShadow,
                            'border': ''
                        });
                    }, 2000);
                }
            });

            document.addEventListener("DOMContentLoaded", function() {

                var messageReply = document.getElementById("scrollSec");
                var scrollToBottomBtn = document.getElementById("scrollToBottomBtn");

                // Function to check the height and scroll position
                function checkScrollButton() {
                    var body = document.body;
                    var html = document.documentElement;

                    // Get the height of the entire document (body and HTML)
                    var documentHeight = Math.max(body.scrollHeight, body.offsetHeight,
                        html.clientHeight, html.scrollHeight, html.offsetHeight);

                    // Check if the document height is greater than the window height
                    if (documentHeight > window.innerHeight) {
                        // Check if the user is scrolled to the bottom
                        if (window.scrollY + window.innerHeight >= documentHeight - 1) {
                            scrollToBottomBtn.style.display = "none"; // Hide button when at the bottom
                        } else {
                            scrollToBottomBtn.style.display = "block"; // Show button when scrolled up
                        }
                    } else {
                        scrollToBottomBtn.style.display =
                            "none"; // Hide button if content height is not greater than window height
                    }
                }

                checkScrollButton();
                window.addEventListener("scroll", checkScrollButton);
                scrollToBottomBtn.addEventListener("click", function() {
                    window.scrollTo({
                        top: document.body.scrollHeight, // Scroll to the bottom of the body
                        behavior: 'smooth', // Smooth scrolling effect
                    });

                    setTimeout(checkScrollButton, 1000); // Recheck after 1 second
                });
            });

            $('body').on('click', '.delete-message', function() {
                var id = $(this).attr('data-message-id');
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var confirmDelete = window.confirm("Are you sure you want to delete this message?");
                if (confirmDelete) {
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
                } else {
                    toastr.info('Deletion canceled.');
                }
            });

            let isFetching = false;

            function fetchMessages() {
                if (isFetching) return;
                isFetching = true;
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

                                        // reply block
                                        var replyHtml = "";
                                        if (value.replied_id) {
                                            var repliedUser = (value.replied_user_id == result.auth_id) ?
                                                "You" :
                                                value.replied_email;
                                            var repliedBgColor = value.replied_user_color || '#e0e0e0';
                                            var displayText = value.quoted_text || value.replied_message;
                                            // Truncate long messages
                                            if (displayText && displayText.length > 150) {
                                                displayText = displayText.substring(0, 150) + '...';
                                            }
                                            var isQuoted = value.quoted_text ? true : false;
                                            var quotedLabel = isQuoted ? ' <span style="font-weight: normal; color: #666;">(quoted)</span>' : '';
                                            var quoteMarks = isQuoted ? '"' : '';

                                            replyHtml = `
                                                <div class="reply-preview clickable-reply" data-replied-message-id="${value.replied_id}"
                                                    style="padding:8px; position: relative; margin-bottom: 8px; background-color: ${repliedBgColor}; border-left: 4px solid rgba(0,0,0,0.2); border-radius: 4px; cursor: pointer;">
                                                    <strong>${repliedUser}${quotedLabel}</strong>
                                                    <pre style="margin:0; border: none; padding: 0;">${quoteMarks}${escapeHtml(displayText)}${quoteMarks}</pre>
                                                </div>
                                            `;
                                        }

                                        $("#last_message_id").val(value.id);
                                        var userEmail = value.email;
                                        var bgColor = value.user_color || '#ffffff';

                                        var deleteButtonHtml = "";
                                        if (value.user_id == result.auth_id) {
                                            deleteButtonHtml = `
                                                <a class="delete-message" data-message-id="${value.id}" title="Delete" style="text-decoration: none;">
                                                    <i class="fa fa-trash-o" aria-hidden="true" style="cursor: pointer; font-size: 18px;"></i>
                                                </a>`;
                                        }

                                        var messageHtml = `
                                                <div class="panel panel-default panel-message1" data-message-id="${value.id}" data-date="${value.created_at.split(' ')[0]}">
                                                    <div class="panel-body panel-message2" style="background-color: ${bgColor}">
                                                        ${replyHtml}
                                                        <b>${userEmail} -</b> ${value.created_at} <br>
                                                        <pre>${makeLinksClickable(escapeHtml(value.message))}</pre>
                                                        <div class="message-actions" style="margin-top: 10px; display: flex; gap: 15px; align-items: center;">
                                                            <a class="reply-specific-message" data-message-id="${value.id}" data-sender-id="${userEmail}" data-message="${value.message}" data-color="${bgColor}" title="Reply" style="text-decoration: none;">
                                                                <i class="fa fa-reply" aria-hidden="true" style="cursor: pointer; font-size: 18px;"></i>
                                                            </a>
                                                            <a data-toggle="modal" data-target="#notesModal" class="open-notes-modal" data-sender-id="${value.user_id}" data-message="${value.message}" title="Note" style="text-decoration: none;">
                                                                <i class="fa fa-sticky-note-o" aria-hidden="true" style="cursor: pointer; font-size: 18px;"></i>
                                                            </a>
                                                            ${deleteButtonHtml}
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        // var messageHtml = `
                                //     <div class="panel panel-default panel-message1">
                                //         <div class="panel-body panel-message2">
                                //             ${replyHtml}
                                //             <b>${userEmail} -</b> ${value.created_at} <br>
                                //             <pre>${value.message}</pre>
                                //             <a class="reply-specific-message" data-message-id="${value.id}" data-sender-id="${userEmail}" data-message="${value.message}" title="Reply">
                                //                 <i class="fa fa-reply ml-1" aria-hidden="true" style="cursor: pointer;"></i>
                                //             </a>
                                //             <a data-toggle="modal" data-target="#notesModal" class="open-notes-modal" data-sender-id="${value.user_id}" data-message="${value.message}" title="Note"><i class="fa fa-sticky-note-o" aria-hidden="true" style="cursor: pointer;"></i></a>
                                //             <a class="delete-message" data-message-id="${value.id}" title="Delete"><i class="fa fa-trash-o" aria-hidden="true" style="cursor: pointer;"></i>
                                //             </a>
                                //         </div>
                                //     </div>
                                // `;
                                        $('#message-list').append(messageHtml);
                                    });
                                }
                            }
                        },
                        complete: function() {
                            isFetching = false; // allow next fetch
                        },
                        error: function(xhr) {
                            isFetching = false;
                            if (xhr.status === 403 && xhr.responseJSON.blocked) {
                                window.location.href = loginUrl; // Redirect to login page
                            }
                        }
                    });
                } else {
                    isFetching = false;
                }
            }

            setInterval(fetchMessages, 10000);

            function toggleElement(id, show, displayType = 'block') {
                const el = document.getElementById(id);
                if (el) el.style.display = show ? displayType : 'none';
            }

            function showReplyTextarea() {
                toggleElement('reply-form', true, 'block');
                toggleElement('reply-btn', false);
                callTextCounter();
                setTimeout(() => {
                    document.getElementById('reply').focus();
                }, 100);
            }

            function hideReplyTextarea() {
                toggleElement('reply-form', false);
                toggleElement('reply-btn', true, 'inline-block');
            }

            function showReplyPreview() {
                toggleElement('reply-preview', true, 'block');
            }

            // function hideReplyPreview() {
            //     toggleElement('reply-preview', false);
            // }

            function hideReplyPreview() {
                // Hide the reply preview div
                toggleElement('reply-preview', false);

                // Clear preview content
                document.getElementById('reply-user').textContent = '';
                document.getElementById('reply-text').textContent = '';
                $("#reply-preview").css("background-color", "");
                document.getElementById('reply_to_message_id').value = '';

                // Clear quoted text
                const quotedTextEl = document.getElementById('quoted_text');
                if (quotedTextEl) quotedTextEl.value = '';

                // Clear the reply textarea
                const replyTextarea = document.getElementById('reply');
                if (replyTextarea) replyTextarea.value = '';

                // Reset character counter
                const countEl = document.getElementById('count');
                if (countEl) countEl.textContent = replyTextarea ? replyTextarea.maxLength : 10000;

                // Optionally clear uploaded image IDs
                const imgIdsEl = document.getElementById('img-ids');
                if (imgIdsEl) imgIdsEl.value = '';
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

                $('#message-notes').on('keyup change', 'input, textarea, select', function(event) {
                    if ($.trim($(this).val()) && $(this).val().length > 0) {
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
                                hideReplyPreview();

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
                $('body').on('click', '.btn-extends-validity', function() {
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
                        beforeSend: function() {
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
                                if (response.message) {
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

                $('#extends-validity').on('keyup change', 'input, textarea, select', function(event) {
                    if ($.trim($(this).val()) && $(this).val().length > 0) {
                        $(this).closest('.form-input').find('.error').html('');
                    }
                });
                // extends Validity end

            });

            document.addEventListener("DOMContentLoaded", function() {
                callTextCounter();

                // Scroll to bottom of page to show latest messages
                setTimeout(function() {
                    window.scrollTo(0, document.body.scrollHeight);
                }, 100);
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

            // --- Search Logic ---
            let matches = [];
            let currentMatchIndex = -1;
            let originalContents = new Map();

            // Function to highlight text nodes only (not HTML attributes)
            function highlightTextNodes(element, regex) {
                let html = '';
                element.childNodes.forEach(node => {
                    if (node.nodeType === Node.TEXT_NODE) {
                        // Text node - apply highlighting
                        html += escapeHtml(node.textContent).replace(regex, '<span class="highlight">$1</span>');
                    } else if (node.nodeType === Node.ELEMENT_NODE) {
                        // Element node - preserve tag and recurse into children
                        const tag = node.tagName.toLowerCase();
                        let attrs = '';
                        for (let attr of node.attributes) {
                            attrs += ` ${attr.name}="${attr.value}"`;
                        }
                        html += `<${tag}${attrs}>${highlightTextNodes(node, regex)}</${tag}>`;
                    }
                });
                return html;
            }

            function highlightWords(searchTerm) {
                if (!searchTerm) {
                    clearSearch();
                    return;
                }

                // Restore
                originalContents.forEach((content, pre) => {
                    pre.innerHTML = content;
                });
                originalContents.clear();

                matches = [];
                const regex = new RegExp(`(${searchTerm.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&')})`, 'gi');

                $('#message-list pre').each(function() {
                    const pre = this;
                    const originalHTML = pre.innerHTML;
                    const textContent = pre.textContent || pre.innerText;

                    if ($(pre).closest('.panel-message1').is(':hidden')) return;

                    // Only search in text content, not HTML source
                    if (textContent.match(regex)) {
                        originalContents.set(pre, originalHTML);
                        // Highlight only in text nodes, preserving HTML structure
                        pre.innerHTML = highlightTextNodes(pre, regex);
                    }
                });

                matches = document.querySelectorAll('#message-list .highlight');

                if (matches.length > 0) {
                    $('#search-nav').css('display', 'flex');
                    currentMatchIndex = 0;
                    updateMatchHighlight();
                } else {
                    $('#search-count').text('0/0');
                    $('#search-nav').css('display', 'flex');
                    currentMatchIndex = -1;
                }
            }

            function updateMatchHighlight() {
                $('.highlight').removeClass('active');
                if (currentMatchIndex >= 0 && matches[currentMatchIndex]) {
                    const activeMatch = matches[currentMatchIndex];
                    $(activeMatch).addClass('active');
                    $('#search-count').text((currentMatchIndex + 1) + '/' + matches.length);
                    activeMatch.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            function clearSearch() {
                originalContents.forEach((content, pre) => {
                    pre.innerHTML = content;
                });
                originalContents.clear();
                $('#word-search').val('');
                $('#search-nav').hide();
                matches = [];
                currentMatchIndex = -1;
            }

            $('#word-search').on('input', function() {
                highlightWords($(this).val());
            });

            $('#word-search').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    if (matches.length > 0) {
                        currentMatchIndex = (currentMatchIndex + 1) % matches.length;
                        updateMatchHighlight();
                    }
                    e.preventDefault();
                }
            });

            $('#next-match').click(function() {
                if (matches.length > 0) {
                    currentMatchIndex = (currentMatchIndex + 1) % matches.length;
                    updateMatchHighlight();
                }
            });

            $('#prev-match').click(function() {
                if (matches.length > 0) {
                    currentMatchIndex = (currentMatchIndex - 1 + matches.length) % matches.length;
                    updateMatchHighlight();
                }
            });

            $('#clear-search').click(clearSearch);

            $('#date-search').on('change', function() {
                const selectedDateVal = $(this).val(); // yyyy-mm-dd
                if (!selectedDateVal) return;

                const dateParts = selectedDateVal.split('-');
                // Create comparision date (Client time 00:00:00)
                const selectedDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                selectedDate.setHours(0, 0, 0, 0);

                // Ensure all messages are visible (Jump mode)
                $('#message-list .panel-message1').show();

                let foundElement = null;

                // Loop through all messages to find the first one >= selectedDate
                $('#message-list .panel-message1').each(function() {
                    const dateAttr = $(this).data('date'); // Format: Y-m-d (e.g., 2024-01-15)

                    if (dateAttr) {
                        const dateParts = dateAttr.split('-');
                        const msgDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                        msgDate.setHours(0, 0, 0, 0);

                        if (msgDate >= selectedDate) {
                            foundElement = this;
                            return false; // Break the each loop
                        }
                    }
                });

                if (foundElement) {
                    foundElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Temporary highlight effect
                    const $panel = $(foundElement).find('.panel-message2');
                    const originalShadow = $panel.css('box-shadow');

                    $panel.css({
                        'transition': 'box-shadow 0.3s ease',
                        'box-shadow': '0 0 15px rgba(255, 165, 0, 0.6)',
                        'border': '1px solid orange'
                    });

                    setTimeout(() => {
                        $panel.css({
                            'box-shadow': originalShadow,
                            'border': '1px solid transparent' // Assuming default has no border or handled by css
                        });
                        // Clear inline styles after transition to revert to CSS file
                        setTimeout(() => {
                            $panel.css({
                                'box-shadow': '',
                                'border': '',
                                'transition': ''
                            });
                        }, 300);
                    }, 2000);
                } else {
                    // No messages found after this date, assume it's in the future relative to chat
                    // Show the last message
                    const lastMessage = $('#message-list .panel-message1').last();
                    if (lastMessage.length > 0) {
                        lastMessage[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        // Highlight last message
                        const $panel = lastMessage.find('.panel-message2');
                        const originalShadow = $panel.css('box-shadow');

                        $panel.css({
                            'transition': 'box-shadow 0.3s ease',
                            'box-shadow': '0 0 15px rgba(255, 165, 0, 0.6)',
                            'border': '1px solid orange'
                        });

                        setTimeout(() => {
                            $panel.css({
                                'box-shadow': originalShadow,
                                'border': '1px solid transparent'
                            });
                            setTimeout(() => {
                                $panel.css({
                                    'box-shadow': '',
                                    'border': '',
                                    'transition': ''
                                });
                            }, 300);
                        }, 2000);
                    }
                }
            });
        </script>
        <script src="{{ asset('js/invite-users.js') }}"></script>
        <script src="{{ asset('js/file-viewer-chat.js') }}"></script>
        <script>
            var loggedInEmail = "{{ auth()->user()->email }}";
            var loggedInUser = "{{ auth()->user()->id }}";
        </script>
    @endsection
@endif
