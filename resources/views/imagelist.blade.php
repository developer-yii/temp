@php
    $userid = Auth::user()->id;
@endphp
@extends('layouts.app')

@section('content')
<div class="panel-body" id="imagelist">
    <div class="alert alert-default">
        <h4><b>Image List</b></h4>
    </div>

    <div class="tab-content">
        <div class="tab-pane show active table-responsive" id="basic-datatable-preview">
            <table id="data-table" class="table table-bordered data-table">
                <thead>
                    <tr>
                        <td colspan="6"><button id="delete-selected-images" class="btn btn-danger">Delete Selected</button></td>
                    </tr>
                    <tr>
                        <th><input type="checkbox" id="select-all-images"></th>
                        <th>File Name</th>
                        <th>File</th>
                        <th>Created Date</th>
                        <th style="width:8%;">Action</th>
                    </tr>
                </thead>
            </table>
        </div> <!-- end preview-->
    </div> <!-- end tab-content-->
</div>
@endsection
@section('script')
<script>
    var imagelist = "{{ route('image.list') }}";
    var imagedelete = "{{ route('image.delete') }}";
    var basePwdProtectedUrl = "{{ route('image.action', ['token' => '__TOKEN__']) }}";
    var deleteMultipleImageUrl = "{{ route('multiple-image.delete') }}";
</script>
<script src="{{ asset('js/images.js') }}"></script>

@endsection

