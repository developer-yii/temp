@extends('layouts.app')

@section('content')
    <div class="panel-body" id="imagelist">
        <div class="alert alert-default">
            <h4><b>My Links</b></h4>
        </div>

        <div class="tab-content">
            <div class="tab-pane show active table-responsive">
                <table id="data-table" class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Link</th>
                            <th>Role</th>
                            <th>Participants</th>
                            <th>Expires</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversations as $index => $conv)
                            @php
                                $isCreator = $conv->user_id === Auth::id();
                                $url = url('/' . $conv->conversation_token);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                                    <button class="btn btn-xs btn-default copy-btn" data-url="{{ $url }}" style="margin-left:5px;">Copy</button>
                                </td>
                                <td>
                                    @if ($isCreator)
                                        <span class="label label-primary">Creator</span>
                                    @else
                                        @php
                                            $myInvite = $conv->invitedUsers->firstWhere('user_id', Auth::id());
                                            $invitedByEmail = $myInvite && $myInvite->createdBy ? $myInvite->createdBy->email : '';
                                        @endphp
                                        <span class="label label-default">Invited</span><br>
                                        <small class="text-muted">by {{ $invitedByEmail }}</small>
                                    @endif
                                </td>
                                <td>
                                    <ul style="margin:0; padding-left:16px;">
                                        @foreach ($conv->invitedUsers as $invite)
                                            <li>{{ $invite->user->nickname ?: $invite->user->email }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($conv->expiry)->format('d/m/Y H:i') }}</td>
                                <td>{{ $conv->created_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No links found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/mylinks.js') }}"></script>
@endsection
