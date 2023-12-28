@php
    $userid = Auth::user()->id;
@endphp
@extends('layouts.app')

@section('content')
    <div class="panel-body" id="imagelist">
        <div class="alert alert-default">
            <h4><b>My Notes</b>
                <button type="button" class="btn btn-info m-b-10 pull-right add-new" data-toggle="modal" data-target="#notesModal">Add Note</button>
            </h4>
        </div>

        <div class="tab-content">
            <div class="tab-pane show active table-responsive" id="basic-datatable-preview">
                <table id="data-table" class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>Notes</th>
                            <th>Message</th>
                            <th style="width:15%;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div> <!-- end preview-->
        </div> <!-- end tab-content-->
    </div>
@endsection
@section('modal')
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
                                    <h4 class="modal-title"><span id="exampleModalLabel">Add Notes</span></h4>
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
                                <input type="hidden" name="note_id" class="note_id" id="note_id">
                            </div>
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
@section('script')
    <script>
        var notesUrl = "{{ route('notes.add') }}";
        var notelist = "{{ route('notes.list') }}";
        var getnote = "{{ route('notes.detail') }}";
        var notedelete = "{{ route('notes.delete') }}";
    </script>
    <script src="{{ asset('js/mynotes.js') }}"></script>
@endsection
