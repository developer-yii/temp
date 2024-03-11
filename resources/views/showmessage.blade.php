
@php
$auth_id=Auth::user()->id;

$expiryTimestamp = strtotime($conversation->expiry);
$expirydate = date('d-m-Y H:i:s', $expiryTimestamp);

@endphp
<div class="panel-body" id="messagereply">
    <div class="alert alert-warning">
        <b>Attention!</b><br>
        <div style="text-align: justify;">
            • If you need to save the message contents somewhere, please make sure you use appropriate encryption.<br>
            • The contents of this page will disappear in <span id="message_time2">{{ $expirydate }}</span>.
        </div>
    </div>

        @foreach($data as $replydata)
        @php
            $date=date('d-m-Y H:i' , strtotime($replydata->created_at));
        @endphp
        <div class="panel panel-default panel-message1">
            <div class="panel-body panel-message2">
                <b>{{ $replydata->email }} -</b>  {{ $date }} <br>
                <pre>{{ $replydata->message }}</pre>
                <a data-toggle="modal" data-target="#notesModal" class="open-notes-modal" data-message="{{ $replydata->message }}">
                    <i class="fa fa-sticky-note-o ml-1" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        @endforeach


        <form  method="post" id="reply-form" autocomplete="off" style="display:none;">
            @csrf
            <input type="hidden" name="imgids" value="" id="img-ids">
            <input type="hidden" name="token" value="{{ $conversation->conversation_token }}">
            <div class="form-group">
                <textarea name="reply" id="reply" class="form-control form-message" rows="8" maxlength="10000" autofocus="autofocus" autocomplete="off" style="margin-bottom: 20px; resize: vertical;"></textarea>
                <span class="error" id="error"></span>
                <div id="char-count">
                    Characters remaining:
                    <span id="count">10000</span>
                    <span id="maximum">/ 10000</span>
                </div>
            </div>
            <div class="spacer">
                <button type="button" name="sendreply" class="btn btn-default" id="sendreply">Send Message</a>
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
    <div class="form-group">
        <a name="Reply" class="btn btnreply btn-primary" id="reply-btn" onclick="showReplyTextarea()">Reply</a>
    </div>
    <div class="spacer-block">
        <div class="spacer">
            <a href="{{ route('home')}}" class="btn btn-default"> Create a New Message</a>
        </div>
        <div class="spacer">
            <a href="" class="btn btn-default" onclick="DeleteChat(event)">Delete This Chat</a>
            <form id="delete-chat" action="{{ route('chat.delete', ['token' => $conversation->conversation_token ]) }}" method="POST" style="display: none;">

                @csrf
            </form>
        </div>
    </div>
</div>


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

