@php
    $baseUrl = asset('backend')."/";
@endphp
@extends('layouts.admin-app')

@section('title','Admin | Notes')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="">Temp</a></li>
                        <li class="breadcrumb-item active">Note list</li>
                    </ol>
                </div>
                <h4 class="page-title">Note list</h4>
            </div>
        </div>
    </div>

    <span id="success"></span>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Note List</h4>
                    <div class="tab-content">
                        <div class="tab-pane show active" id="basic-datatable-preview">
                            <table id="note_datatable" class="table w-100">
                                <thead>
                                    <tr>
                                        <th width="10%">Id</th>
                                        <th width="25%">Notes</th>
                                        <th width="50%">Message</th>
                                        <th width="15%"><center>Action</center></th>
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

@section('js')
<script src="{{$baseUrl}}js/custom.js"></script>
@endsection
