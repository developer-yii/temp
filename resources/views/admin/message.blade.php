@php
    $baseUrl = asset('backend')."/";
@endphp
@extends('layouts.admin-app')

@section('title','Admin | Dashboard')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="">Temp</a></li>
                        <li class="breadcrumb-item active">Message list</li>
                    </ol>
                </div>
                <h4 class="page-title">Message list</h4>
            </div>
        </div>
    </div>

    <span id="success"></span>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane show active" id="basic-datatable-preview">
                            <table id="message_datatable" class="table dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <td colspan="5"><button id="delete-selected-messages" class="btn btn-danger">Delete Selected</button></td>
                                    </tr>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-message"></th>
                                        <th>Date</th>
                                        <th>User Email</th>
                                        <th>Token/Url</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div> <!-- end preview-->
                    </div> <!-- end tab-content-->
                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->
</div>
@endsection
<script>
    var deleteMultipleMessageUrl = "{{ route('admin.multiple-message.delete') }}";
</script>
@section('js')
<script src="{{$baseUrl}}js/custom.js"></script>
@endsection
