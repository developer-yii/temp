<div class="modal fade" id="inviteModal" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <!-- Modal Header -->
            <form action="" method="post" id="invite-user" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="invite-for-chat">
                                <h4 class="modal-title">Invite for Chat</h4>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btn-close" class="close" data-dismiss="modal">&times;</button>
                        </div>
                    </div>
                    {{-- <h4 style="margin-bottom: -15px !important;">Message Creator : <span id="creator-user"></span> </h4> --}}
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="container">

                        <input type="hidden" name="user_id" value="" id="user-id">
                        <input type="hidden" name="conversation_id" value="" id="conversation-id">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <label for="email_{{ $i }}">User {{ $i }} : </label>
                                </div>
                                <div class="col-md-6 form-group">
                                    <input type="text" id="email_{{ $i }}" name="email_{{ $i }}" class="form-control" placeholder="Email or Nickname">
                                    <input type="hidden" id="user_id_{{ $i }}" name="user_id_{{ $i }}">
                                    <span class="error"></span>
                                </div>
                            </div>
                        @endfor

                        <div class="row mb-3" id="firstVisitorRow" style="display:none;">
                            <div class="col-md-2">
                                <label><strong>First Visitor:</strong></label>
                            </div>
                            <div class="col-md-6">
                                <label><span id="firstVisitorEmail"></span></label>
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